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
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Component\ComponentHelper;

class SplmsModelEvents extends ListModel {

	protected function getListQuery() {
		$app = Factory::getApplication();
		$user = Factory::getUser();
		// get current time
		$nowDate = Factory::getDate()->format('Y-m-d');

		$app 			= Factory::getApplication();
		$input 			= Factory::getApplication()->input;
		$params   		= $app->getMenu()->getActive()->getParams(); // get the active item
		$ordering 		= $params->get('ordering', ' DESC');
		$category_id 	= $params->get('category_id', '');

		// get post params
		$etype 		= $input->get('etype', NULL, 'WORD');
		$monthindex = $input->get('monthindex', NULL, 'INT');

		// Create a new query object.
		$db = $this->getDbo();
		$query = $db->getQuery(true);

		// Select the required fields from the table.
		$query->select('a.*');
		$query->from($db->quoteName('#__splms_events', 'a'));

		//Authorised
		$groups = implode(',', $user->getAuthorisedViewLevels());
		$query->where('a.access IN (' . $groups . ')');

		if ($category_id) {
			$query->where($db->quoteName('a.eventcategory_id')." = " . $db->quote($category_id));
		}

		if ( ($ordering=='upcoming' && !$etype) || $etype == 'upcoming' ) {
			$query->where($db->quoteName('a.event_start_date') . ' >= ' . $db->quote($nowDate));
			$query->order($db->quoteName('a.event_start_date') . ' ASC');
			$query->order($db->quoteName('a.event_time') . ' ASC');
		} elseif (($ordering=='latest' && !$etype) || $etype == 'latest') {
			$query->where($db->quoteName('a.event_start_date') . ' <= ' . $db->quote($nowDate));
			$query->order($db->quoteName('a.event_start_date') . ' DESC');
			$query->order($db->quoteName('a.event_time') . ' ASC');
		} elseif ($ordering== 'asc' && !$etype) {
			$query->order($db->quoteName('a.ordering') . ' ASC');
		} else {
			$query->order($db->quoteName('a.ordering') . ' DESC');
		}

		if($monthindex) {
			$query->where('MONTH('.$db->quoteName('a.event_start_date').')  = '. $db->quote($monthindex));
		}

		// Filter by language
		$query->where('a.language in (' . $db->quote(Factory::getLanguage()->getTag()) . ',' . $db->quote('*') . ')');
		$query->where('a.published = 1');

		return $query;
	}

	protected function populateState($ordering = null, $direction = null) {
		$app = Factory::getApplication('site');
		$params = $app->getParams();
		$this->setState('list.start', $app->input->get('limitstart', 0, 'uint'));
		$limit = $params->get('limit');
		$this->setState('list.limit', $limit);
	}

	//if item not found
	public function &getItem($id = null) {
		$item = parent::getItem($id);
		if(Factory::getApplication()->isSite()) {
			if($item->id) {
				return $item;
			} else {
				throw new \Exception(Text::_('COM_SPLMS_NO_ITEMS_FOUND'), 404);
			}
		} else {
			return $item;
		}
	}

	// Get All Events
	public static function getAllEvents() {

		// Get Thumb Size
		$cParams = ComponentHelper::getParams('com_splms');
		$thumb_size = strtolower($cParams->get('event_thumbnail', '480X300'));
		
		// get current time
		$nowDate = Factory::getDate()->format('Y-m-d');
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select($db->quoteName(array('id', 'title', 'alias', 'image', 'description', 'event_time', 'event_start_date', 'event_end_date', 'event_address', 'event_end_time','speaker_id')));
		$query->from($db->quoteName('#__splms_events'));
		$query->where($db->quoteName('published')." = 1");
		//Time sorting
		$query->where($db->qn('event_start_date')." > ".$db->quote($nowDate));
		//order
		$query->order($db->quoteName('event_start_date') . ' ASC');
		$query->order('ordering DESC');
		$db->setQuery($query);
		$results = $db->loadObjectList();

		foreach ($results as &$result) {
			$result->url = Route::_('index.php?option=com_splms&view=event&id='.$result->id.':'.$result->alias . SplmsHelper::getItemid('events'));
			//event thumb
			$filename = basename($result->image);
			$path = JPATH_BASE .'/'. dirname($result->image) . '/thumbs/' . File::stripExt($filename) . '_' . $thumb_size . '.' . File::getExt($filename);
			$src = Uri::base(true) . '/' . dirname($result->image) . '/thumbs/' . File::stripExt($filename) . '_' . $thumb_size . '.' . File::getExt($filename);

			if(File::exists($path)) {
				$result->thumb = $src;
			} else {
				$result->thumb = $result->image;	
			}

		}

		return $results;
	}


	// Get Event category by event
	public static function getEventCategory($id) {

		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select($db->quoteName(array('id', 'title', 'alias', 'image')));
		$query->from($db->quoteName('#__splms_eventcategories'));
		$query->where($db->quoteName('published')." = 1");
		$query->where($db->quoteName('eventcategory_id')." = ".$db->quote($id));
		$query->order('ordering DESC');
		$db->setQuery($query);
		$result = $db->loadObject();

		$result->url = Route::_('index.php?option=com_splms&view=eventcategory&id='.$result->id.':'.$result->alias . SplmsHelper::getItemid('events'));

		return $result;
	}

	// Get Event Speakers by event
	public static function getEventSpeakers($ids) {

		$db = Factory::getDbo();
		$query = $db->getQuery(true);

		if (is_array($ids) == false) {
			$replaceArr  	 = array("\"","[","]");
			$speakerids 	 = str_replace( $replaceArr,"", $ids);
		} else {
			$speakerids = implode(',', $ids);
		}

		if (!empty($speakerids)) {
			$query->select($db->quoteName(array('id', 'title', 'alias', 'designation', 'image')));
			$query->from($db->quoteName('#__splms_speakers'));
			$query->where($db->quoteName('published')." = 1");
			$query->where($db->quoteName('id')." IN (". $speakerids . ")");
			$query->order('ordering DESC');
			$db->setQuery($query);
			$items = $db->loadObjectList();
			foreach ($items as &$item) {
				$item->url = Route::_('index.php?option=com_splms&view=speaker&id='.$item->id.':'.$item->alias . SplmsHelper::getItemid('speakers'));
			}
		}else{
			$items='';
		}

		return $items;
	}

	//Get Events By category
	public static function getEventByCategory($eventcategory_id) {
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('a.*');
		$query->from($db->quoteName('#__splms_events', 'a'));
		$query->where($db->quoteName('a.published')." = 1");
		$query->where($db->quoteName('a.eventcategory_id')." = ".$db->quote($eventcategory_id));
		$query->order('a.ordering DESC');
		$db->setQuery($query);
		$events = $db->loadObjectList();
		
		foreach ($events as &$categoryEvent) {
			$categoryEvent->url = Route::_('index.php?option=com_splms&view=event&id='.$categoryEvent->id.':'.$categoryEvent->alias . SplmsHelper::getItemid('events'));
		}

		return $events;
	}

}
