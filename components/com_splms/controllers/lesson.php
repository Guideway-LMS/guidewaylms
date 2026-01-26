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

  // --- FUNÇÃO DE ENVIO CORRIGIDA ---
  public function submit() {
      $app   = Factory::getApplication();
      $input = $app->input;
      $user  = Factory::getUser();

      if ($user->guest) {
          $this->setRedirect('index.php', 'Você precisa estar logado.', 'warning');
          return false;
      }

      if (!JSession::checkToken()) {
          $this->setRedirect('index.php', 'Token inválido.', 'error');
          return false;
      }

      $lesson_id = $input->getInt('lesson_id');
      $file      = $input->files->get('uploaded_file');

      if ($file && $file['error'] == 0) {
          $uploadDir = JPATH_ROOT . '/images/uploads/submissions/';
          if (!file_exists($uploadDir)) {
              mkdir($uploadDir, 0755, true);
          }

          $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
          $cleanName = Path::clean(pathinfo($file['name'], PATHINFO_FILENAME)); // Uso seguro do Path
          $cleanName = preg_replace('/[^a-zA-Z0-9_-]/', '', $cleanName); // Limpeza extra
          $newFileName = time() . '_' . $cleanName . '.' . $ext;
          $targetPath = $uploadDir . $newFileName;
          $dbPath = 'images/uploads/submissions/' . $newFileName;

          if (File::upload($file['tmp_name'], $targetPath)) {
              $db = Factory::getDbo();
              $query = $db->getQuery(true);
              
              $columns = array('user_id', 'lesson_id', 'file_path', 'status', 'submitted_at');
              $values  = array(
                  (int) $user->id,
                  (int) $lesson_id,
                  $db->quote($dbPath),
                  0, 
                  'NOW()'
              );

              $query->insert($db->quoteName('#__splms_submissions'))
                    ->columns($db->quoteName($columns))
                    ->values(implode(',', $values));
              
              $db->setQuery($query);
              
              try {
                  $db->execute();

                  $model = $this->getModel();
                  $model->completedItem($lesson_id, 'lesson', $user->id); 

                  // Redireciona para a PRÓPRIA LIÇÃO para ver o status
                  $this->setRedirect(
                      Route::_('index.php?option=com_splms&view=lesson&id=' . $lesson_id, false),
                      'Trabalho enviado com sucesso!'
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

      $this->setRedirect(
          Route::_('index.php?option=com_splms&view=lesson&id=' . $lesson_id, false),
          'Erro no upload do arquivo.',
          'error'
      );
      return false;
  }
}