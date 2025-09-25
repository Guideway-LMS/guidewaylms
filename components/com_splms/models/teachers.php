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
use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Router\Route;
use Joomla\CMS\MVC\Model\ListModel;

class SplmsModelTeachers extends ListModel {

	protected function populateState($ordering = null, $direction = null) {
		$app = Factory::getApplication('site');
		$params = $app->getParams();
		$this->setState('list.start', $app->input->get('limitstart', 0, 'uint'));
		$limit = $params->get('limit');
		$this->setState('list.limit', $limit);
		$search = $app->input->get('term', '', 'STRING');
		$this->setState('filter.search', $search);
		$skill = $app->input->get('skill', '', 'STRING');
		$this->setState('filter.skill', $skill);
	}

	protected function getListQuery() {
		$app = Factory::getApplication();
		$user = Factory::getUser();
		// Get Params
		$params   = $app->getMenu()->getActive()->getParams();
		$ordering = $params->get('ordering', ' DESC');

		// Create a new query object.
		$db = $this->getDbo();
		$query = $db->getQuery(true);
		$query->select('a.*');
		$query->from($db->quoteName('#__splms_teachers', 'a'));

		// Search
		if ($search = $this->getState('filter.search'))
		{
			$search = preg_replace('#\xE3\x80\x80#s', " ", trim($db->escape($search)));
			$terms = explode(" ", $search);
			$query->where($db->quoteName('a.title') . " LIKE '%" . implode("%' OR " . $db->quoteName('a.title') . " LIKE '%", $terms) . "%'");
		}

		// Sort by skills
		if ($skill = $this->getState('filter.skill'))
		{
			$skill = preg_replace('#\xE3\x80\x80#s', " ", trim($db->escape($skill)));
			$terms = explode('-', $skill);
			$query->where($db->quoteName('a.specialist_in') . " LIKE '%" . implode("%' OR " . $db->quoteName('a.specialist_in') . " LIKE '%", $terms) . "%'");
		}

		// Courses
		$query->select("CASE WHEN courses.count IS NULL THEN 0 ELSE courses.count END as courses")
			->join('LEFT', '( SELECT b.teacher_id, COUNT(b.course_id) AS count FROM #__splms_lessons b WHERE b.published = 1 GROUP BY b.teacher_id ) AS courses ON a.id = courses.teacher_id');

		// Lessons
		$query->select("CASE WHEN lessons.count IS NULL THEN 0 ELSE lessons.count END as lessons")
			->join('LEFT', '( SELECT c.teacher_id, COUNT(c.id) AS count FROM #__splms_lessons c WHERE c.published = 1 GROUP BY c.teacher_id ) AS lessons ON a.id = lessons.teacher_id');

		// Followers
		$query->select("CASE WHEN followers.count IS NULL THEN 0 ELSE followers.count END as followers")
			->join('LEFT', '( SELECT d.teacher, COUNT(d.id) AS count FROM #__splms_followers d WHERE d.status = 1 GROUP BY d.teacher ) AS followers ON a.id = followers.teacher');
		
		$groups = implode(',', $user->getAuthorisedViewLevels());
		$query->where('a.access IN (' . $groups . ')');
		$query->where('a.language in (' . $db->quote(Factory::getLanguage()->getTag()) . ',' . $db->quote('*') . ')');
		$query->where($db->quoteName('a.published')." = ".$db->quote('1'));
		$query->order($db->quoteName('a.ordering') . $ordering);

		return $query;
	}

	public function getItems() {
		$items = parent::getItems();

		if(!empty($items)) {
			foreach($items as &$item) {
				$item->isFollowing = $this->isFollowing($item->id);
				$item->ratings = $this->getRatings($item->id);
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

	// Get teachers
	public static function getTeacher($id) {
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		// Order it by the ordering field.
		$query->select($db->quoteName(array('id', 'title', 'alias', 'image', 'description' , 'experience', 'specialist_in', 'website', 'social_facebook', 'social_twitter', 'social_youtube', 'social_linkedin')));
		$query->from($db->quoteName('#__splms_teachers'));
		$query->where($db->quoteName('published')." = 1");
		$query->where($db->quoteName('id')." = ".$db->quote($id));
		$query->order('ordering DESC');
		$db->setQuery($query);
		$result = $db->loadObject();

		if ($result != '') {
			$result->url = Route::_('index.php?option=com_splms&view=teacher&id='.$result->id.':'.$result->alias . SplmsHelper::getItemid('teachers'));
		}
		
		return $result;
	}

	// Get teacher ratings
	public static function getTeacherRatings( $teacher_id = 0 ){
		// Load courses & lesson Model
		$courses_model = BaseDatabaseModel::getInstance( 'Courses', 'SplmsModel' );
		$lessons_model = BaseDatabaseModel::getInstance( 'Lessons', 'SplmsModel' );

		$lessons = $lessons_model->getTeacherLessons($teacher_id);
		
		$data = new stdClass();
		$data->count_ratings = 0;
		$data->total_ratings = 0;

		$courseid_loaded = array();
		foreach ($lessons as $lesson) {
			// if not loaded already
			if(!in_array($lesson->get_course_info->id, $courseid_loaded)) {
				$course_ratings = $courses_model->getRatings($lesson->get_course_info->id);
				//Ratings generate
				$data->count_ratings += $course_ratings->count;
				$data->total_ratings += $course_ratings->total;
				array_push($courseid_loaded, $lesson->get_course_info->id);
			}
		}

		return $data;
	}

	// Get unique skills
	public function getSkills() {
		$db = $this->getDbo();
		$query = $db->getQuery(true);
		$query->select('DISTINCT(a.specialist_in) AS skills');
		$query->from($db->quoteName('#__splms_teachers', 'a'));
		$db->setQuery($query);
		$skillSets = $db->loadObjectList();

		$skills = array();
		if(!empty($skillSets)) {
			foreach($skillSets as $skillSet) {
				if($this->isJson($skillSet->skills)) {
					$skillsArray = json_decode($skillSet->skills, true);
					if(count($skillsArray)) {
						foreach($skillsArray as $arrSkill) {
							if(isset($arrSkill['specialist_text']) && !empty($arrSkill['specialist_text'])) {
								$skills[] = trim($arrSkill['specialist_text']);
							}
						}
					}
				}
			}
		}

		$skills = array_unique($skills);
		asort($skills);

		// rearrange by key
		$newSkills = array();
		foreach($skills as $skill) {
			if (Factory::getConfig()->get('unicodeslugs') == 1) {
				$key = OutputFilter::stringURLUnicodeSlug($skill);
			} else {
				$key = OutputFilter::stringURLSafe($skill);
			}

			$newSkills[$key] = $skill;
		}

		return $newSkills;
	}

	private function isJson($string) {
		json_decode($string);
		return (json_last_error() == JSON_ERROR_NONE);
	}
}
