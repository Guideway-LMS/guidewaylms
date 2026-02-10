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
use Joomla\CMS\Filesystem\Path; 
use Joomla\CMS\Table\Table;
use Joomla\CMS\Session\Session;

class SplmsControllerLesson extends FormController {

    // Redirecionamento de compatibilidade
    public function submit() {
        return $this->uploadAssignment();
    }

    public function uploadAssignment() {
        $app   = Factory::getApplication();
        $input = $app->input;
        $user  = Factory::getUser();

        // 1. DADOS CRÍTICOS
        $lesson_id = $input->getInt('lesson_id');
        $itemId    = $input->getInt('Itemid'); // Pega o ID do Menu enviado pelo form

        // 2. MONTAGEM DA URL DE RETORNO (Blindada)
        // Se temos ID da lição, voltamos para ela.
        if ($lesson_id) {
            $url = 'index.php?option=com_splms&view=lesson&id=' . $lesson_id;
            // Se temos Itemid, anexamos. Isso garante que o módulo lateral e o menu fiquem ativos.
            if ($itemId) {
                $url .= '&Itemid=' . $itemId;
            }
            $redirectUrl = Route::_($url, false);
        } else {
            // Se o ID sumiu (upload falhou gravemente), voltamos pelo HTTP_REFERER
            $redirectUrl = $input->server->getString('HTTP_REFERER', 'index.php');
        }

        // 3. SEGURANÇA
        if ($user->guest) {
            $this->setRedirect(Route::_('index.php?option=com_users&view=login', false), 'Faça login.', 'warning');
            return false;
        }

        // 4. UPLOAD
        $file = $input->files->get('uploaded_file');
        if (!$file || $file['error'] != 0) {
            $this->setRedirect($redirectUrl, 'Selecione um arquivo válido.', 'warning');
            return false;
        }

        // Upload Físico
        $uploadDir = JPATH_ROOT . '/images/uploads/submissions/';
        if (!file_exists($uploadDir)) mkdir($uploadDir, 0755, true);

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $newFileName = time() . '_u' . $user->id . '_' . $lesson_id . '.' . $ext;
        $targetPath = $uploadDir . $newFileName;
        $dbPath = 'images/uploads/submissions/' . $newFileName;

        if (!File::upload($file['tmp_name'], $targetPath)) {
            $this->setRedirect($redirectUrl, 'Erro ao salvar arquivo.', 'error');
            return false;
        }

        // 5. BANCO DE DADOS
        try {
            Table::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_splms/tables');
            $table = Table::getInstance('Submission', 'SplmsTable');
            
            $data = [
                'user_id' => $user->id,
                'lesson_id' => $lesson_id,
                'file_path' => $dbPath,
                'status' => 0,
                'submitted_at' => Factory::getDate()->toSql()
            ];

            if (!$table->bind($data) || !$table->store()) {
                throw new Exception($table->getError());
            }

            // Marca lição como completa
            $model = $this->getModel('Lessons', 'SplmsModel');
            if ($model) $model->completedItem($lesson_id, 'lesson', $user->id);

            // SUCESSO: Redireciona para a URL montada no passo 2
            $this->setRedirect($redirectUrl, 'Trabalho enviado com sucesso!', 'success');
            return true;

        } catch (Exception $e) {
            $this->setRedirect($redirectUrl, 'Erro: ' . $e->getMessage(), 'error');
            return false;
        }
    }
}