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
use Joomla\CMS\MVC\Model\ListModel;

class SplmsModelFollowings extends ListModel {

	protected function populateState($ordering = null, $direction = null) {
		$app = Factory::getApplication('site');
		$params = $app->getParams();
		$this->setState('list.start', $app->input->get('limitstart', 0, 'uint'));
		$limit = $params->get('limit');
		$this->setState('list.limit', $limit);
	}

	protected function getListQuery() {
		$app = Factory::getApplication();
		$user = Factory::getUser();

		// Create a new query object.
		$db = $this->getDbo();
		$query = $db->getQuery(true);
		$query->select('b.*');
		$query->from($db->quoteName('#__splms_followers', 'a'));

		// join over teachers table
		$query->join('LEFT', $db->quoteName('#__splms_teachers', 'b') . ' ON (' . $db->quoteName('a.teacher') . ' = ' . $db->quoteName('b.id') . ')');

		// Courses
		$query->select("CASE WHEN courses.count IS NULL THEN 0 ELSE courses.count END as courses")
			->join('LEFT', '( SELECT b.teacher_id, COUNT(b.course_id) AS count FROM #__splms_lessons b WHERE b.published = 1 GROUP BY b.teacher_id ) AS courses ON b.id = courses.teacher_id');

		// Lessons
		$query->select("CASE WHEN lessons.count IS NULL THEN 0 ELSE lessons.count END as lessons")
			->join('LEFT', '( SELECT c.teacher_id, COUNT(c.id) AS count FROM #__splms_lessons c WHERE c.published = 1 GROUP BY c.teacher_id ) AS lessons ON b.id = lessons.teacher_id');

		// Followers
		$query->select("CASE WHEN followers.count IS NULL THEN 0 ELSE followers.count END as followers")
			->join('LEFT', '( SELECT d.teacher, COUNT(d.id) AS count FROM #__splms_followers d WHERE d.status = 1 GROUP BY d.teacher ) AS followers ON b.id = followers.teacher');

		$groups = implode(',', $user->getAuthorisedViewLevels());
		$query->where('b.access IN (' . $groups . ')');
		$query->where('b.language in (' . $db->quote(Factory::getLanguage()->getTag()) . ',' . $db->quote('*') . ')');

		$query->where($db->quoteName('a.student')." = " . $db->quote( (int) $user->id ) );
		$query->where($db->quoteName('a.status')." = 1");

		$query->where($db->quoteName('b.published')." = ".$db->quote('1'));
		$query->order('b.ordering DESC');

		return $query;
	}

	public function getItems() {
		$items = parent::getItems();

		if(!empty($items)) {
			foreach($items as &$item) {
				$item->isFollowing = $this->isFollowing($item->id);
				$item->ratings = $this->getRatings($item->id);
				$item->url = Route::_('index.php?option=com_splms&view=teacher&id='.$item->id.':'.$item->alias . SplmsHelper::getItemid('teachers'));
			
				// Skills
				$skills = json_decode($item->specialist_in);
				$item->skills = '';
				if (isset($skills) && is_object($skills)) {
					$newSkills = array();
					foreach ($skills as $key => $skill) {
						$newSkills[] = $skill->specialist_text;
					}
					$item->skills = implode(', ', $newSkills);
				}
			}
		}

		return $items;
	}

	// Get teachers rating
	public function getRatings($teacher) {
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->from($db->quoteName('#__splms_lessons', 'a'));
		
		// Ratings
		$query->select('CASE WHEN reviews.ratings IS NULL THEN 0 ELSE reviews.ratings END as ratings, CASE WHEN reviews.count IS NULL THEN 0 ELSE reviews.count END as count')
			->join('LEFT', '( SELECT b.course_id, SUM(b.rating) AS ratings, COUNT(b.id) AS count FROM #__splms_reviews b WHERE b.published = 1 ) AS reviews ON a.course_id = reviews.course_id');

		$query->where($db->quoteName('published')." = 1");
		$query->where($db->quoteName('a.teacher_id')." = ".$db->quote((int) $teacher));
		$query->group('a.course_id');
		$db->setQuery($query);
		$reviews = $db->loadObjectList();

		$count = 0;
		$ratings = 0;

		if(!empty($reviews) && count($reviews)) {
			foreach($reviews as $review) {
				$ratings += $review->ratings;
				$count += $review->count;
			}
		}

		$output = new stdClass();
		$output->ratings = $ratings;
		$output->count = $count;

		return $output;
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
}
