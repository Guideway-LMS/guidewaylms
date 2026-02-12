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
		$lesson_id = isset($data['lesson_id']) ? $data['lesson_id'] : 0; // Receive Lesson ID

		$insert_data = $quiz_model->insertQuizResult($user_id, $quiz_id, $course_id, $total_marks, $q_result);

		if ($insert_data) {
			
			// --- GUIDEWAY CUSTOM: Check Passing Score ---
			$passed = false;
			$percentage = ($total_marks > 0) ? ($q_result / $total_marks) * 100 : 0;

			// 1. Get Lesson Passing Score
			$db = Factory::getDbo();
			$query = $db->getQuery(true)
				->select('passing_score')
				->from('#__splms_lessons')
				->where('id = ' . (int)$lesson_id);
			$db->setQuery($query);
			$lessonScore = $db->loadResult();

			// 2. Get Global Config
			$params = \Joomla\CMS\Component\ComponentHelper::getParams('com_splms');
			$globalScore = $params->get('quiz_passing_score', 70);

			// 3. Determine Required Score
			$requiredScore = ($lessonScore !== null && $lessonScore !== '') ? $lessonScore : $globalScore;

			// 4. Verify Pass
			if ($percentage >= $requiredScore) {
				$passed = true;
				// Mark as Completed
				$lessons_model = BaseDatabaseModel::getInstance('Lessons', 'SplmsModel');
				if ($lessons_model && $lesson_id) {
					// Check if already completed to avoid duplicate logic (though model handles it)
					if (!$lessons_model->hasCompleted($lesson_id, $user_id, 'lesson')) {
						$lessons_model->completedItem($lesson_id, 'lesson', $user_id);
					}
				}
			}

			$status = array(
				'success' => true,
				'passed' => $passed,
				'score' => $percentage,
				'required' => $requiredScore
			);
		} else {
             $status = array('success' => false);
        }

		echo json_encode($status);
		die();

	}


}