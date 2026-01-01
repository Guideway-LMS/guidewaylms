<?php
/**
 * @package com_splms
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2024 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
 */

// No Direct Access
defined ('_JEXEC') or die('Resticted Aceess');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\MVC\Controller\FormController;

jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');

class SplmsControllerLesson extends FormController {

	public function __construct($config = array()) {
		parent::__construct($config);
	}

	protected function allowAdd($data = array()) {
		return parent::allowAdd($data);
	}

    protected function allowEdit($data = array(), $key = 'id') {
		$recordId = (int) isset($data[$key]) ? $data[$key] : 0;
		$user = Factory::getUser();
		// Zero record (id:0), return component edit permission by calling parent controller method
		if (!$recordId) {
			return parent::allowEdit($data, $key);
		}
		// Check edit on the record asset (explicit or inherited)
		if ($user->authorise('core.edit', 'com_splms.lesson.' . $recordId)) {
			return true;
		}
		// Check edit own on the record asset (explicit or inherited)
		if ($user->authorise('core.edit.own', 'com_splms.lesson.' . $recordId)){
			// Existing record already has an owner, get it
			$record = $this->getModel()->getItem($recordId);
			if (empty($record)) {
				return false;
			}
			// Grant if current user is owner of the record
			return $user->id == $record->created_by;
		} 
		return false;
	}

	 // Delete File
    public function delete_media() {

        $model      = $this->getModel();
        $input      = Factory::getApplication()->input;
        $filePath   = $input->post->get('filePath', NULL, 'STRING');
        $itemID     = $input->post->get('itemId', NULL, 'INT');

        $report = array();
        $report['status'] = false;

        $report['itemID'] = $itemID;

        if(isset($filePath) && $filePath) {
            $report['delete'] = $model->removeAttachmentByID($itemID);
            if(File::exists($filePath)) {
                // Delete thumb
                if (File::delete($filePath)) {
                    $report['status']   = true;
                    $report['message']  = Text::_('SPLMS_ATTACHMENT_SUCCESSFULLY_REMOVED');
                }

            } else {
                $report['status'] = false;
                $report['message']  = Text::_('SPLMS_ATTACHMENT_ISNOT_EXIST');
            }
        } else {
            $report['status'] = false;
            $report['message']  = Text::_('SPLMS_NO_ATTACHMENT_FOUND');
        }

        echo json_encode($report);
        die;
    }

	/**
	 * Endpoint AJAX para processamento de texto via IA
	 * 
	 * Recebe texto e ação do admin, valida CSRF token,
	 * e retorna resposta processada pelo GuidewayAIHelper.
	 * 
	 * @return void Outputs JSON response
	 * @since  1.0.0
	 */
	public function callAI()
	{
		// Force JSON response header
		header('Content-Type: application/json');
		$output = ['success' => false, 'message' => ''];

		try {
			// 1. Verificação de segurança CSRF
			if (!Session::checkToken('post')) {
				throw new Exception(Text::_('JINVALID_TOKEN'));
			}

			// 2. Verificação de usuário logado e com permissão de edição
			$user = Factory::getUser();
			if (!$user->id) {
				throw new Exception('Usuário não autenticado.');
			}

			// 3. Obter parâmetros do request
			$input = Factory::getApplication()->input;
			$texto = $input->post->get('texto', '', 'RAW');
			$acao  = $input->post->get('acao', '', 'STRING');

			// Validação básica
			if (empty($texto) || empty($acao)) {
				throw new Exception('Parâmetros inválidos: texto e ação são obrigatórios.');
			}

			// 4. Carregar e chamar o Helper de IA (do componente site)
			$helperPath = JPATH_ROOT . '/components/com_splms/helpers/GuidewayAIHelper.php';
			if (!file_exists($helperPath)) {
				throw new Exception('Helper não encontrado em: ' . $helperPath);
			}
			require_once $helperPath;

			if (!class_exists('GuidewayAIHelper')) {
				throw new Exception('Classe GuidewayAIHelper não encontrada.');
			}

			$result = GuidewayAIHelper::processarTexto($texto, $acao);

			// 5. Retornar resposta JSON
			echo json_encode($result);
			die();

		} catch (\Throwable $e) {
			// Catch ALL errors (including parse errors/fatal errors if possible)
			$output['message'] = 'Erro no servidor: ' . $e->getMessage();
			$output['debug_file'] = $e->getFile();
			$output['debug_line'] = $e->getLine();
			http_response_code(500); // Still 500 but with content
			echo json_encode($output);
			die();
		}
	}

}
