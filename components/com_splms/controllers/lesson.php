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
use Joomla\CMS\Filesystem\File; // Importante para manipular o arquivo
use Joomla\CMS\Router\Route;    // Importante para redirecionar

class SplmsControllerLesson extends FormController {

  public function __construct($config = array()) {
    parent::__construct($config);
  }

  public function getModel($name = 'Lessons', $prefix = 'SplmsModel', $config = array()) {
    return parent::getModel($name, $prefix, $config);
  }

  /**
   * Função existente para completar via botão AJAX
   */
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

    $item_id      = $input->post->get('item_id', 0, 'INT');
    $item_type      = $input->post->get('item_type', NULL, 'STRING');

    $output['status'] = false;
    if($item_id && $item_type) {
      $submitted = $model->completedItem($item_id, $item_type, $user->id);
      $output['content'] = Text::_('COM_SPLMS_LESSON_COMPLETED');
      $output['status'] = true;
    }

    echo json_encode($output);
    die();
  }

  /**
   * NOVA FUNÇÃO: Processa o Upload e Marca Progresso
   */
  public function submit() {
      // 1. Verificações de Segurança
      $app   = Factory::getApplication();
      $input = $app->input;
      $user  = Factory::getUser();

      // Checa se está logado
      if ($user->guest) {
          $this->setRedirect('index.php', 'Você precisa estar logado.', 'warning');
          return false;
      }

      // Verifica Token de segurança (CSRF)
      if (!JSession::checkToken()) {
          $this->setRedirect('index.php', 'Token inválido.', 'error');
          return false;
      }

      // 2. Recebe dados do Formulário
      $course_id = $input->getInt('course_id');
      $lesson_id = $input->getInt('lesson_id');
      $file      = $input->files->get('uploaded_file'); // Nome do campo no HTML

      // 3. Processa o Arquivo
      if ($file && $file['error'] == 0) {
          
          // Define pasta de destino (cria se não existir)
          $uploadDir = JPATH_ROOT . '/images/uploads/submissions/';
          if (!file_exists($uploadDir)) {
              mkdir($uploadDir, 0755, true);
          }

          // Limpa o nome do arquivo e adiciona timestamp para evitar duplicatas
          $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
          $cleanName = JFile::makeSafe(pathinfo($file['name'], PATHINFO_FILENAME));
          $newFileName = time() . '_' . $cleanName . '.' . $ext;
          $targetPath = $uploadDir . $newFileName;
          $dbPath = 'images/uploads/submissions/' . $newFileName;

          // Move o arquivo
          if (File::upload($file['tmp_name'], $targetPath)) {
              
              // 4. Salva no Banco de Submissões
              $db = Factory::getDbo();
              $query = $db->getQuery(true);
              
              $columns = array('user_id', 'lesson_id', 'course_id', 'file_path', 'status', 'submitted_at');
              $values  = array(
                  (int) $user->id,
                  (int) $lesson_id,
                  (int) $course_id,
                  $db->quote($dbPath),
                  0, // 0 = Pendente, 1 = Aprovado
                  'NOW()'
              );

              $query->insert($db->quoteName('#__splms_submissions')) // Use o nome correto da tabela do Michael
                    ->columns($db->quoteName($columns))
                    ->values(implode(',', $values));
              
              $db->setQuery($query);
              try {
                  $db->execute();

                  // ======================================================
                  // 5. O SEGREDO DO PROGRESSO ESTÁ AQUI
                  // ======================================================
                  // Chama o Model para marcar a lição como concluída na tabela de progresso
                  $model = $this->getModel();
                  $model->completedItem($lesson_id, 'lesson', $user->id); 
                  // ======================================================

                  // Sucesso! Redireciona para o curso
                  $this->setRedirect(
                      Route::_('index.php?option=com_splms&view=course&id=' . $course_id, false),
                      'Trabalho enviado com sucesso! Progresso contabilizado.'
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

      // Erro Genérico no Upload
      $this->setRedirect(
          Route::_('index.php?option=com_splms&view=lesson&id=' . $lesson_id, false),
          'Erro ao fazer upload do arquivo. Tente novamente.',
          'error'
      );
      return false;
  }

}