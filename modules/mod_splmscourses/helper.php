<?php
/**
 * @package com_splms
 * @subpackage  mod_splmscourses
 *
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2024 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
 */

// No Direct Access
defined ('_JEXEC') or die('Resticted Aceess');

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;

class ModSplmscoursesHelper {

	public static function getCourses($params) {

		// Get Course Type
		$course_type = $params->get('course_type', '');

		$db = Factory::getDbo();
		$query = $db->getQuery(true);

		$query->select('a.*');
		$query->select($db->quoteName('b.title', 'category_name'));
		$query->from($db->quoteName('#__splms_courses', 'a'));
		$query->join('LEFT', $db->quoteName('#__splms_coursescategories', 'b') . ' ON (' . $db->quoteName('a.coursecategory_id') . ' = ' . $db->quoteName('b.id') . ')');
		$query->where($db->quoteName('a.published') . ' = 1');
		$query->where('a.language in (' . $db->quote(Factory::getLanguage()->getTag()) . ',' . $db->quote('*') . ')');
		$query->where($db->quoteName('a.access')." IN (" . implode( ',', Factory::getUser()->getAuthorisedViewLevels() ) . ")");

		if ($course_type=='course_paid') {
			$query->where($db->quoteName('a.price') . ' > 1');
			$query->order($db->quoteName('a.ordering') . ' ASC');
		} elseif ($course_type =='course_free') {
			$query->where($db->quoteName('a.price') . ' = 0');
			$query->order($db->quoteName('a.ordering') . ' ASC');
		} elseif ($course_type == 'course_featured') {
			$query->where($db->quoteName('a.featured_course') . ' = 1');
			$query->order($db->quoteName('a.ordering') . ' ASC');
		} elseif ($course_type == 'course_discount') {
			$query->where($db->quoteName('a.sale_price') . ' < ' . $db->quoteName('a.price'));
			$query->where($db->quoteName('a.sale_price') . ' != ' . $db->quote(0.00));
			$query->order($db->quoteName('a.ordering') . ' ASC');
		} else {
			$query->order($db->quoteName('a.ordering') . ' ASC');
		}

		$query->setLimit($params->get('limit', 6));
		$db->setQuery($query);
		$items = $db->loadObjectList();

		if ($course_type == 'course_popular') {
			$items = self::getPopularCourses($params->get('limit', 6));

		}

		foreach ($items as &$item) {
			$item->url = Route::_('index.php?option=com_splms&view=course&id='.$item->id.':'.$item->alias . SplmsHelper::getItemid('courses'));
		}

		return $items;
	}

	// Get Popular Courses
	private static function getPopularCourses($limit = 6) {

		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select( array('a.*', 'count(b.course_id) AS count_course'  ));
		$query->from($db->quoteName('#__splms_courses', 'a'));
		$query->join('LEFT', $db->quoteName('#__splms_orders', 'b') . ' ON (' . $db->quoteName('a.id') . ' = ' . $db->quoteName('b.course_id') . ')');
		$query->where('a.language in (' . $db->quote(Factory::getLanguage()->getTag()) . ',' . $db->quote('*') . ')');
		$query->where($db->quoteName('a.access')." IN (" . implode( ',', Factory::getUser()->getAuthorisedViewLevels() ) . ")");
		$query->where($db->quoteName('b.published') . ' = 1');
		$query->group($db->quoteName('b.course_id'));
		$query->order($db->quoteName('count_course') . ' DESC');
		$query->setLimit($limit);
		$db->setQuery($query);
		$items = $db->loadObjectList();

		return $items;

	}

}
