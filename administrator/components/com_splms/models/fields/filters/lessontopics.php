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

class JFormFieldLessontopics extends FormField
{

	protected $type = 'Lessontopics';

	public function getLessonTopics() {

		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*');
		$query->from($db->quoteName('#__splms_lessiontopics'));
		$query->where($db->quoteName('published')." = 1");
		$query->where('published = 1');
		$query->order('ordering DESC');
		$db->setQuery($query);

		return $db->loadObjectList();
	}

	public function getInput() {
		$lesson_topics = $this->getLessonTopics();
		
		$topicid = '';
		if ($this->value) {
			$topicid = $this->value;
		}
		$selected = ($topicid == '') ? 'selected' : '' ;

		$output = '';
		$output .= '<select id="'.$this->id.'" name="'.$this->name.'" onchange="this.form.submit();" class="custom-select">';
		$output .= '<option value="" ' . $selected . '>'. Text::_('COM_SPSPLMS_FILTER_LESSON_TOPICS') .'</option>';
		foreach ($lesson_topics as $key => $lesson_topic) {
			$selected = ($lesson_topic->id == $topicid) ? 'selected' : '' ;
			$output .= '<option value="'. $lesson_topic->id .'" ' . $selected . '>'. $lesson_topic->title .'</option>';
		}
		$output .= '</select>';

		return $output;
	}

}
