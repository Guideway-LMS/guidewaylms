<?php

/**
 * @package     SP LMS
 *
 * @copyright   Copyright (C) 2010 - 2021 JoomShaper. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No Direct Access
defined ('_JEXEC') or die('Resticted Aceess');

// Load jQuery Library

$doc = JFactory::getDocument();

class SplmsViewQuizquestions extends FOFViewForm{

	public function display($tpl = null){
		// Get model
		$model = $this->getModel();
		$this->items = $model->getItemList();
		// Load Lessons model
		$courses_model = FOFModel::getTmpInstance('Courses', 'SplmsModel');
		
		foreach ($this->items as &$this->item) {
			$this->item->url 	= JRoute::_('index.php?option=com_splms&view=quizquestion&id=' . $this->item->splms_quizquestion_id . ':' . $this->item->slug . SplmsHelper::getItemid('quizquestions'));
			$this->item->cat_name = $courses_model->getCourse($this->item->splms_course_id)->title;
			
		}

		return parent::display($tpl = null);
	}

}