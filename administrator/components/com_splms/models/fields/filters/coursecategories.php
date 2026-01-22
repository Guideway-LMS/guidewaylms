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


class JFormFieldCoursecategories extends FormField
{
	protected $type = 'Coursecategories';

	public function getCategories() {

		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*');
		$query->from($db->quoteName('#__splms_coursescategories'));
		$query->where($db->quoteName('published')." = 1");
		$query->where('published = 1');
		$query->order('ordering DESC');
		$db->setQuery($query);

		return $db->loadObjectList();

	}

	public function getInput() {
		$categories    = $this->getCategories();

		$catid = '';
		if ($this->value) {
			$catid = $this->value;
		}

		$selected = ($catid == '') ? 'selected' : '' ;
		$output = '';
		$output .= '<select id="'.$this->id.'" name="'.$this->name.'" onchange="this.form.submit();" class="custom-select">';
		$output .= '<option value="" ' . $selected . '>'. Text::_('COM_SPSPLMS_FILTER_COURSE_CATEGORY') .'</option>';
		foreach ($categories as $key => $category) {
			$selected = ($category->id == $catid) ? 'selected' : '' ;
			$output .= '<option value="'. $category->id .'" ' . $selected . '>'. $category->title .'</option>';
		}
		$output .= '</select>';

		return $output;
	}

}
