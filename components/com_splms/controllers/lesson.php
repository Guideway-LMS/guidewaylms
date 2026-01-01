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
use Joomla\CMS\Session\Session;
use Joomla\CMS\MVC\Controller\FormController;

class SplmsControllerLesson extends FormController {

	public function __construct($config = array()) {
		parent::__construct($config);
	}

	public function getModel($name = 'Lessons', $prefix = 'SplmsModel', $config = array()) {
		return parent::getModel($name, $prefix, $config);
	}
	
	public function completeditem() {
		$model 	= $this->getModel();
		$user 	= Factory::getUser();
		$input 	= Factory::getApplication()->input;
		$output = array();

		if(!$user->id) {
			$output['status'] = false;
			$output['content'] = Text::_('COM_SPLMS_LOGIN_TO_REVIEW');
			echo json_encode($output);
			die();
		}

		$item_id 			= $input->post->get('item_id', 0, 'INT');
		$item_type 			= $input->post->get('item_type', NULL, 'STRING');
		
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
	 * Endpoint AJAX para processamento de texto via IA
	 * 
	 * Recebe texto e ação do frontend, valida CSRF token,
	 * e retorna resposta processada pelo GuidewayAIHelper.
	 * 
	 * @return void Outputs JSON response
	 * @since  1.0.0
	 */
	public function callAI()
	{
		$output = ['success' => false, 'message' => ''];

		// 1. Verificação de segurança CSRF
		if (!Session::checkToken('post')) {
			http_response_code(403);
			$output['message'] = Text::_('JINVALID_TOKEN');
			echo json_encode($output);
			die();
		}

		// 2. Verificação de usuário logado
		$user = Factory::getUser();
		if (!$user->id) {
			http_response_code(401);
			$output['message'] = Text::_('COM_SPLMS_LOGIN_TO_REVIEW');
			echo json_encode($output);
			die();
		}

		// 3. Obter parâmetros do request
		$input = Factory::getApplication()->input;
		$texto = $input->post->get('texto', '', 'RAW');
		$acao  = $input->post->get('acao', '', 'STRING');

		// Validação básica
		if (empty($texto) || empty($acao)) {
			http_response_code(400);
			$output['message'] = 'Parâmetros inválidos: texto e ação são obrigatórios.';
			echo json_encode($output);
			die();
		}

		// 4. Carregar e chamar o Helper de IA
		require_once JPATH_COMPONENT_SITE . '/helpers/GuidewayAIHelper.php';

		$result = GuidewayAIHelper::processarTexto($texto, $acao);

		// 5. Retornar resposta JSON
		echo json_encode($result);
		die();
	}

}