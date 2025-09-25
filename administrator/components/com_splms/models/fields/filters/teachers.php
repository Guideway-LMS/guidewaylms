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

class JFormFieldTeachers extends FormField
{

	protected $type = 'Teachers';

	public function getTeachers() {

		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*');
		$query->from($db->quoteName('#__splms_teachers'));
		$query->where($db->quoteName('published')." = 1");
		$query->where('published = 1');
		$query->order('ordering DESC');
		$db->setQuery($query);

		return $db->loadObjectList();

	}

	public function getInput() {
		$teachers = $this->getTeachers();
		
		$teacherid = '';
		if ($this->value) {
			$teacherid = $this->value;
		}
		$selected = ($teacherid == '') ? 'selected' : '' ;

		$output = '';
		$output .= '<select id="'.$this->id.'" name="'.$this->name.'" onchange="this.form.submit();" class="custom-select">';
		$output .= '<option value="" ' . $selected . '>'. Text::_('COM_SPSPLMS_FILTER_TEACHERS') .'</option>';
		foreach ($teachers as $key => $teacher) {
			$selected = ($teacher->id == $teacherid) ? 'selected' : '' ;
			$output .= '<option value="'. $teacher->id .'" ' . $selected . '>'. $teacher->title .'</option>';
		}
		$output .= '</select>';

		return $output;
	}

}
