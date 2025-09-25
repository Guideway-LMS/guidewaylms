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

class SplmsTableEvent extends Table {

	public function __construct(&$db) {
		parent::__construct('#__splms_events', 'id', $db);
	}

	public function store($updateNulls = true) {
		$date = Factory::getDate()->toSql();
		$user = Factory::getUser();

		if ($this->id)
		{
			$this->modified		= $date;
			$this->modified_by		= $user->get('id');
		}
		else
		{
			if (!(int) $this->created)
			{
				$this->created = $date;
			}
			if (empty($this->created_by))
			{
				$this->created_by = $user->get('id');
			}
			if (!(int) $this->modified)
			{
				$this->modified = $date;
			}
			if (empty($this->modified_by))
			{
				$this->modified_by = $user->get('id');
			}
		}

		if(empty($this->price) || $this->price == "")
		{
			$this->price = 0;
		}
		if (empty($this->event_time)) 
		{
			$this->event_time = NULL;
		}
		if (empty($this->event_end_time)) 
		{
			$this->event_end_time = NULL;
		}
		if (empty($this->event_start_date)) {
			$this->event_start_date = NULL;
		}
		if (empty($this->event_end_date)) {
			$this->event_end_date = NULL;
		}
		if(empty($this->eventcategory_id))
		{
			$this->eventcategory_id = 0;
		}

		// Verify that the alias is unique
		$table = Table::getInstance('Event', 'SplmsTable');

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

		$this->alias = ApplicationHelper::stringURLSafe($this->alias, $this->language);

		if (trim(str_replace('-', '', $this->alias)) == '') {
			$this->alias = Factory::getDate()->format('Y-m-d-H-i-s');
		}

		// value before save //
		// has speakers
		if (is_array($this->speaker_id)) {
			if (!empty($this->speaker_id)) {
				$this->speaker_id = json_encode($this->speaker_id);
			}
		}
		if (is_null($this->speaker_id) || empty($this->speaker_id)){
			$this->speaker_id = '';
		}

		// gallery image
		if (is_array($this->gallery)) {
			if (!empty($this->gallery)) {
				$this->gallery = json_encode($this->gallery);
			}
		}
		if (is_null($this->gallery) || empty($this->gallery)) {
			$this->gallery = '';
		}
		
		//topics
		if (is_array($this->topics)) {
			if (!empty($this->topics)) {
				$this->topics = json_encode($this->topics);
			}
		}
		if (is_null($this->topics) || empty($this->topics)) {
			$this->topics = '';
		}
		// has price
		if (is_array($this->pricing_tables)) {
			if (!empty($this->pricing_tables)) {
				$this->pricing_tables = json_encode($this->pricing_tables);
			}
		}
		if (is_null($this->pricing_tables) || empty($this->pricing_tables)) {
			$this->pricing_tables = '';
		}

		//Generate Thumbnails
		if($this->image)
		{
			$params = ComponentHelper::getParams('com_splms');
			$thumb = $params->get('event_thumbnail', '480X300');
			$thumb_small = $params->get('event_thumbnail_small', '100X60');
			
			$filteredImage = explode('#', $this->image);
			$this->image = str_replace('%20', ' ', $filteredImage[0]);

			if(!is_null($this->image)) {
				$image = JPATH_ROOT . '/' . $this->image;
				$sizes = array($thumb, $thumb_small);
				$image = new Image($image);
				$image->createThumbs($sizes, 5);
			}
		}

		return true;
	}

	public function onAfterLoad(&$result) {
		// Convert plan to an array speakers
		if(!is_array($this->speaker_id)) {
			if(!empty($this->speaker_id)) {
				$this->speaker_id = json_decode($this->speaker_id, true);
			}
		}
		if(is_null($this->speaker_id) || empty($this->speaker_id)) {
			$this->speaker_id = array();
		}
		// gallery
		if(!is_array($this->gallery)) {
			if(!empty($this->gallery)) {
				$this->gallery = json_decode($this->gallery, true);
			}
		}
		if(is_null($this->gallery) || empty($this->gallery)) {
			$this->gallery = array();
		}
		// topics
		if(!is_array($this->topics)) {
			if(!empty($this->topics)) {
				$this->topics = json_decode($this->topics, true);
			}
		}
		if(is_null($this->topics) || empty($this->topics)) {
			$this->topics = array();
		}

		// prices
		if(!is_array($this->pricing_tables)) {
			if(!empty($this->pricing_tables)) {
				$this->pricing_tables = json_decode($this->pricing_tables, true);
			}
		}
		if(is_null($this->pricing_tables) || empty($this->pricing_tables)) {
			$this->pricing_tables = array();
		}

		return parent::onAfterLoad($result);
	}

}
