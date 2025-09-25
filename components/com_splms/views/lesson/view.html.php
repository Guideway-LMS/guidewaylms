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
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Uri\Uri;

class SplmsViewLesson extends HtmlView{

	protected $item;
	protected $params;

	function display($tpl = null) {
		// Assign data to the view
		$this->item = $this->get('Item');
		$app = Factory::getApplication();
		$this->params = $app->getParams();
		$menus = Factory::getApplication()->getMenu();
		$menu = $menus->getActive();

		//Joomla Component Helper & Get LMS Params
		$params = ComponentHelper::getParams('com_splms');

		// Load Lessons model
		BaseDatabaseModel::addIncludePath(JPATH_SITE.'/components/com_splms/models');

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			throw new \Exception(implode("\n", $errors), 500);
			return false;
		}

		// Load Models
		$courses_model 	= BaseDatabaseModel::getInstance('Courses', 'SplmsModel');
		$teachers_model = BaseDatabaseModel::getInstance('Teachers', 'SplmsModel');
		$lessons_model 	= BaseDatabaseModel::getInstance('Lessons', 'SplmsModel');

		// Get User
		$this->user = Factory::getUser();

		$this->lessons = $lessons_model->getLessons($this->item->course_id);
		$this->teacher = $teachers_model->getTeacher($this->item->teacher_id);
		$this->courese = $courses_model->getCourse($this->item->course_id);
		$this->isAuthorised = $courses_model->getIsbuycourse($this->user->id, $this->item->course_id);
		$this->has_complete_lesson = $lessons_model->hasCompleted($this->item->id, $this->user->id, 'lesson');

		// check authorised or free course
		if($this->item->lesson_type > 0 && !$this->isAuthorised && $this->courese->price > 0) {
			$output  = '<div class="alert alert-warning">';
			$output .= '<p>' . Text::_('COM_SPLMS_LESSON_NO_ACCESS') .'</p>';
			$output .= '<a href="' . $this->courese->url . '">' . $this->courese->title .'</a>';
			$output .= '</div>';

			echo $output;
			return;	
		}
		
		if (isset($this->teacher) && $this->teacher) {
			$this->teacher_description = strip_tags($this->teacher->description);
			if (strlen($this->teacher_description) > 400) {
		    // truncate string
				$descriptionCut = substr($this->teacher_description, 0, 340);
		    // make sure it ends in a word so assassinate doesn't become ass...
				$this->teacher_description = substr($descriptionCut, 0, strrpos($descriptionCut, ' ')).'...';
		    // Show Desription
				$this->teacher_description = $this->teacher_description;
			}else{
				$this->teacher_description = $this->teacher_description;
			}
		}

		//Generate Item Meta
        $itemMeta               = array();
        $itemMeta['title']      = $this->item->title;
        $cleanText              = $this->item->description;
        $itemMeta['metadesc']   = HTMLHelper::_('string.truncate', OutputFilter::cleanText($cleanText), 155);
        if ($this->item->vdo_thumb) {
        	$itemMeta['image']      = Uri::base() . $this->item->vdo_thumb;
        }
		SplmsHelper::itemMeta($itemMeta);
		
		parent::display($tpl);
	}

}