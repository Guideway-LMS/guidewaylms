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

 // --- FUNÇÃO DE ENVIO SEGURA (SEM COMENTÁRIO) ---
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
      $uploadDir = JPATH_ROOT . '/images/uploads/submissions/';
      if (!file_exists($uploadDir)) {
          mkdir($uploadDir, 0755, true);
          // Cria index.html vazio para segurança
          file_put_contents($uploadDir . 'index.html', '');
      }

      // Gera nome único e seguro: timestamp_usuario_nomelimpo
      $cleanName   = Path::clean(pathinfo($file['name'], PATHINFO_FILENAME));
      $cleanName   = preg_replace('/[^a-zA-Z0-9_-]/', '', $cleanName); 
      $newFileName = time() . '_u' . $user->id . '_' . $cleanName . '.' . $ext;
      
      $targetPath = $uploadDir . $newFileName;
      $dbPath     = 'images/uploads/submissions/' . $newFileName;

      // 5. Upload e Gravação no Banco
      if (File::upload($file['tmp_name'], $targetPath)) {
          $db = Factory::getDbo();
          $query = $db->getQuery(true);
          
          // Colunas originais (sem student_comment)
          $columns = array(
              'user_id', 
              'lesson_id', 
              'file_path', 
              'status', 
              'submitted_at'
          );
          
          $values  = array(
              (int) $user->id,
              (int) $lesson_id,
              $db->quote($dbPath),
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