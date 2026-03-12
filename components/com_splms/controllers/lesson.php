<?php
/**
 * @package     com_splms
 * @copyright   Copyright (c) 2010 - 2024 JoomShaper
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
 */
defined('_JEXEC') or die('Restricted Access');

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Router\Route;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Language\Text;

class SplmsControllerLesson extends FormController {
    
    public function completeditem()
    {
        error_reporting(E_ALL);
        ini_set('display_errors', 0);

        $app   = Factory::getApplication();
        $input = $app->input;
        $user  = Factory::getUser();

        $response = ['status' => false, 'content' => ''];

        if ($user->guest) {
            $response['content'] = Text::_('COM_SPLMS_LOGIN_TO_COMPLETE');
            echo json_encode($response);
            $app->close();
        }

        $itemId   = $input->getInt('item_id');
        $itemType = $input->getString('item_type', 'lesson');

        if (!$itemId) {
            $response['content'] = 'Item ID inválido';
            echo json_encode($response);
            $app->close();
        }

        try {
            BaseDatabaseModel::addIncludePath(JPATH_COMPONENT . '/models');
            $model = $this->getModel('Lessons', 'SplmsModel');

            if (!$model) {
                throw new \Exception('Model Lessons não encontrado.');
            }

            $result = $model->completedItem($itemId, $itemType, $user->id);

            if ($result) {
                $response['status'] = true;
                $response['content'] = 'Item marcado como concluído.';
            } else {
                $response['content'] = 'Não foi possível concluir o item.';
            }

        } catch (\Throwable $e) {
            $response['status'] = false;
            $response['content'] = 'Erro PHP: ' . $e->getMessage() . ' em ' . basename($e->getFile()) . ':' . $e->getLine();
        }

        echo json_encode($response);
        $app->close();
    }

    public function submit() {
        return $this->uploadAssignment();
    }

    public function uploadAssignment() {
        $app    = Factory::getApplication();
        $input  = $app->input;
        $user   = Factory::getUser();
        $db     = Factory::getDbo();
        
        // Deteta se a chamada veio do JavaScript (AJAX) da View do Aluno
        $isAjax = ($input->server->getString('HTTP_X_REQUESTED_WITH') === 'XMLHttpRequest');

        // 1. DADOS CRÍTICOS
        $lesson_id  = $input->getInt('lesson_id', 0);
        $course_id  = $input->getInt('course_id', 0);
        $itemId     = $input->getInt('Itemid', 0);
        $teacher_id = $input->getInt('teacher_id', 0);

        if ($lesson_id) {
            $url = 'index.php?option=com_splms&view=lesson&id=' . $lesson_id;
            if ($itemId) {
                $url .= '&Itemid=' . $itemId;
            }
            $redirectUrl = Route::_($url, false);
        } else {
            $redirectUrl = $input->server->getString('HTTP_REFERER', 'index.php');
        }

        if ($user->guest) {
            if ($isAjax) { echo json_encode(['success' => false, 'message' => 'Faça login.']); $app->close(); }
            $this->setRedirect(Route::_('index.php?option=com_users&view=login', false), 'Faça login.', 'warning');
            return false;
        }

        // 2. UPLOAD COM SEGURANÇA
        $file = $input->files->get('uploaded_file');
        if (!$file || $file['error'] != 0) {
            if ($isAjax) { echo json_encode(['success' => false, 'message' => 'Selecione um arquivo válido.']); $app->close(); }
            $this->setRedirect($redirectUrl, 'Selecione um arquivo válido.', 'warning');
            return false;
        }

        $uploadDir = JPATH_ROOT . '/uploads_privados/trabalhos/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
            file_put_contents($uploadDir . 'index.html', '<!DOCTYPE html><title></title>');
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $safeName = preg_replace('/[^a-zA-Z0-9_-]/', '_', pathinfo($file['name'], PATHINFO_FILENAME));
        $hash = bin2hex(random_bytes(4)); 
        
        $newFileName = $hash . '_' . $safeName . '.' . $ext;
        $targetPath = $uploadDir . $newFileName;
        $dbPath = 'uploads_privados/trabalhos/' . $newFileName;

        if (!File::upload($file['tmp_name'], $targetPath)) {
            if ($isAjax) { echo json_encode(['success' => false, 'message' => 'Erro ao salvar arquivo no servidor.']); $app->close(); }
            $this->setRedirect($redirectUrl, 'Erro ao salvar arquivo.', 'error');
            return false;
        }

        // 3. BANCO DE DADOS (Blindado via Query Direta)
        try {
            $data = new \stdClass();
            $data->user_id = $user->id;
            $data->lesson_id = $lesson_id;
            $data->course_id = $course_id;
            
            // Garantia dupla: Busca o teacher_id caso o formulário falhe ao enviar
            if (empty($teacher_id) && $course_id > 0) {
                $queryT = $db->getQuery(true)->select('created_by')->from('#__splms_courses')->where('id = ' . (int)$course_id);
                $db->setQuery($queryT);
                $teacher_id = (int) $db->loadResult();
            }
            
            $data->teacher_id = $teacher_id;
            $data->file_path = $dbPath;
            $data->status = 0; // 0 = Aguardando Correção (Aciona a mudança de ecrã do Aluno)
            $data->submitted_at = Factory::getDate()->toSql();
            
            // Gravação direta contornando a ausência de Models/Tables da equipa
            if (!$db->insertObject('#__splms_submissions', $data)) {
                throw new Exception("Falha ao gravar na base de dados.");
            }

            // Marca lição como completa nativamente
            $model = $this->getModel('Lessons', 'SplmsModel');
            if ($model) {
                $model->completedItem($lesson_id, 'lesson', $user->id);
            }

            // Retorno de Sucesso para o AJAX atualizar o ecrã
            if ($isAjax) {
                echo json_encode(['success' => true]);
                $app->close();
            }
            
            $this->setRedirect($redirectUrl, 'Trabalho enviado com sucesso!', 'success');
            return true;
            
        } catch (Exception $e) {
            if (file_exists($targetPath)) {
                File::delete($targetPath);
            }
            
            if ($isAjax) {
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
                $app->close();
            }
            
            $this->setRedirect($redirectUrl, 'Erro: ' . $e->getMessage(), 'error');
            return false;
        }
    }
}