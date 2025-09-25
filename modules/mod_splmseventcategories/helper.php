<?php
/**
 * @package com_splms
 * @subpackage  mod_splmseventcategories
 *
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2024 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
 */

// No Direct Access
defined ('_JEXEC') or die('Resticted Aceess');

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;

// Load Component Helper
require_once JPATH_BASE . '/components/com_splms/helpers/helper.php';
class ModSplmseventcategegoriesHelper {

	public static function getEventcategories($params) {

		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*');
		$query->from($db->quoteName('#__splms_eventcategories'));
		$query->where('language in (' . $db->quote(Factory::getLanguage()->getTag()) . ',' . $db->quote('*') . ')');
		$query->where($db->quoteName('access')." IN (" . implode( ',', Factory::getUser()->getAuthorisedViewLevels() ) . ")");
		$query->where($db->quoteName('published') . ' = 1');
		$query->order($db->quoteName('ordering') . ' ASC');
		$query->setLimit($params->get('limit', 12));
		$db->setQuery($query);
		$items = $db->loadObjectList();

		foreach ($items as &$item) {
			$item->url = $item->url = Route::_('index.php?option=com_splms&view=eventcategory&id='.$item->id.':'.$item->alias . Splmshelper::getItemid('eventcategories'));
		}

		if (empty($items)) {
			$items = Text::_('MOD_SPLMS_NO_EVENT_FOUND');
		}

		return $items;

	}
}
