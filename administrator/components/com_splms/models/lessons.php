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

class SplmsModelLessons extends ListModel {

	public function __construct($config = array()) {
		if (empty($config['filter_fields'])) {
			$config['filter_fields'] = array(
				'id','a.id',
				'title','a.title',
				'checked_out', 'a.checked_out',
				'checked_out_time', 'a.checked_out_time',
				'created_by','a.created_by',
				'published','a.published',
				'course_id', 'a.course_id', 'course_title',
				'topic_id', 'a.topic_id',
				'teacher_id', 'a.teacher_id', 'teacher_title',
				'access', 'a.access', 'access_level',
				'created_on','a.created_on',
				'ordering', 'a.ordering',
				'hits', 'a.hits',
				'language','a.language',
				'category_id',
			);
		}

		parent::__construct($config);
	}

	protected function populateState($ordering = 'a.ordering', $direction = 'asc') {
		$app = Factory::getApplication();
		$context = $this->context;

		$search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$access = $this->getUserStateFromRequest($this->context . '.filter.access', 'filter_access');
		$this->setState('filter.access', $access);

		$published = $this->getUserStateFromRequest($this->context . '.filter.published', 'filter_published', '');
		$this->setState('filter.published', $published);

		$courseId = $this->getUserStateFromRequest($this->context . '.filter.course_id', 'filter_course_id');
		$this->setState('filter.course_id', $courseId);

		$topicId = $this->getUserStateFromRequest($this->context . '.filter.topic_id', 'filter_topic_id');
		$this->setState('filter.topic_id', $topicId);

		$teacherId = $this->getUserStateFromRequest($this->context . '.filter.teacher_id', 'filter_teacher_id');
		$this->setState('filter.course_id', $teacherId);

		$language = $this->getUserStateFromRequest($this->context . '.filter.language', 'filter_language', '');
		$this->setState('filter.language', $language);

		// List state information.
		parent::populateState($ordering, $direction);
	}

	protected function getStoreId($id = '') {
		// Compile the store id.
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.access');
		$id .= ':' . $this->getState('filter.published');
		$id .= ':' . $this->getState('filter.course_id');
		$id .= ':' . $this->getState('filter.topic_id');
		$id .= ':' . $this->getState('filter.teacher_id');
		$id .= ':' . $this->getState('filter.language');

		return parent::getStoreId($id);
	}

	/**
	* Method to build an SQL query to load the list data.
	*
	* @return      string  An SQL query
	*/
	protected function getListQuery() {
		// Initialize variables.
		$app = Factory::getApplication();
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select(
			$this->getState(
				'list.select',
				'a.*'
			)
		);

		$query->from('#__splms_lessons as a');

		$query->select('l.title AS language_title')
			->join('LEFT', $db->quoteName('#__languages') . ' AS l ON l.lang_code = a.language');

			// Join over the users for the checked out user.
		$query->select('uc.name AS editor')
			->join('LEFT', '#__users AS uc ON uc.id=a.checked_out');

		$query->select('ua.name AS author_name')
			->join('LEFT', '#__users AS ua ON ua.id = a.created_by');

		$query->select('ug.title AS access_title')
			->join('LEFT','#__viewlevels AS ug ON ug.id = a.access');

		// Join over the categories.
		$query->select('c.title AS course_title')
			->join('LEFT', '#__splms_courses AS c ON c.id = a.course_id');

		// Join over the teacher.
		$query->select('d.title AS teacher_title')
			->join('LEFT', '#__splms_teachers AS d ON d.id = a.teacher_id');

		// Filter by published state
		$published = $this->getState('filter.published');

		if (is_numeric($published)) {
			$query->where('a.published = ' . (int) $published);
		} elseif ($published === '') {
			$query->where('(a.published IN (0, 1))');
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

		// topic id
		$topicId = $this->getState('filter.topic_id');
		if (is_numeric($topicId)) {
			$query->where('a.topic_id = ' . $db->quote($topicId));
		} elseif (is_array($topicId)) {
			ArrayHelper::toInteger($topicId);
			$topicId = implode(',', $topicId);
			$query->where('a.topic_id IN (' . $topicId . ')');
		}

		//teacher
		$teacherId = $this->getState('filter.teacher_id');
		if (is_numeric($teacherId)) {
			$query->where('a.teacher_id = ' . $db->quote($teacherId));
		} elseif (is_array($teacherId)) {
			ArrayHelper::toInteger($teacherId);
			$teacherId = implode(',', $teacherId);
			$query->where('a.teacher_id IN (' . $teacherId . ')');
		}

		// Filter by language
		if ($language = $this->getState('filter.language'))
		{
			$query->where('a.language = ' . $db->quote($language));
		}

		$search = $this->getState('filter.search');
		if (!empty($search)) {
			if (stripos($search, 'id:') === 0) {
				$query->where('a.id = ' . (int) substr($search, 3));
			} elseif (stripos($search, 'author:') === 0) {
				$search = $db->quote('%' . $db->escape(substr($search, 7), true) . '%');
				$query->where('(uc.name LIKE ' . $search . ' OR uc.username LIKE ' . $search . ')');
			} else {
				$search = $db->quote('%' . $db->escape($search, true) . '%');
				$query->where('(a.title LIKE ' . $search . ')');
			}
		}

		// Add the list ordering clause.
		$orderCol = $this->getState('list.ordering', 'a.ordering');
		$orderDirn = $this->getState('list.direction', 'desc');

		$query->order($db->escape($orderCol) . ' ' . $db->escape($orderDirn));

		return $query;
	}

	// Get Lessons by course ID
	public static function getLessons($lesson_id) {
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select($db->quoteName(array('id', 'title', 'lesson_type', 'course_id')));
		$query->from($db->quoteName('#__splms_lessons'));
		$query->where($db->quoteName('published')." = 1");
		$query->where($db->quoteName('id')." = ".$db->quote($lesson_id));
		$db->setQuery($query);
		$results = $db->loadObject();
		return $results;
	}
}
