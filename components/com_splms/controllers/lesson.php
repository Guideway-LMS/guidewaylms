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

}