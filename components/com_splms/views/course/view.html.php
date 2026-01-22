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
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

class SplmsViewCourse extends HtmlView
{	
	protected $item;
	protected $params;

	function display($tpl = null) {
		// Assign data to the view
		$this->item = $this->get('Item');
		$app = Factory::getApplication();
		$this->params = $app->getParams('com_splms');
		$menus = Factory::getApplication()->getMenu();
		$menu = $menus->getActive();
		
		// merge with menu params
		if ($menu) {
			$this->params->merge($menu->getParams());
		}

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			throw new RuntimeException(implode("\n", $errors), 500);
			return false;
		}

		// legacy support
		$this->item->splms_coursescategory_id 	= $this->item->coursecategory_id;
		$this->item->splms_course_id 			= $this->item->id;

		// Load Lessons model
		BaseDatabaseModel::addIncludePath(JPATH_SITE.'/components/com_splms/models');
		$model = BaseDatabaseModel::getInstance( 'courses', 'SplmsModel' );
		$lessons_model = BaseDatabaseModel::getInstance( 'Lessons', 'SplmsModel' );

		// Get image thumb
		$this->thumb_size = strtolower($this->params->get('course_thumbnail', '480X300'));
		$this->show_related_courses = $this->params->get('related_course', 0);
		$this->review 	= $this->params->get('review', false);

		// Add Script
		$doc = Factory::getDocument();
		$doc->addScript( Uri::root(true) . '/components/com_splms/assets/js/splms.js' );

		$this->user 		= Factory::getUser();
		$this->lessons      = $lessons_model->getLessons($this->item->id);
		$this->quizzes      = $model->getQuizzes($this->item->id);
		$this->freeLesson   = $lessons_model->getCourseFreeLesson($this->item->id);
		$this->isAuthorised = $model->getIsbuycourse($this->user->id, $this->item->id);

		//Get course teacher
		$this->teachers = $model->getCourseTeachers( $this->item->id );
		foreach ($this->teachers as $this->teacher) {
			$speacility_decodes = (array) json_decode($this->teacher->specialist_in);
			if ($speacility_decodes) {
				$this->teacher->specialist_in = array ();
				$i = 0;
				foreach ($speacility_decodes as $key => $speacility_decode) {
					if ($speacility_decode->specialist_text) {
						$this->teacher->specialist_in[$i] = $speacility_decode->specialist_text;
						$i ++;
					}
				}
				$this->teacher->specialist_in = implode(', ', $this->teacher->specialist_in);
			}
		}

		// Lessons
		$sum_durations = 0;
		foreach ($this->lessons as $lesson) {
			$exploded_times = explode(':', $lesson->video_duration);
			if(count($exploded_times)> 1) {
				$duration_seconds = ($exploded_times[0] * 60) + $exploded_times[1]; 
				$sum_durations += $duration_seconds;
			}
		}

		$this->total_durations = explode(':', SplmsHelper::time_from_seconds($sum_durations));
		$this->total_teachers = count($this->teachers);
		$this->total_lessons  = count($this->lessons);
		$this->total_enrolled = $model->getEnrolled($this->item->id);

		//schedule days
		if (isset($this->item->course_schedules) && !empty($this->item->course_schedules) && !is_array($this->item->course_schedules) && SplmsHelper::isJson($this->item->course_schedules)) {
			$this->item->course_schedules = json_decode($this->item->course_schedules, TRUE);
		}

		$this->schedule_days_lang = array(
			Text::_('COM_SPLMS_DAY_SUNDAY'),
			Text::_('COM_SPLMS_DAY_MONDAY'),
			Text::_('COM_SPLMS_DAY_TUESDAY'),
			Text::_('COM_SPLMS_DAY_WEDNESDAY'),
			Text::_('COM_SPLMS_DAY_THURSDAY'),
			Text::_('COM_SPLMS_DAY_FRIDAY'),
			Text::_('COM_SPLMS_DAY_SATURDAY')
		);
		
		$this->schedule_days = array(
			'sunday', 
			'monday', 
			'tuesday', 
			'wednesday', 
			'thursday', 
			'friday', 
			'saturday'
		);

		$this->hasdays = array();
		if (is_array($this->item->course_schedules) && $this->item->course_schedules) {
			foreach ($this->item->course_schedules as $course_schedule) {
				$this->hasdays [] = $course_schedule['day'];
			}
		}

		// course info
		if (isset($this->item->course_infos) && !empty($this->item->course_infos) && !is_array($this->item->course_infos) && SplmsHelper::isJson($this->item->course_infos)) {
			$this->item->course_infos = json_decode($this->item->course_infos, TRUE);
		}

		// Related courses
		$related_courses_count = $this->params->get('related_courses_count', 3);
		$this->related_courses = $model->getRelatedCourses($this->item->title, $this->item->id, $this->item->coursecategory_id, $related_courses_count);
		foreach ($this->related_courses as &$this->related_course) {
			$this->related_course->thumb = $this->related_course->thumbnail;
		}

		// review
		$this->myReview = $model->getMyReview($this->item->id);
		$this->reviews 	= $model->getReviews($this->item->id);
		$this->ratings 	= $model->getRatings($this->item->id);

		$this->showLoadMore = false;
		if($model->getTotalReviews($this->item->id) > count($this->reviews)) {
			$this->showLoadMore = true;
		}

		// Get Currency
		$this->currency = explode(':', $this->params->get('currency', 'USD:$'));

		$this->coursePrice='';
		if ($this->item->price == 0) {
			$this->coursePrice = Text::_('COM_SPLMS_FREE');
		}else{
			$this->coursePrice = SplmsHelper::getPrice($this->item->price, $this->item->sale_price);
		}

		//Generate Item Meta
        $itemMeta               = array();
		$itemMeta['title']      = $this->item->title;
		
		if(!empty($this->item->metakey)) {
			$itemMeta['keywords'] = $this->item->metakey;
		}
		
		if(!empty($this->item->metadesc)) {
			$itemMeta['metadesc'] = $this->item->metadesc;
		} else {
			$cleanText              = $this->item->description;
			$itemMeta['metadesc']   = HTMLHelper::_('string.truncate', OutputFilter::cleanText($cleanText), 155);
		}
       
        if ($this->item->image) {
        	$itemMeta['image']      = Uri::base() . $this->item->image;
        }
        SplmsHelper::itemMeta($itemMeta);

		// Carrega o Model de Avisos
		BaseDatabaseModel::addIncludePath(JPATH_SITE . '/components/com_splms/models');
		$annModel = BaseDatabaseModel::getInstance('Announcements', 'SplmsModel');

		// Obtém os avisos
		$this->announcements = $annModel->getItems();


		parent::display($tpl);
	}
	
}
