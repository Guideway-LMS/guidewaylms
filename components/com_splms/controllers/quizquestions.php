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
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\MVC\Controller\FormController;

class SplmsControllerQuizquestions extends FormController{

	public function __construct($config = array()){
		parent::__construct($config);
	}

	public function submit_result() {

		$status = false;
		// Load Lessons model
		BaseDatabaseModel::addIncludePath(JPATH_SITE.'/components/com_splms/models');
		// Load Quiz model
		$quiz_model = BaseDatabaseModel::getInstance('quizquestions', 'SplmsModel');
		
		$input = Factory::getApplication()->input;
		$data = $input->post->get('data', NULL, 'ARRAY');

		$user_id = $data['user_id'];
		$quiz_id = $data['quiz_id'];
		$course_id = $data['course_id'];
		$total_marks = $data['total_marks'];
		$q_result = $data['q_result'];

		$insert_data = $quiz_model->insertQuizResult($user_id, $quiz_id, $course_id, $total_marks, $q_result);

		if ($insert_data) {
			$status = true;
		}

		echo json_encode($status);
		die();

	}


}