<?php
/**
 * @package com_splms
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2024 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
 **/

defined('_JEXEC') or die('Restricted Access');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Filesystem\Path; // Adicionado para garantir compatibilidade

class SplmsControllerLesson extends FormController {

  public function __construct($config = array()) {
    parent::__construct($config);
  }

  public function getModel($name = 'Lessons', $prefix = 'SplmsModel', $config = array()) {
    return parent::getModel($name, $prefix, $config);
  }

  public function completeditem() {
    $model  = $this->getModel();
    $user   = Factory::getUser();
    $input  = Factory::getApplication()->input;
    $output = array();

    if(!$user->id) {
      $output['status'] = false;
      $output['content'] = Text::_('COM_SPLMS_LOGIN_TO_REVIEW');
      echo json_encode($output);
      die();
    }

    $item_id   = $input->post->get('item_id', 0, 'INT');
    $item_type = $input->post->get('item_type', NULL, 'STRING');

    $output['status'] = false;
    if($item_id && $item_type) {
      $submitted = $model->completedItem($item_id, $item_type, $user->id);
      $output['content'] = Text::_('COM_SPLMS_LESSON_COMPLETED');
      $output['status'] = true;
    }

    echo json_encode($output);
    die();
  }
// --- FUNÇÃO DE ENVIO COM SEGURANÇA (VALIDAÇÃO DE TIPO E TAMANHO) ---
 public function submit() {
      $app   = Factory::getApplication();
      $input = $app->input;
      $user  = Factory::getUser();

      // --- VACINA CONTRA ARQUIVO GIGANTE (SERVER CRASH) ---
      if ($_SERVER['REQUEST_METHOD'] == 'POST' && empty($_POST) && empty($_FILES) && $_SERVER['CONTENT_LENGTH'] > 0) {
          
          $lesson_id = $input->getInt('lesson_id', 0); 

          if (!$lesson_id) {
             // MUDANÇA: Se o ID sumiu, vai para a página inicial (seguro)
             $redirectUrl = 'index.php'; 
          } else {
             $redirectUrl = 'index.php?option=com_splms&view=lesson&id=' . $lesson_id;
          }

          $this->setRedirect(
              Route::_($redirectUrl, false),
              'Erro: O arquivo é maior do que o servidor permite (Crash). Tente um arquivo menor que 2MB.',
              'error'
          );
          return false;
      }
      // -----------------------------------------------------

      // 1. Verificação de Login
      if ($user->guest) {
          $this->setRedirect('index.php', 'Você precisa estar logado.', 'warning');
          return false;
      }

      // 2. Verificação de Token (CSRF)
      if (!JSession::checkToken()) {
          $this->setRedirect('index.php', 'Token de segurança inválido. Tente recarregar a página.', 'error');
          return false;
      }

      $lesson_id = $input->getInt('lesson_id');
      $file      = $input->files->get('uploaded_file');
      $comment   = $input->getString('student_comment', ''); // Captura o comentário (se já tiver criado a coluna no banco)

      // Configurações de Segurança
      $maxSize = 10 * 1024 * 1024; // 10MB em Bytes
$allowedExts = ['pdf', 'doc', 'docx', 'txt', 'jpg', 'jpeg', 'png', 'mp4'];

      if ($file && $file['error'] == 0) {
          
          $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

          // TRAVA 1: Extensão não permitida (Bloqueia .exe, .php, etc)
          if (!in_array($ext, $allowedExts)) {
              $this->setRedirect(
                  Route::_('index.php?option=com_splms&view=lesson&id=' . $lesson_id, false),
                  'Erro: Formato de arquivo não permitido (.' . $ext . '). Envie apenas: PDF, ZIP, DOC, Imagens ou MP4.',
                  'error'
              );
              return false;
          }

          // TRAVA 2: Tamanho do Arquivo (Bloqueia > 10MB)
          if ($file['size'] > $maxSize) {
              $this->setRedirect(
                  Route::_('index.php?option=com_splms&view=lesson&id=' . $lesson_id, false),
                  'Erro: O arquivo é muito grande. O limite máximo é 10MB.',
                  'error'
              );
              return false;
          }

          // Se passou pelas travas, prepara o upload
          $uploadDir = JPATH_ROOT . '/images/uploads/submissions/';
          if (!file_exists($uploadDir)) {
              mkdir($uploadDir, 0755, true);
          }

          // Limpeza do nome do arquivo (Segurança extra)
          $cleanName = Path::clean(pathinfo($file['name'], PATHINFO_FILENAME));
          $cleanName = preg_replace('/[^a-zA-Z0-9_-]/', '', $cleanName); 
          $newFileName = time() . '_' . $cleanName . '.' . $ext;
          $targetPath = $uploadDir . $newFileName;
          $dbPath = 'images/uploads/submissions/' . $newFileName;

          if (File::upload($file['tmp_name'], $targetPath)) {
              $db = Factory::getDbo();
              $query = $db->getQuery(true);
              
              // Verifica se a coluna student_comment existe na tabela antes de tentar inserir
              // Se você ainda não rodou o SQL da coluna, remova 'student_comment' daqui temporariamente
              $columns = array('user_id', 'lesson_id', 'file_path', 'status', 'submitted_at'); 
              $values  = array((int) $user->id, (int) $lesson_id, $db->quote($dbPath), 0, 'NOW()');

              // Se quiser salvar o comentário, descomente a linha abaixo quando atualizar o banco:
              // $columns[] = 'student_comment'; $values[] = $db->quote($comment);

              $query->insert($db->quoteName('#__splms_submissions'))
                    ->columns($db->quoteName($columns))
                    ->values(implode(',', $values));
              
              $db->setQuery($query);
              
              try {
                  $db->execute();

                  // Marca a lição como completa no sistema geral do SPLMS
                  $model = $this->getModel();
                  $model->completedItem($lesson_id, 'lesson', $user->id); 

                  $this->setRedirect(
                      Route::_('index.php?option=com_splms&view=lesson&id=' . $lesson_id, false),
                      'Trabalho enviado com sucesso! Aguarde a correção.'
                  );
                  return true;

              } catch (Exception $e) {
                  $this->setRedirect(
                      Route::_('index.php?option=com_splms&view=lesson&id=' . $lesson_id, false),
                      'Erro ao salvar no banco: ' . $e->getMessage(),
                      'error'
                  );
                  return false;
              }
          }
      }

      // Erro genérico de upload (ex: arquivo corrompido ou maior que o post_max_size do PHP)
      $this->setRedirect(
          Route::_('index.php?option=com_splms&view=lesson&id=' . $lesson_id, false),
          'Erro no upload. Verifique se o arquivo não ultrapassa o limite do servidor.',
          'error'
      );
      return false;
  }
 
}