<?php
/**
 * @package com_splms
 * @subpackage  mod_splmseventcalendar
 *
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2024 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
 */

defined ('_JEXEC') or die('Resticted Aceess');

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\HTML\HTMLHelper;

if(!class_exists('SplmsHelper')) {
	require_once JPATH_BASE . '/components/com_splms/helpers/helper.php';
}

class modSplmsEventCalendarHelper {

	public static function getEvents($params) {
		$nowDate = date('Y-m-d');

		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select(array('id', 'title', 'alias', 'event_start_date AS start'));
		$query->from($db->quoteName('#__splms_events'));
		$query->where('language in (' . $db->quote(Factory::getLanguage()->getTag()) . ',' . $db->quote('*') . ')');
		$query->where($db->quoteName('access')." IN (" . implode( ',', Factory::getUser()->getAuthorisedViewLevels() ) . ")");
		$query->where($db->quoteName('published'). " = " .$db->quote('1'));
		$query->where($db->quoteName('event_start_date')." >= ". $db->quote($nowDate));
		$query->order('event_start_date ASC');
		$query->setLimit($params->get('limit', 30));
		$db->setQuery($query);
		$items = $db->loadObjectList();

		$menuItem = Factory::getApplication()->getMenu()->getActive();
		$itemId = $menuItem->id;

		foreach ($items as &$item) {
			$item->start = HTMLHelper::_('date', $item->start, 'DATE_FORMAT_LC4');
			$item->date = HTMLHelper::_('date', $item->start, 'j-n-Y');
			$item->url  = Route::_('index.php?option=com_splms&view=event&id='.$item->id.':'.$item->alias . '&Itemid=' . $itemId, false);
		}

		return $items;
	}

}
