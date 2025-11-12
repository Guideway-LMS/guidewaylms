<?php

/**
 * @package     SP LMS
 *
 * @copyright   Copyright (C) 2010 - 2021 JoomShaper. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access!');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Form\FormField;

class JFormFieldQuizquestions extends FormField
{

	protected $type = 'Quizquestions';

	public function getQuizquestions() {
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*');
		$query->from($db->quoteName('#__splms_quizquestions'));
		$query->where($db->quoteName('published')." = 1");
		$query->where('published = 1');
		$query->order('ordering DESC');
		$db->setQuery($query);
		return $db->loadObjectList();
	}

	public function getInput() {
		$quizzes = $this->getQuizquestions();
		// $input 		   = JFactory::getApplication()->input;
		// $filter		   = $input->get('filter', NULL, 'filter');

		$quizid = '';
		if ($this->value) {
			//$quizid = $filter['quizquestion_id'];
			$quizid = $this->value;
		}

		$selected = ($quizid == '') ? 'selected' : '' ;

		$output = '';
		$output .= '<select id="'.$this->id.'" name="'.$this->name.'" onchange="this.form.submit();" class="custom-select">';
		$output .= '<option value="" ' . $selected . '>'. Text::_('COM_SPSPLMS_FILTER_QUIZQUESTIONS') .'</option>';
		foreach ($quizzes as $key => $quiz) {
			$selected = ($quiz->id == $quizid) ? 'selected' : '' ;
			$output .= '<option value="'. $quiz->id .'" ' . $selected . '>'. $quiz->title .'</option>';
		}
		$output .= '</select>';

		return $output;
	}

}
