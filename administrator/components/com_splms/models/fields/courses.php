<?php

/**
 * @package     SP Movie Database
 * @copyright   Copyright (C) 2010 - 2021 JoomShaper. All rights reserved.
 * @license     GNU General Public License version 2 or later.
 */

defined('_JEXEC') or die('Restricted access!');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Form\FormField;
class JFormFieldFilterCourses extends FormField
{
	protected $type = 'Courses';
	public function getCategories() {
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*');
		$query->from($db->quoteName('#__splms_courses'));
		$query->where($db->quoteName('published')." = 1");
		$query->where('published = 1');
		$query->order('ordering DESC');
		$db->setQuery($query);
		return $db->loadObjectList();
	}

	public function getInput() {
		$courses 	   = $this->getCategories();
		$input 		   = Factory::getApplication()->input;
		$filter		   = $input->get('filter', NULL, 'filter');

		$courseid = '';
		if ($filter) {
			$courseid = $filter['course_id'];
		}

		$selected = ($courseid == '') ? 'selected' : '' ;
		$output = '';
		$output .= '<select id="'.$this->id.'" name="'.$this->name.'" onchange="this.form.submit();" class="custom-select">';
		$output .= '<option value="" ' . $selected . '>'. Text::_('JALL') .'</option>';
		foreach ($courses as $key => $course) {
			$selected = ($course->id == $courseid) ? 'selected' : '' ;
			$output .= '<option value="'. $course->id .'" ' . $selected . '>'. $course->title .'</option>';
		}
		$output .= '</select>';

		return $output;
	}

}
