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
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\MVC\Model\ListModel;

class SplmsModelLessiontopics extends ListModel {
	public function __construct(array $config = array()) {
		if (empty($config['filter_fields'])) {
			$config['filter_fields'] = [
				'id','a.id',
				'title', 'a.title',
				'course_id', 'a.course_id', 'course_title',
				'ordering', 'a.ordering',
				'created_by', 'a.created_by',
				'created', 'a.created',
				'published', 'a.published',
				'id', 'a.id'
			];
		}
		parent::__construct($config);
	}

	protected function populateState($ordering = 'a.ordering', $direction = 'asc') {
		$app = Factory::getApplication();
		$context = $this->context;

		$search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$courseId = $this->getUserStateFromRequest($this->context . '.filter.course_id', 'filter_course_id');
		$this->setState('filter.course_id', $courseId);

		$access = $this->getUserStateFromRequest($this->context . '.filter.access', 'filter_access');
		$this->setState('filter.access', $access);

		$published = $this->getUserStateFromRequest($this->context . '.filter.published', 'filter_published', '');
		$this->setState('filter.published', $published);

		$language = $this->getUserStateFromRequest($this->context . '.filter.language', 'filter_language', '');
		$this->setState('filter.language', $language);

		parent::populateState($ordering, $direction);
	}

	protected function getStoreId($id = '') {
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.access');
		$id .= ':' . $this->getState('filter.course_id');
		$id .= ':' . $this->getState('filter.published');
		$id .= ':' . $this->getState('filter.language');

		return parent::getStoreId($id);
	}

	protected function getListQuery() {
		$app 	= Factory::getApplication();
		$state = $this->get('State');
		$db 	= Factory::getDbo();
		$query = $db->getQuery(true);

		$query->select('a.*, l.title_native as lang');
		$query->from($db->quoteName('#__splms_lessiontopics', 'a'));
		$query->join('LEFT' , $db->quoteName('#__languages', 'l') . ' ON (' . $db->quoteName('a.language') . ' = ' . $db->quoteName('l.lang_code') . ' )');

		// Join over the categories.
		$query->select('c.title AS course_title')
			->join('LEFT', '#__splms_courses AS c ON c.id = a.course_id');
		
		// Join over the users for the checked out user.
		$query->select('uc.name AS editor')
			->join('LEFT', '#__users AS uc ON uc.id=a.checked_out');

		$query->select('ua.name AS author_name')
			->join('LEFT', '#__users AS ua ON ua.id = a.created_by');

		$query->select('ug.title AS access_title')
			->join('LEFT','#__viewlevels AS ug ON ug.id = a.access');

		if ($status = $this->getState('filter.published')) {
			if ($status != '*')
				$query->where($db->quoteName('a.published') . ' = ' . $status);
		} else {
			$query->where($db->quoteName('a.published') . ' IN (0,1)');
		}

		// Filter by a single or group of categories.
		$baselevel = 1;
		$courseId = $this->getState('filter.course_id');
		if (is_numeric($courseId)) {
			$query->where('a.course_id = ' . $db->quote($courseId));
		} elseif (is_array($courseId)) {
			ArrayHelper::toInteger($courseId);
			$courseId = implode(',', $courseId);
			$query->where('a.course_id IN (' . $courseId . ')');
		}
		
		$orderCol 	= $this->getState('list.ordering','a.ordering');
		$orderDirn = $this->getState('list.direction','desc');

		$order = $db->escape($orderCol) . ' ' . $db->escape($orderDirn);
		$query->order($order);

		return $query;
	}
}