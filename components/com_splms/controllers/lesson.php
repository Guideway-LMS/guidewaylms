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
    
    // READICIONADO
    public function completeditem()
    {
        error_reporting(E_ALL);
        ini_set('display_errors', 0);

        $app   = Factory::getApplication();
        $input = $app->input;
        $user  = Factory::getUser();

        $response = ['status' => false, 'content' => ''];

        // 1️⃣ Verifica login
        if ($user->guest) {
            $response['content'] = Text::_('COM_SPLMS_LOGIN_TO_COMPLETE');
            echo json_encode($response);
            $app->close();
        }

        // 2️⃣ Valida input
        $itemId   = $input->getInt('item_id');
        $itemType = $input->getString('item_type', 'lesson');

        if (!$itemId) {
            $response['content'] = 'Item ID inválido';
            echo json_encode($response);
            $app->close();
        }

        try {

            // 3️⃣ Carrega model
            BaseDatabaseModel::addIncludePath(JPATH_COMPONENT . '/models');
            $model = $this->getModel('Lessons', 'SplmsModel');

            if (!$model) {
                throw new \Exception('Model Lessons não encontrado.');
            }

            // 4️⃣ Executa lógica do model
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

    // Redirecionamento de compatibilidade
    public function submit() {
        return $this->uploadAssignment();
    }

    public function uploadAssignment() {
        $app   = Factory::getApplication();
        $input = $app->input;
        $user  = Factory::getUser();
        $db    = Factory::getDbo();

        // 1. DADOS CRÍTICOS - Captura os dados que vieram do formulário de forma segura
        $lesson_id = $input->getInt('lesson_id', 0);
        $course_id = $input->getInt('course_id', 0);
        $itemId    = $input->getInt('Itemid', 0);

        // === INJEÇÃO GUIDEWAY: CAPTURA O ID DO PROFESSOR ===
        $teacher_id = $input->getInt('teacher_id', 0);
        // ===================================================

        // 2. MONTAGEM DA URL DE RETORNO (Blindada)
        if ($lesson_id) {
            $url = 'index.php?option=com_splms&view=lesson&id=' . $lesson_id;
            if ($itemId) {
                $url .= '&Itemid=' . $itemId;
            }
            $redirectUrl = Route::_($url, false);
        } else {
            $redirectUrl = $input->server->getString('HTTP_REFERER', 'index.php');
        }

        // 3. SEGURANÇA
        if ($user->guest) {
            $this->setRedirect(Route::_('index.php?option=com_users&view=login', false), 'Faça login.', 'warning');
            return false;
        }

        // 4. UPLOAD COM MELHORIAS DE SEGURANÇA - GUIDEWAY CUSTOM
        $file = $input->files->get('uploaded_file');
        if (!$file || $file['error'] != 0) {
            $this->setRedirect($redirectUrl, 'Selecione um arquivo válido.', 'warning');
            return false;
        }

        // GUIDEWAY CUSTOM - Armazenamento fora do public_html
        $uploadDir = '/var/www/uploads_privados/trabalhos/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
            file_put_contents($uploadDir . 'index.html', '');
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        // GUIDEWAY CUSTOM - Anonimização com random_bytes
        $hash = bin2hex(random_bytes(8)); // 16 caracteres
        $newFileName = $hash . '.' . $ext;
        
        $targetPath = $uploadDir . $newFileName;
        $dbPath = 'uploads_privados/trabalhos/' . $newFileName;
        
        // GUIDEWAY CUSTOM - Guardar nome original
        $originalFileName = $file['name'];

        if (!File::upload($file['tmp_name'], $targetPath)) {
            $this->setRedirect($redirectUrl, 'Erro ao salvar arquivo.', 'error');
            return false;
        }

        // 5. BANCO DE DADOS
        // 5. BANCO DE DADOS - Usa Model ao invés de código direto
try {
    // Carrega o Model
    BaseDatabaseModel::addIncludePath(JPATH_COMPONENT . '/models');
    $model = BaseDatabaseModel::getInstance('Trabalho', 'SplmsModel');
    
    if (!$model) {
        throw new Exception('Model Trabalho não encontrado.');
    }
    
    // Busca teacher_id da lição
    $teacher_id = $model->getTeacherId($lesson_id);
    
    // Prepara dados
    $dados = [
        'user_id' => $user->id,
        'teacher_id' => $teacher_id,
        'lesson_id' => $lesson_id,
        'course_id' => $course_id,
        'file_path' => $dbPath,
        'original_filename' => $originalFileName
    ];
    
    // Salva no banco via Model
    if (!$model->salvarTrabalho($dados)) {
        throw new Exception('Erro ao salvar trabalho no banco.');
    }
    
    // Marca lição como completa
    $lessonModel = $this->getModel('Lessons', 'SplmsModel');
    if ($lessonModel) {
        $lessonModel->completedItem($lesson_id, 'lesson', $user->id);
    }
    
    $this->setRedirect($redirectUrl, 'Trabalho enviado com sucesso!', 'success');
    return true;
    
} catch (Exception $e) {
    // Limpa arquivo se der erro no banco
    if (file_exists($targetPath)) {
        File::delete($targetPath);
    }
    $this->setRedirect($redirectUrl, 'Erro: ' . $e->getMessage(), 'error');
    return false;
	}
        // 5. BANCO DE DADOS (Blindado contra Erro 500)
        try {
            $data = new \stdClass();
            $data->user_id = $user->id;
            $data->lesson_id = $lesson_id;
            $data->course_id = $course_id;
            $data->teacher_id = $teacher_id; // Injetado do formulário!
            $data->file_path = $dbPath;
            $data->original_filename = $originalFileName;
            $data->status = 0; // 0 = Aguardando Correção
            $data->submitted_at = Factory::getDate()->toSql();
            
            if (!$db->insertObject('#__splms_submissions', $data)) {
                throw new Exception("Falha ao gravar envio no banco de dados.");
            }

            // Marca lição como completa
            $model = $this->getModel('Lessons', 'SplmsModel');
            if ($model) {
                $model->completedItem($lesson_id, 'lesson', $user->id);
            }

            $this->setRedirect($redirectUrl, 'Trabalho enviado com sucesso!', 'success');
            return true;
            
        } catch (Exception $e) {
            // Limpa arquivo se der erro no banco
            if (file_exists($targetPath)) {
                File::delete($targetPath);
            }
            $this->setRedirect($redirectUrl, 'Erro: ' . $e->getMessage(), 'error');
            return false;
        }
    }
}
