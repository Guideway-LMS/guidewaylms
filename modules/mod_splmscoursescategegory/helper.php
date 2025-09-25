<?php
/**
 * @package com_splms
 * @subpackage  mod_splmscoursescategegory
 *
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2024 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
 */

// No Direct Access
defined ('_JEXEC') or die('Resticted Aceess');

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
class ModSplmscoursescategegoryHelper {

	public static function getCoursescategories($params) {

		// Get Course Type	
		$category_type 	= $params->get('category_type');
		$limit 			= $params->get('limit');

		$db = Factory::getDbo();
		$query = $db->getQuery(true);

		$query->select('*');
		$query->from($db->quoteName('#__splms_coursescategories'));
		$query->where($db->quoteName('published') . ' = 1');
		$query->where('language in (' . $db->quote(Factory::getLanguage()->getTag()) . ',' . $db->quote('*') . ')');
		$query->where($db->quoteName('access')." IN (" . implode( ',', Factory::getUser()->getAuthorisedViewLevels() ) . ")");

		if ($category_type== 'featured') {
			$query->where($db->quoteName('featured') . ' = 1');
		}

		$query->where($db->quoteName('published') . ' = 1');
		$query->order($db->quoteName('ordering') . ' ASC');

		if($limit) {
			$query->setLimit($limit);
		}

		$db->setQuery($query);
		$items = $db->loadObjectList();

		//Retrive number of courses
		foreach ($items as &$item) {
			$item->url = Route::_('index.php?option=com_splms&view=coursescategory&id='.$item->id.':'.$item->alias . SplmsHelper::getItemid('coursescategories'));
		}

		return $items;

	}

}
