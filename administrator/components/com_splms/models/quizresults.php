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

class SplmsModelQuizresults extends ListModel {

	public function __construct($config = array()) {
		if (empty($config['filter_fields'])) {
			$config['filter_fields'] = array(
				'id','a.id',
				'published','a.published',
				'quizquestion_id', 'a.quizquestion_id', 'quiz_name',
				'course_id', 'a.course_id', 'course_name',
				'user_id', 'a.user_id', 'student_name',
				'user_id', 'a.user_id', 'student_name',
				'access', 'a.access', 'access_level',
				'created_on','a.created_on',
				'ordering', 'a.ordering',
			);
		}

		parent::__construct($config);
	}

	protected function populateState($ordering = 'a.ordering', $direction = 'asc') {
		$app = Factory::getApplication();
		$context = $this->context;

		$search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$published = $this->getUserStateFromRequest($this->context . '.filter.published', 'filter_published', '');
		$this->setState('filter.published', $published);

		$quizquestionId = $this->getUserStateFromRequest($this->context . '.filter.quizquestion_id', 'filter_quizquestion_id');
		$this->setState('filter.quizquestion_id', $quizquestionId);

		$courseId = $this->getUserStateFromRequest($this->context . '.filter.course_id', 'filter_course_id');
		$this->setState('filter.course_id', $courseId);

		// List state information.
		parent::populateState($ordering, $direction);
	}

	protected function getStoreId($id = '') {
		// Compile the store id.
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.published');
		$id .= ':' . $this->getState('filter.quizquestion_id');
		$id .= ':' . $this->getState('filter.course_id');

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

		$query->from('#__splms_quizresults as a');

		// Join over the student.
		$query->select('c.name AS student_name')
			->join('LEFT', '#__users AS c ON c.id=a.user_id');
		// Join over the quiz.
		$query->select('d.title AS quiz_name')
			->join('LEFT', '#__splms_quizquestions AS d ON d.id=a.quizquestion_id');
		// Join over the course.
		$query->select('e.title AS course_name')
			->join('LEFT', '#__splms_courses AS e ON e.id=a.course_id');

		// Filter options
		$courseId = $this->getState('filter.course_id');
		if (is_numeric($courseId)) {
			$query->where('a.course_id = ' . $db->quote($courseId));
		} elseif (is_array($courseId)) {
			ArrayHelper::toInteger($courseId);
			$courseId = implode(',', $courseId);
			$query->where('a.course_id IN (' . $courseId . ')');
		}

		$quizquestionId = $this->getState('filter.quizquestion_id');
		if (is_numeric($quizquestionId)) {
			$query->where('a.quizquestion_id = ' . $db->quote($quizquestionId));
		} elseif (is_array($quizquestionId)) {
			ArrayHelper::toInteger($quizquestionId);
			$quizquestionId = implode(',', $quizquestionId);
			$query->where('a.quizquestion_id IN (' . $quizquestionId . ')');
		}

		// Filter by published state
		$published = $this->getState('filter.published');

		if (is_numeric($published)) {
			$query->where('a.published = ' . (int) $published);
		} elseif ($published === '') {
			$query->where('(a.published IN (0, 1))');
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
				$query->where('(uc.name LIKE ' . $search . ')');
			}
		}

		// Add the list ordering clause.
		$orderCol = $this->getState('list.ordering', 'a.ordering');
		$orderDirn = $this->getState('list.direction', 'desc');

		$query->order($db->escape($orderCol) . ' ' . $db->escape($orderDirn));

		return $query;
	}
}
