<?php
/**
 * @package com_splms
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2024 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Image\Image;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Application\ApplicationHelper;

class SplmsTableCourse extends Table {

	public function __construct(&$db) {
		parent::__construct('#__splms_courses', 'id', $db);
	}

	public function store($updateNulls = false) {
		$date = Factory::getDate()->toSql();
		$user = Factory::getUser();

		if ($this->id) {
			$this->modified    = $date;
			$this->modified_by = $user->get('id');
		} else {
			if (!(int) $this->created) {
				$this->created = $date;
			}
			if (empty($this->created_by)) {
				$this->created_by = $user->get('id');
			}
			if (!(int) $this->modified) {
				$this->modified = $date;
			}
			if (empty($this->modified_by)) {
				$this->modified_by = $user->get('id');
			}
		}

		

		// Verify that the alias is unique
		$table = Table::getInstance('Course', 'SplmsTable');

		if ($table->load(array('alias' => $this->alias)) && ($table->id != $this->id || $this->id == 0)) {
			$this->setError(Text::_('COM_SPLMS_ERROR_UNIQUE_ALIAS'));

			return false;
		}

		return parent::store($updateNulls);
	}

	public function check() {
		// Check for valid name.
		if (trim($this->title) == '') {
			throw new UnexpectedValueException(sprintf('The title is empty'));
		}

		if (empty($this->alias)) {
			$this->alias = $this->title;
		}

		if (empty($this->course_sub_title)) {
			$this->course_sub_title = '';
		}

		if (empty($this->coursecategory_id))
		{
			$this->coursecategory_id = 0;
		}

		if (empty($this->download))
		{
			$this->download = 0;
		}

		if (empty($this->admission_deadline))
		{
			$this->admission_deadline = '0000-00-00 00:00:00';
		}

		if(empty($this->price) || $this->price < 0)
		{
			$this->price = 0.00;
		}
		if((empty($this->sale_price) || $this->sale_price < 0) || (empty($this->price) || $this->price < 0) || ($this->price <= $this->sale_price) )
		{
			$this->sale_price = 0.00;
		}

		$this->alias = ApplicationHelper::stringURLSafe($this->alias, $this->language);

		if (trim(str_replace('-', '', $this->alias)) == '') {
			$this->alias = Factory::getDate()->format('Y-m-d-H-i-s');
		}

		if (strlen($this->short_description) > 255)
		{
			throw new UnexpectedValueException(sprintf('Short description is too long. please make it short.'));
		}

		//course_schedules
		if (is_array($this->course_schedules)){
			if (!empty($this->course_schedules)){
				$this->course_schedules = json_encode($this->course_schedules);
			}
		}
		if (is_null($this->course_schedules) || empty($this->course_schedules)){
			$this->course_schedules = '';
		}

		//course info
		if (is_array($this->course_infos)){
			if (!empty($this->course_infos)){
				$this->course_infos = json_encode($this->course_infos);
			}
		}
		if (is_null($this->course_infos) || empty($this->course_infos)){
			$this->course_infos = '';
		}

		//Generate Thumbnails
		if($this->image) {
			$params 		= ComponentHelper::getParams('com_splms');
			$thumb 			= $params->get('course_thumbnail', '480X300');
			$thumb_small 	= $params->get('course_thumbnail_small', '100X60');

			$filteredImage = explode('#', $this->image);
			$this->image = str_replace('%20', ' ', $filteredImage[0]);

			if (isset($this->image) && $this->image) {
				$image = JPATH_ROOT . '/' . $this->image;
				$sizes = array($thumb, $thumb_small);
				$image = new Image($image);
				$image->createThumbs($sizes, 5);
			}
		}

		return true;
	}

	public function onAfterLoad(&$result) {
		// course schedules
		if(!is_array($this->course_schedules)) {
			if(!empty($this->course_schedules)) {
				$this->course_schedules = json_decode($this->course_schedules, true);
			}
		}
		if(is_null($this->course_schedules) || empty($this->course_schedules)) {
			$this->course_schedules = array();
		}

		// course identification
		if(!is_array($this->course_infos)) {
			if(!empty($this->course_infos)) {
				$this->course_infos = json_decode($this->course_infos, true);
			}
		}
		if(is_null($this->course_infos) || empty($this->course_infos)) {
			$this->course_infos = array();
		}

		return parent::onAfterLoad($result);
	}

}
