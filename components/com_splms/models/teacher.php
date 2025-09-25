<?php
/**
* @package com_splms
* @author JoomShaper http://www.joomshaper.com
* @copyright Copyright (c) 2010 - 2024 JoomShaper
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\ItemModel;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

class SplmsModelTeacher extends ItemModel {

	protected $_context = 'com_splms.teacher';

	protected function populateState() {
		$app = Factory::getApplication('site');
		$itemId = $app->input->getInt('id');
		$this->setState('teacher.id', $itemId);
		$this->setState('filter.language', Multilanguage::isEnabled());
	}

	public function getItem( $itemId = null ) {
		$user = Factory::getUser();

		$itemId = (!empty($itemId))? $itemId : (int)$this->getState('teacher.id');

		if ( $this->_item == null ) {
			$this->_item = array();
		}

		if (!isset($this->_item[$itemId])) {
			try {
				$db = $this->getDbo();
				$query = $db->getQuery(true);
				$query->select('a.*');
				$query->from('#__splms_teachers as a');
				$query->where('a.id = ' . (int) $itemId);

				// Courses
				$query->select("CASE WHEN courses.count IS NULL THEN 0 ELSE courses.count END as courseCount")
					->join('LEFT', '( SELECT b.teacher_id, COUNT(b.course_id) AS count FROM #__splms_lessons b WHERE b.published = 1 GROUP BY b.teacher_id ) AS courses ON a.id = courses.teacher_id');

				// Lessons
				$query->select("CASE WHEN lessons.count IS NULL THEN 0 ELSE lessons.count END as lessonsCount")
					->join('LEFT', '( SELECT c.teacher_id, COUNT(c.id) AS count FROM #__splms_lessons c WHERE c.published = 1 GROUP BY c.teacher_id ) AS lessons ON a.id = lessons.teacher_id');

				// Followers
				$query->select("CASE WHEN followers.count IS NULL THEN 0 ELSE followers.count END as followersCount")
					->join('LEFT', '( SELECT d.teacher, COUNT(d.id) AS count FROM #__splms_followers d WHERE d.status = 1 GROUP BY d.teacher ) AS followers ON a.id = followers.teacher');
				
				// Filter by published state.
				$query->where('a.published = 1');

				if ($this->getState('filter.language')) {
					$query->where('a.language in (' . $db->quote(Factory::getLanguage()->getTag()) . ',' . $db->quote('*') . ')');
				}

				//Authorised
				$groups = implode(',', $user->getAuthorisedViewLevels());
				$query->where('a.access IN (' . $groups . ')');

				$db->setQuery($query);
				$data = $db->loadObject();

				if (empty($data)) {
					throw new \Exception(Text::_('COM_SPLMS_ERROR_ITEM_NOT_FOUND'), 404);
				}

				$user = Factory::getUser();
				$groups = $user->getAuthorisedViewLevels();
				if(!in_array($data->access, $groups)) {
					throw new \Exception(Text::_('COM_SPLMS_ERROR_NOT_AUTHORISED'), 404);
				}

				// Teacher Model
				$teacherModel = BaseDatabaseModel::getInstance('Teachers', 'SplmsModel');
				$data->isFollowing = $teacherModel->isFollowing($data->id);
				$data->ratings = $teacherModel->getRatings($data->id);
				$data->featuredCourse = $this->getCourses($data->id, true);
				$data->courses = $this->getCourses($data->id);
				$data->followers = $this->getFollowers($data->id);

				// skills
				$skills = $data->specialist_in;
				if(SplmsHelper::isJson($skills)) {
					$skills = json_decode($skills, true);
				} elseif (!empty($skills)) {
					$skills = explode(',', $skills);
				}

				$data->skills = $skills;

				$this->_item[$itemId] = $data;
			}
			catch (Exception $e) {
				if ($e->getCode() == 404 ) {
					throw new \Exception($e->getMessage(), 404);
				} else {
					Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');
					$this->_item[$itemId] = false;
				}
			}
		}

		return $this->_item[$itemId];
	}

	// Is following
	public function isFollowing($teacher) {
		$user = Factory::getUser();
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select(array($db->quoteName('a.status')));
		$query->from($db->quoteName('#__splms_followers', 'a'));
		$query->where($db->quoteName('a.teacher') . ' = ' . $db->quote((int) $teacher));
		$query->where($db->quoteName('a.student') . ' = ' . $db->quote((int) $user->id));
		$db->setQuery($query);
		$result = $db->loadResult();
		return $result;
	}

	// Toggle follow
	public function toggleFollow($teacher) {
		$follower = $this->isFollower($teacher);
		if(!empty($follower)) {
			$this->toggleFollowerStatus($follower);
		} else {
			$this->addFollower($teacher);
		}

		return $this->isFollower($teacher);
	}

	public function isFollower($teacher) {
		$user = Factory::getUser();
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select(array($db->quoteName('a.id'),$db->quoteName('a.teacher'),$db->quoteName('a.student'),$db->quoteName('a.status')));
		$query->from($db->quoteName('#__splms_followers', 'a'));
		$query->where($db->quoteName('a.teacher') . ' = ' . $db->quote((int) $teacher));
		$query->where($db->quoteName('a.student') . ' = ' . $db->quote((int) $user->id));
		$db->setQuery($query);
		$item = $db->loadObject();
		return $item;
	}

	public function addFollower($teacher) {
		$db = Factory::getDbo();
		$object = new stdClass();
		$object->teacher = (int) $teacher;
		$object->student = (int) Factory::getUser()->id;
		$object->status = 1;
		$result = $db->insertObject('#__splms_followers', $object);

		return $result;
	}

	public function toggleFollowerStatus($follower) {
		$status = ($follower->status == 0) ? 1 : 0;
		$db = Factory::getDbo();
		$object = new stdClass();
		$object->id = (int) $follower->id;
		$object->status = $status;
		$result = $db->updateObject('#__splms_followers', $object, 'id');

		return $result;
	}

	public function getCourses($teacher, $featured = false) {
		$courseIds = array();
		$lessons = $this->getLessons($teacher);
		if(!empty($lessons)) {
			foreach($lessons as $lesson) {
				$courseIds[] = $lesson->course_id;
			}
		}

		if(count($courseIds)) {
			$courseIds = implode(',', $courseIds);

			$db = $this->getDbo();
			$query = $db->getQuery(true);
			$query->select(array('a.*', 'b.title AS category_name', 'b.id AS catid', 'b.alias AS catalias'));
			$query->from($db->quoteName('#__splms_courses', 'a'));

			// join over course category
			$query->join('LEFT', $db->quoteName('#__splms_coursescategories', 'b') . ' ON (' . $db->quoteName('a.coursecategory_id') . ' = ' . $db->quoteName('b.id') . ')');

			// Duration
			$query->select("CASE WHEN lessonsDuration.timeSum IS NULL THEN '00:00:00' ELSE lessonsDuration.timeSum END as duration")
				->join('LEFT', '( SELECT b.course_id, SEC_TO_TIME( ROUND( SUM( TIME_TO_SEC(b.video_duration) ) ) ) AS timeSum FROM #__splms_lessons b WHERE b.published = 1 GROUP By b.course_id) AS lessonsDuration ON a.id = lessonsDuration.course_id');

			// lessons
			$query->select("CASE WHEN lessons.count IS NULL THEN 0 ELSE lessons.count END as lessonsCount")
				->join('LEFT', '( SELECT c.course_id, COUNT(c.id) AS count FROM #__splms_lessons c WHERE c.published = 1 GROUP BY c.course_id ) AS lessons ON a.id = lessons.course_id');

			$query->where('a.id IN (' . $courseIds . ')');

			if($featured) {
				$query->where('a.featured_course = 1');
			}

			$query->where('a.published = 1');
			$query->order('a.created DESC');
			if($featured) {
				$query->setLimit(1);
			}
			$db->setQuery($query);
			$courses = $db->loadObjectList();

			if(!empty($courses)) {
				foreach($courses as &$course) {
					$course->url = $course->link = Route::_('index.php?option=com_splms&view=course&id=' . $course->id . ':' . $course->alias . SplmsHelper::getItemId('courses'));
					$course->thumbnail = SplmsHelper::getThumbnail($course->image);
				}
				return $featured ? $courses[0] : $courses;
			}
		}

		return;
	}

	public function getLessons($teacher) {
		$db = $this->getDbo();
		$query = $db->getQuery(true);
		$query->select('a.*');
		$query->from($db->quoteName('#__splms_lessons', 'a'));
		$query->where('a.teacher_id = ' . (int) $teacher);
		$query->where('a.published = 1');
		$db->setQuery($query);
		$lessons = $db->loadObjectList();

		return $lessons;
	}

	public function getFollowers($teacher, $count = 18, $start = 0) {
		$user = Factory::getUser();
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select(array('a.*', 'b.name'));
		$query->from($db->quoteName('#__splms_followers', 'a'));
		$query->join('LEFT', $db->quoteName('#__users', 'b') . ' ON (' . $db->quoteName('a.student') . ' = ' . $db->quoteName('b.id') . ')');
		$query->where($db->quoteName('a.teacher') . ' = ' . $db->quote((int) $teacher));
		$query->where($db->quoteName('a.status') . ' = 1');
		$query->setLimit($count, $start);
		$db->setQuery($query);
		$followers = $db->loadObjectList();

		if(!empty($followers)) {
			foreach($followers as &$follower) {
				$follower->avatar = SplmsHelper::getAvatar($follower->student);
			}
		}

		return $followers;
	}

}
