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
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\ListModel;

class SplmsModelQuizquestions extends ListModel {

	protected function getListQuery() {
		$app = Factory::getApplication();
		$user = Factory::getUser();

		$db = $this->getDbo();
		$query = $db->getQuery(true);

		// Select the required fields from the table.
		$query->select('a.*');
		$query->from($db->quoteName('#__splms_quizquestions', 'a'));

		//Authorised
		$groups = implode(',', $user->getAuthorisedViewLevels());
		$query->where('a.access IN (' . $groups . ')');

		// Filter by language
		$query->where('a.language in (' . $db->quote(Factory::getLanguage()->getTag()) . ',' . $db->quote('*') . ')');
		$query->where('a.published = 1');
		$query->order('a.ordering ASC');

		return $query;
	}

	protected function populateState($ordering = null, $direction = null) {
		$app = Factory::getApplication('site');
		$params = $app->getParams();
		$this->setState('list.start', $app->input->get('limitstart', 0, 'uint'));
		$limit = $params->get('limit');
		$this->setState('list.limit', $limit);
	}

	//if item not found
	public function &getItem($id = null) {
		$item = parent::getItem($id);
		if(Factory::getApplication()->isSite()) {
			if($item->id) {
				return $item;
			} else {
				throw new \Exception(Text::_('COM_SPLMS_NO_ITEMS_FOUND'), 404);
			}
		} else {
			return $item;
		}
	}

	// Get Quiz results by user id
	public function getQuizResult($user_id) {
		$db = Factory::getDbo();
		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('a.id', 'a.point', 'a.total_marks', 'a.quizquestion_id', 'a.course_id')));
		$query->select($db->quoteName('b.title', 'quiz_name'));
		$query->select($db->quoteName('c.title', 'course_name'));
		$query->select($db->quoteName('c.alias', 'course_slug'));
    	$query->from($db->quoteName('#__splms_quizresults', 'a'));
    	$query->join('LEFT', $db->quoteName('#__splms_quizquestions', 'b') . ' ON (' . $db->quoteName('a.quizquestion_id') . ' = ' . $db->quoteName('b.id') . ')');
    	$query->join('LEFT', $db->quoteName('#__splms_courses', 'c') . ' ON (' . $db->quoteName('a.course_id') . ' = ' . $db->quoteName('c.id') . ')');
    	$query->where($db->quoteName('a.user_id')." = ".$db->quote($user_id));
    	$query->where($db->quoteName('a.published')." = 1");

		$db->setQuery($query);
		$results = $db->loadObjectList();
		
		foreach ($results as &$result) {
			$result->course_url = Route::_('index.php?option=com_splms&view=course&id='.$result->course_id.':'.$result->course_slug . SplmsHelper::getItemid('courses'));
		}

		return $results;
	}

	// Get quiz by quiz ID and user id
	public static function getQuizById($user_id, $quiz_id) {
		$db = Factory::getDbo();
		// Create a new query object.
		$query = $db->getQuery(true);
		$query->select($db->quoteName(array('id', 'point', 'total_marks', 'quizquestion_id', 'course_id')));
		$query->from($db->quoteName('#__splms_quizresults'));
		$query->where($db->quoteName('published')." = 1");
		$query->where($db->quoteName('user_id')." = ".$db->quote($user_id));
		$query->where($db->quoteName('quizquestion_id')." = ".$db->quote($quiz_id));
		$query->setLimit(1);
		$query->order('ordering ASC');
		$db->setQuery($query);
		// Load the results as a list of stdClass objects (see later for more options on retrieving data).
		$result = $db->loadObject();

		return $result;
	}

	// Get number of attempts for a quiz by user id
	public static function getQuizAttempts($user_id, $quiz_id) {
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		
		$query->select('COUNT(id)');
		$query->from($db->quoteName('#__splms_quizresults'));
		$query->where($db->quoteName('published')." = 1");
		$query->where($db->quoteName('user_id')." = ".$db->quote($user_id));
		$query->where($db->quoteName('quizquestion_id')." = ".$db->quote($quiz_id));
		
		$db->setQuery($query);
		return (int)$db->loadResult();
	}

	// Get best score for a quiz by user id
	public static function getBestQuizResult($user_id, $quiz_id) {
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		
		$query->select($db->quoteName(array('id', 'point', 'total_marks', 'quizquestion_id', 'course_id')));
		$query->from($db->quoteName('#__splms_quizresults'));
		$query->where($db->quoteName('published')." = 1");
		$query->where($db->quoteName('user_id')." = ".$db->quote($user_id));
		$query->where($db->quoteName('quizquestion_id')." = ".$db->quote($quiz_id));
		$query->order($db->quoteName('point') . ' DESC');
		$query->setLimit(1);
		
		$db->setQuery($query);
		return $db->loadObject();
	}

	// Insert Quiz Result
	public function insertQuizResult($user_id, $quiz_id, $course_id, $total_marks, $point) {
		$date = Factory::getDate()->toSql();
		$user = Factory::getUser();
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		
		$created = $date;
		$created_by = $user->get('id');
			
		$modified = $date;
		$modified_by = $user->get('id');


		$columns = array('user_id', 'quizquestion_id', 'course_id', 'total_marks', 'point','created','created_by','modified','modified_by');
		$values = array($db->quote($user_id), $db->quote($quiz_id), $db->quote($course_id), $db->quote($total_marks), $db->quote($point), $db->quote($created),$db->quote($created_by),$db->quote($modified),$db->quote($modified_by));
		$query
		->insert($db->quoteName('#__splms_quizresults'))
		->columns($db->quoteName($columns))
		->values(implode(',', $values));
		$db->setQuery($query);
		$result = $db->execute();

		return $result;

	}


}
