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
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

class SplmsModelDashboard extends BaseDatabaseModel {

	// Get Orders
	public static function getOrders() {
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('COUNT(id)');
		$query->from($db->quoteName('#__splms_orders'));
		$query->where($db->quoteName('published')." = 1");
		$query->order('ordering DESC');
		$db->setQuery($query);
		$results = $db->loadResult();
		return $results;
	}

	// Get Orders
	public static function getCourses() {
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('COUNT(id)');
		$query->from($db->quoteName('#__splms_courses'));
		$query->where($db->quoteName('published')." = 1");
		$query->order('ordering DESC');
		$db->setQuery($query);
		$results = $db->loadResult();

		return $results;
	}

	// Get Orders
	public static function getLessons() {
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('COUNT(id)');
		$query->from($db->quoteName('#__splms_lessons'));
		$query->where($db->quoteName('published')." = 1");
		$query->order('ordering DESC');
		$db->setQuery($query);
		$results = $db->loadResult();

		return $results;
	}

	// Get Orders
	public static function getUsers() {
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('COUNT(id)');
		$query->from($db->quoteName('#__users'));
		$query->where($db->quoteName('block')." = 0");
		$db->setQuery($query);
		$results = $db->loadResult();

		return $results;
	}

	/**
	 * Get total number of registered student
	 *
	 * @return int
	 * @since 4.0.5
	 */
	public static function getTotalStudent() {
		$db		= Factory::getDbo();
		$sql	= 'SELECT count(ugm.user_id) total_student FROM #__user_usergroup_map ugm '. 
				  'LEFT JOIN #__users u ON u.id = ugm.user_id '.
				  'WHERE ugm.group_id=2 AND u.block=0';
		
		$db->setQuery($sql);
		return (int) $db->loadResult();
	}

	//Get total sales by day
	public static function getTotalSales() {

		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('SUM(order_payment_price)');
		$query->from($db->quoteName('#__splms_orders'));
		$query->where($db->quoteName('published')." = 1");
		$db->setQuery($query);
		$results = $db->loadResult() ?? 0.00;

		return round($results, 2);
	}
	// get courses list
	public static function getCourseList() {
		$db = Factory::getDbo();
		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('id', 'title', 'created', 'price')));
		$query->from($db->quoteName('#__splms_courses'));
		$query->where($db->quoteName('published')." = 1");
		$query->setLimit('5');
		$query->order('ordering DESC');
		$db->setQuery($query);
		$results = $db->loadObjectList();

		return $results;
	}

	//Orders List
	public static function getOrderList() {
		$db = Factory::getDbo();
		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('a.id', 'a.course_id', 'a.created', 'a.order_payment_price', 'b.title')));
		$query->from($db->quoteName('#__splms_orders', 'a'));
		$query->join('LEFT', $db->quoteName('#__splms_courses', 'b') . ' ON (' . $db->quoteName('a.course_id') . ' = ' . $db->quoteName('b.id') . ')');
		$query->where($db->quoteName('a.published')." = 1");
		$query->setLimit('5');
		$query->order('a.ordering DESC');
		$db->setQuery($query);
		$results = $db->loadObjectList();

		if ($results && count($results)) {
			return $results;
		} else {
			return;
		}
		
	}

	//Get total sales by day
	public static function getSales($day, $month, $year) {
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('SUM(order_payment_price)');
		$query->from($db->quoteName('#__splms_orders'));
		$query->where('DAY(created) = ' . $day);
		$query->where('MONTH(created) = ' . $month);
		$query->where('YEAR(created) = ' . $year);
		$query->where($db->quoteName('published')." = 1");
		$db->setQuery($query);
		$results = $db->loadResult();

		return $results;
	}
}
