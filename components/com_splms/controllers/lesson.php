<?php
/**
 * @package SP LMS
 * @subpackage com_splms
 * @author JoomShaper https://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2023 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
 */

// No Direct Access
defined('_JEXEC') or die('Restricted Access');

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Filesystem\Path;

class SplmsControllerLesson extends BaseController {

  public function __construct($config = array()) {
      parent::__construct($config);
  }

  public function getModel($name = 'Lessons', $prefix = 'SplmsModel', $config = array()) {
      return parent::getModel($name, $prefix, array('ignore_request' => true));
  }

  public function completeditem() {
      $app = Factory::getApplication();
      $input = $app->input;
      $user = Factory::getUser();

      $item_id = $input->get('item_id', 0, 'INT');
      $item_type = $input->get('item_type', '', 'STRING');
      $user_id = $input->get('user_id', 0, 'INT');
      $course_id = $input->get('course_id', 0, 'INT');

      if($user_id != $user->id) {
          $output['success'] = false;
          $output['message'] = 'You do not have permission to complete this item.';
          echo json_encode($output);
          die();
      }

      $model = $this->getModel();

      if(method_exists($model, 'completedItem')) {
          $result = $model->completedItem($item_id, $item_type, $user_id, $course_id);
          echo json_encode($result);
          die();
      }
  }
public function uploadassignment() {
    // Redireciona para o método submit
    return $this->submit();
}

  public function submit() {
      // 1. Inicialização e Segurança Básica
      $app   = Factory::getApplication();
      $input = $app->input;
      $user  = Factory::getUser();

      // Checa login
      if ($user->guest) {
          $this->setRedirect('index.php', 'Você precisa estar logado para enviar trabalhos.', 'warning');
          return false;
      }

      // Checa Token CSRF (Proteção contra hackers)
      if (!\Joomla\CMS\Session\Session::checkToken()) {
          $this->setRedirect('index.php', 'Sessão expirada ou token inválido. Tente novamente.', 'error');
          return false;
      }

      // 2. Recebe os dados
      $lesson_id = $input->getInt('lesson_id');
      $file      = $input->files->get('uploaded_file');

      // 3. Validações de Arquivo (SEGURANÇA CRÍTICA)
      if (!$file || $file['error'] != 0) {
          $this->setRedirect(
              Route::_('index.php?option=com_splms&view=lesson&id=' . $lesson_id, false),
              'Nenhum arquivo enviado ou erro no upload.',
              'warning'
          );
          return false;
      }

      // 3.1 Valida Tamanho (Máximo 10MB)
      $maxSize = 10 * 1024 * 1024; // 10MB
      if ($file['size'] > $maxSize) {
          $this->setRedirect(
              Route::_('index.php?option=com_splms&view=lesson&id=' . $lesson_id, false),
              'O arquivo é muito grande. O limite máximo é 10MB.',
              'warning'
          );
          return false;
      }

      // 3.2 Valida Extensão (Lista Branca - Só permite arquivos seguros)
      $allowedExts = ['pdf', 'zip', 'rar', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'mp4'];
      $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

      if (!in_array($ext, $allowedExts)) {
          $this->setRedirect(
              Route::_('index.php?option=com_splms&view=lesson&id=' . $lesson_id, false),
              'Formato não permitido. Aceitamos apenas: PDF, ZIP, DOC, Imagens ou MP4.',
              'error'
          );
          return false;
      }

      // 4. Preparação do Arquivo
      // GUIDEWAY CUSTOM - Joshua - Fevereiro 2026
      // Salva FORA do public_html para segurança (TAREFA DA DAILY)
     $uploadDir = '/var/www/uploads_privados/trabalhos/';
// DEBUG - Remover depois
	if (!file_exists($uploadDir)) {
          mkdir($uploadDir, 0755, true);
          // Cria index.html vazio para segurança
          file_put_contents($uploadDir . 'index.html', '');
      }

      // Gera nome ANONIMIZADO com random_bytes (TAREFA DA DAILY)
      $hash = bin2hex(random_bytes(20)); // 40 caracteres hexadecimais
      $newFileName = $hash . '.' . $ext;

      $targetPath = $uploadDir . $newFileName;
      // Caminho relativo para salvar no banco
      $dbPath = 'uploads_privados/trabalhos/' . $newFileName;
      
      // Guardar nome original para o professor ver (TAREFA DA DAILY)
      $originalFileName = $file['name'];

      // 5. Upload e Gravação no Banco
      if (File::upload($file['tmp_name'], $targetPath)) {
          $db = Factory::getDbo();
          $query = $db->getQuery(true);

          // Colunas + nome original (TAREFA DA DAILY)
          $columns = array(
              'user_id',
              'lesson_id',
              'file_path',
              'original_filename',
              'status',
              'submitted_at'
          );

          $values  = array(
              (int) $user->id,
              (int) $lesson_id,
              $db->quote($dbPath),
              $db->quote($originalFileName),
              0, // Status 0 = Pendente
              'NOW()'
          );

          $query->insert($db->quoteName('#__splms_submissions'))
                ->columns($db->quoteName($columns))
                ->values(implode(',', $values));

          $db->setQuery($query);

          try {
              $db->execute();

              // Atualiza progresso do curso
              $model = $this->getModel();
              if (method_exists($model, 'completedItem')) {
                   $model->completedItem($lesson_id, 'lesson', $user->id);
              }

              $this->setRedirect(
                  Route::_('index.php?option=com_splms&view=lesson&id=' . $lesson_id, false),
                  'Trabalho enviado com sucesso! Aguarde a correção.',
                  'message'
              );
              return true;

          } catch (Exception $e) {
              // Limpa arquivo se der erro no banco
              if (file_exists($targetPath)) {
                  File::delete($targetPath);
              }

              $this->setRedirect(
                  Route::_('index.php?option=com_splms&view=lesson&id=' . $lesson_id, false),
                  'Erro ao salvar registro. Tente novamente.',
                  'error'
              );
              return false;
          }
      }

      $this->setRedirect(
          Route::_('index.php?option=com_splms&view=lesson&id=' . $lesson_id, false),
          'Falha ao mover o arquivo.',
          'error'
      );
      return false;
  }
}
