<?php

/**
 * @package com_splms
 * @subpackage  mod_splmspersons
 *
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2024 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
 */

// No Direct Access
defined ('_JEXEC') or die('Resticted Aceess');

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;

class ModSplmspersonsHelper {

	// Get teachers
	public static function getTeachers($params) {

		$item_type = $params->get('person_type', 'teachers');
		$ordering = $params->get('ordering', 'DESC');

		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select(array('*'));
		if ($item_type == 'spekaers') {
			$query->from($db->quoteName('#__splms_speakers'));
		} else {
			$query->from($db->quoteName('#__splms_teachers'));
		}

		$query->where('language in (' . $db->quote(Factory::getLanguage()->getTag()) . ',' . $db->quote('*') . ')');
		$query->where($db->quoteName('access')." IN (" . implode( ',', Factory::getUser()->getAuthorisedViewLevels() ) . ")");
		$query->where($db->quoteName('published')." = 1");
		$query->setLimit($params->get('limit', 4));
		$query->order('ordering ' . $ordering);
		$db->setQuery($query);
		$results = $db->loadObjectList();

		foreach ($results as $result) {
			if ($item_type == 'spekaers') {
				$result->url = Route::_('index.php?option=com_splms&view=speaker&id='.$result->id.':'.$result->alias . SplmsHelper::getItemid('speakers'));
			} else {
				$result->url = Route::_('index.php?option=com_splms&view=teacher&id='.$result->id.':'.$result->alias . SplmsHelper::getItemid('teachers'));
			}
		}

		return $results;
	}


}
