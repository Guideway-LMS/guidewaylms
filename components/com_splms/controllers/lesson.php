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
use Joomla\CMS\Filesystem\Path;
use Joomla\CMS\Session\Session; // Adicionado para verificação de segurança moderna

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

  // --- FUNÇÃO DE ENVIO DE TRABALHO ---
  // Renomeada para uploadAssignment para bater com o formulário da View
  public function uploadAssignment() {
      $app   = Factory::getApplication();
      $input = $app->input;
      $user  = Factory::getUser();

      // 1. Verificações de Segurança
      if ($user->guest) {
          $this->setRedirect('index.php', 'Você precisa estar logado.', 'warning');
          return false;
      }

      if (!Session::checkToken()) {
          $this->setRedirect('index.php', 'Token inválido ou sessão expirada.', 'error');
          return false;
      }

      // 2. Recebimento dos Dados
      $lesson_id = $input->getInt('lesson_id');
      $course_id = $input->getInt('course_id', 0); // <--- Captura o ID do Curso
      $file      = $input->files->get('uploaded_file');

      // 3. Processamento do Upload
      if ($file && $file['error'] == 0) {
          $uploadDir = JPATH_ROOT . '/images/uploads/submissions/';
          if (!file_exists($uploadDir)) {
              mkdir($uploadDir, 0755, true);
          }

          $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
          $cleanName = Path::clean(pathinfo($file['name'], PATHINFO_FILENAME));
          $cleanName = preg_replace('/[^a-zA-Z0-9_-]/', '', $cleanName);
          $newFileName = time() . '_' . $cleanName . '.' . $ext;
          $targetPath = $uploadDir . $newFileName;
          $dbPath = 'images/uploads/submissions/' . $newFileName;

          if (File::upload($file['tmp_name'], $targetPath)) {
              $db = Factory::getDbo();
              
              // 4. Salvar na Tabela de Submissões
              $query = $db->getQuery(true);
              $columns = array('user_id', 'lesson_id', 'course_id', 'file_path', 'status', 'submitted_at');
              $values  = array(
                  (int) $user->id,
                  (int) $lesson_id,
                  (int) $course_id, // <--- Salva o ID do Curso no Banco
                  $db->quote($dbPath),
                  0, // Status 0 = Pendente de nota
                  'NOW()'
              );

              $query->insert($db->quoteName('#__splms_submissions'))
                    ->columns($db->quoteName($columns))
                    ->values(implode(',', $values));
              $db->setQuery($query);
              
              try {
                  $db->execute();

                  // 5. Atualizar Status da Aula (Pendente)
                  
                  // Passo A: Cria o registro na tabela useritems (padrão do sistema)
                  $model = $this->getModel();
                  $model->completedItem($lesson_id, 'lesson', $user->id); 

                  // Passo B: Força o status para 2 (Pendente)
                  // Isso impede que a barra de progresso conte como "Concluído" antes da nota
                  $queryUpdate = $db->getQuery(true);
                  $queryUpdate->update($db->quoteName('#__splms_useritems'))
                              ->set($db->quoteName('published') . ' = 2') 
                              ->where($db->quoteName('user_id') . ' = ' . (int)$user->id)
                              ->where($db->quoteName('item_id') . ' = ' . (int)$lesson_id)
                              ->where($db->quoteName('item_type') . ' = ' . $db->quote('lesson'));
                  
                  $db->setQuery($queryUpdate);
                  $db->execute();
                  // ----------------------------------------------------

                  $this->setRedirect(
                      Route::_('index.php?option=com_splms&view=lesson&id=' . $lesson_id, false),
                      'Trabalho enviado com sucesso! Aguarde a correção do professor.'
                  );
                  return true;

              } catch (Exception $e) {
                  $this->setRedirect(
                      Route::_('index.php?option=com_splms&view=lesson&id=' . $lesson_id, false),
                      'Erro ao salvar no banco de dados: ' . $e->getMessage(),
                      'error'
                  );
                  return false;
              }
          }
      }

      $this->setRedirect(
          Route::_('index.php?option=com_splms&view=lesson&id=' . $lesson_id, false),
          'Erro no upload do arquivo. Verifique o tamanho e o formato.',
          'error'
      );
      return false;
  }
}