<?php
/**
* @package com_splms
* @subpackage  mod_splmupcomingevents
*
* @author JoomShaper http://www.joomshaper.com
* @copyright Copyright (c) 2010 - 2024 JoomShaper
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

// No Direct Access
defined ('_JEXEC') or die('Resticted Aceess');

class ModSplmsupcomingeventsHelper {

	public static function getUpcomingEvents($params) {

		// Load Lessons model
		BaseDatabaseModel::addIncludePath(JPATH_SITE.'/components/com_splms/models');
		$events_model = BaseDatabaseModel::getInstance( 'Events', 'SplmsModel' );

		// Now time
		$nowDate = date('Y-m-d');
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*');
		$query->from($db->quoteName('#__splms_events'));
		$query->where('language in (' . $db->quote(Factory::getLanguage()->getTag()) . ',' . $db->quote('*') . ')');
		$query->where($db->quoteName('access')." IN (" . implode( ',', Factory::getUser()->getAuthorisedViewLevels() ) . ")");
		$query->where($db->quoteName('published'). " = " .$db->quote('1'));
		$query->where($db->quoteName('event_start_date')." > ". $db->quote($nowDate));
		$query->order('event_start_date'. ' ASC');
		$query->setLimit($params->get('limit', 5));
		$db->setQuery($query);
		$items = $db->loadObjectList();

		foreach ($items as &$item) {
			$item->url  = Route::_('index.php?option=com_splms&view=event&id='.$item->id.':'.$item->alias . SplmsHelper::getItemid('events'));
			$item->speakers = $events_model->getEventSpeakers($item->speaker_id);
		}

		return $items;
	}

}
