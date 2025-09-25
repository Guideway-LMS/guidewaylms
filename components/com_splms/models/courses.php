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
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

class SplmsModelCourses extends ListModel {

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

		// Get Params
		$app 				= Factory::getApplication();
		$params   			= $app->getMenu()->getActive()->getParams(); // get the active item
		$input      		= Factory::getApplication()->input;
		$item_type 			= $params->get('item_type', '');
		$ordering 			= $params->get('ordering_type', 'ordering');
		$ordering_dir 		= $params->get('ordering', 'DESC');
		$category_id 		= $params->get('category_id', '');
		$subcategory_enable = $params->get('show_subcategory' , '');

		// get post params
		$terms    		= $input->get('terms', NULL, 'STRING');
		$categories   	= $input->get('category', NULL, 'RAW');
		$levels   		= $input->get('level', NULL, 'STRING');
		$course_type	= $input->get('course_type', NULL, 'STRING');

		// Create a new query object.
		$db		= $this->getDbo();
		$query	= $db->getQuery(true);

		// Select the required fields from the table.
		$query->select('a.*');
		$query->select($db->quoteName('b.title', 'category_name'));
		$query->from($db->quoteName('#__splms_courses', 'a'));
		$query->join('LEFT', $db->quoteName('#__splms_coursescategories', 'b') . ' ON (' . $db->quoteName('a.coursecategory_id') . ' = ' . $db->quoteName('b.id') . ')');
		
		// search
		if (ltrim($db->escape($terms))) {
			$search_terms	= array_map('trim', explode(',', $db->escape($terms)));
			$searchTermSql	= $db->quoteName('a.title') . " LIKE '%" . implode("%' OR " . $db->quoteName('a.title') . " LIKE '%", $search_terms) . "%'";
			$query->where( '(' . $searchTermSql . ')' );
		}
		
		// Filter category
		if (is_array($categories)) {
			array_push($categories, $category_id);
			$categories = ArrayHelper::toInteger($categories);
			$categories = implode(',', $categories);
		} 
		else
		{
			$categories = ($categories) ? htmlspecialchars($categories) : '';
		}
	
		if(empty($filtered))
		{
			if(!empty($category_id) && empty($categories))
			{
				$categories = $category_id;
			}

			if(!empty($item_type) && $item_type == 1 && empty($course_type))
			{
				$course_type = 'featured';
			}
		}

		if(!empty($categories)) {

			// get Sub-Category if option is enabled in settings
			if ($subcategory_enable) {
				$subcategoryids		=	self::getSubcategories($categories);
				array_push($subcategoryids, $categories);
				$coursecategory_id		= 	implode(',', $subcategoryids);
			}
			else {
				$coursecategory_id = $categories;
			}			
			
			$query->where('a.coursecategory_id IN (' . $db->escape($coursecategory_id) . ')');
		}

		// Filter Level
		if(!empty($levels)) {
			$levels		= array_map('trim', explode(',', $db->escape($levels)));
			$levelSql	= $db->quoteName('a.level') . " LIKE '%" . implode("%' OR " . $db->quoteName('a.level') . " LIKE '%", $levels) . "%'";
			$query->where( '(' . $levelSql . ')' );
		}

		// Filter by monetization type
		if( $course_type == 'featured' ) {
			$query->where($db->quoteName('a.featured_course') . ' = 1');
		} elseif( $course_type == 'free' ) {
			$query->where($db->quoteName('a.price') . ' = 0');
		} elseif( $course_type == 'paid' ) {
			$query->where($db->quoteName('a.price') . ' > 0');
		}

		// lessons count
		$query->select("CASE WHEN lessons.count IS NULL THEN 0 ELSE lessons.count END as lessonsCount")
			->join('LEFT', '( SELECT c.course_id, COUNT(c.id) AS count FROM #__splms_lessons c WHERE c.published = 1 GROUP BY c.course_id ) AS lessons ON a.id = lessons.course_id');

		// Filter by language
		$query->where('a.language in (' . $db->quote(Factory::getLanguage()->getTag()) . ',' . $db->quote('*') . ')');
		
		// Authorised
		$groups = implode(',', $user->getAuthorisedViewLevels());
		$query->where('a.access IN (' . $groups . ')');
		
		$query->where($db->quoteName('a.published')." = ".$db->quote('1'));
		$query->order($db->quoteName('a.' . $ordering) . ' ' . $ordering_dir);

		return $query;
	}

	public function getItems() {
		$items = parent::getItems();
		if(!empty($items) && count($items)) {
			foreach($items as &$item) {
				$item->link 	  = Route::_('index.php?option=com_splms&view=course&id=' . $item->id . ':' . $item->alias . SplmsHelper::getItemId('courses'));
				$item->thumbnail  = SplmsHelper::getThumbnail($item->image);
				$item->teachers   = $this->getCourseTeachers($item->id);
				$item->price	  = SplmsHelper::formatPrice($item->price);
				$item->sale_price = SplmsHelper::formatPrice($item->sale_price) ?: $item->sale_price;
			}
		}

		return $items;
	}

	// Get Couse Teachers by Course ID
	public static function getCourseTeachers($course) {
		// Load Lessons model
		//jimport('joomla.application.component.model');
		BaseDatabaseModel::addIncludePath(JPATH_SITE.'/components/com_splms/models');
		$lessons_model = BaseDatabaseModel::getInstance( 'Lessons', 'SplmsModel' );

		$lessons = $lessons_model->getLessons($course);
		$ids = array();
		foreach ($lessons as $lesson) {
			$ids[] = $lesson->teacher_id;
		}

		$ids = array_unique($ids);
		$ids = implode(',',$ids);

		if ($ids) {
			$db = Factory::getDbo();
			$query = $db->getQuery(true);

			$query->select($db->quoteName(array('id', 'title', 'alias', 'designation', 'description', 'image', 'experience', 'specialist_in', 'website', 'social_facebook', 'social_twitter', 'social_youtube', 'social_linkedin')));
			$query->from($db->quoteName('#__splms_teachers'));
			$query->where($db->quoteName('published')." = 1");
			$query->where($db->quoteName('id')." IN (".$ids . ")");
			$query->order('ordering DESC');

			$db->setQuery($query);

			$items = $db->loadObjectList();

			foreach ($items as &$item) {
				$item->url = Route::_('index.php?option=com_splms&view=teacher&id='.$item->id.':'.$item->alias . SplmsHelper::getItemid('teachers'));
			}
		} else {
			$items= array();
		}

		return $items;
	}

	// Get Course By Course id
	public static function getCourse($id) {
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select($db->quoteName(array('a.id', 'a.title', 'a.alias', 'a.image', 'a.course_time', 'a.price', 'a.sale_price')));
		$query->select($db->quoteName('b.title', 'category_name'));
		$query->from($db->quoteName('#__splms_courses', 'a'));
		$query->join('LEFT', $db->quoteName('#__splms_coursescategories', 'b') . ' ON (' . $db->quoteName('a.id') . ' = ' . $db->quoteName('b.id') . ')');

		$query->where($db->quoteName('a.published')." = 1");
		$query->where($db->quoteName('a.id')." = ".$db->quote($id));
		$query->order('a.ordering DESC');

		$db->setQuery($query);

		$result = $db->loadObject();

		if ($result) {
			$result->url = Route::_('index.php?option=com_splms&view=course&id='.$result->id.':'.$result->alias . SplmsHelper::getItemid('courses'));
		}
		
		return $result;
	}

	// Get Course By Course id
	public static function getAllCourses() {
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select($db->quoteName(array('id', 'title', 'alias', 'image', 'short_description')));
		$query->from($db->quoteName('#__splms_courses'));
		$query->where($db->quoteName('published')." = 1");
		$query->order('ordering DESC');
		$db->setQuery($query);
		$results = $db->loadObjectList();
		foreach ($results as &$result) {
			$result->url = Route::_('index.php?option=com_splms&view=course&id='.$result->id.':'.$result->alias . SplmsHelper::getItemid('courses'));
		}
		return $results;
	}

	// Check user already buy course
	public static function getIsbuycourse($user_id, $course_id) {
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select($db->quoteName(array('id')));
		$query->from($db->quoteName('#__splms_orders'));
		$query->where($db->quoteName('published')." = 1");
		$query->where($db->quoteName('course_id')." = ".$db->quote($course_id));
		$query->where($db->quoteName('order_user_id')." = ".$db->quote($user_id));
		$query->order('ordering DESC');
		$db->setQuery($query);
		$result = $db->loadObject();

		return $result;
	}

	// Get users puchased item
	public static function getPurchasedCourse($user_id) {
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select($db->quoteName(array('order_user_id', 'course_id', 'published')));
		$query->from($db->quoteName('#__splms_orders'));
		$query->where($db->quoteName('published') ." = 1");
		$query->where($db->quoteName('order_user_id')." = ".$db->quote($user_id));
		$query->order('ordering DESC');
		$db->setQuery($query);
		$results = $db->loadObjectList();

		foreach ($results as &$result) {
			$result->course_info = self::getCourse($result->course_id);

			$result->course_name = $result->course_info->title;
			$result->url  = $result->course_info->url;
		}

		return $results;
	}

	// Get Quizzes By Course id
	public static function getQuizzes($course_id) {
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select($db->quoteName(array('id', 'title', 'alias', 'quiz_type')));
		$query->from($db->quoteName('#__splms_quizquestions'));
		$query->where($db->quoteName('published') ." = 1");
		$query->where($db->quoteName('course_id')." = ".$db->quote($course_id));
		$query->order('ordering DESC');
		$db->setQuery($query);
		$items = $db->loadObjectList();

		foreach ($items as &$item) {
			$item->url = Route::_('index.php?option=com_splms&view=quizquestion&id='.$item->id.':'.$item->alias . SplmsHelper::getItemid('quizquestions'));
		}

		return $items;
	}

	//Get Related Artists
	public function getRelatedCourses($course_name, $course_id, $cat_id, $limit = 3) {
		$search = preg_replace('#\xE3\x80\x80#s', " ", htmlspecialchars(trim($course_name)));
		$search_array = explode(" ", $search);

		$str_tag_ids = implode(' OR ', array_map(function ($entry) {
			return "a.title LIKE '%" . $entry . "%'";
		}, $search_array));
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select( array('a.id', 'a.title', 'a.alias', 'a.level', 'a.course_sub_title', 'a.course_time', 'a.image', 'a.price', 'a.sale_price') );
		$query->select($db->quoteName('b.title', 'category_name'));
		$query->from($db->quoteName('#__splms_courses', 'a'));
		$query->join('LEFT', $db->quoteName('#__splms_coursescategories', 'b') . ' ON (' . $db->quoteName('a.coursecategory_id') . ' = ' . $db->quoteName('b.id') . ')');
		$query->where($db->quoteName('a.id')." != ".$db->quote($course_id));
		$query->where($db->quoteName('a.published')." = ".$db->quote('1'));

		if ($str_tag_ids) {
			$query->where( '(' . $str_tag_ids . ')' );
		}
		
		$query->setLimit($limit);
		$db->setQuery($query);
		$relatedCourses = $db->loadObjectList();

		foreach ($relatedCourses as &$relatedCourse) {
			$relatedCourse->thumbnail = SplmsHelper::getThumbnail($relatedCourse->image);
			$relatedCourse->url = Route::_('index.php?option=com_splms&view=course&id=' . $relatedCourse->id . ':' . $relatedCourse->alias . SplmsHelper::getItemId('courses'));
		}

		return $relatedCourses;
	}

	// get ratings by item id
	public function getEnrolled($course) {
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('COUNT(*)');
	    $query->from($db->quoteName('#__splms_orders', 'a'));
	    $query->where($db->quoteName('a.course_id') . ' = ' . $db->quote($course));
	    $query->where($db->quoteName('a.published')." = ".$db->quote('1'));
	    $db->setQuery($query);
		
		return $db->loadResult();
	}

	// get user's review by item id
	public function getMyReview($item_id) {
		$user = Factory::getUser();
		if($user->id) {
			$db = Factory::getDbo();
			$query = $db->getQuery(true);
			$query->select( array('a.*', 'b.email', 'b.name') );
	    	$query->from($db->quoteName('#__splms_reviews', 'a'));
	    	$query->join('LEFT', $db->quoteName('#__users', 'b') . ' ON (' . $db->quoteName('a.created_by') . ' = ' . $db->quoteName('b.id') . ')');
	    	$query->where($db->quoteName('a.course_id') . ' = ' . $db->quote($item_id));
		    $query->where($db->quoteName('a.created_by') . ' = ' . $db->quote($user->id));
		    $query->where($db->quoteName('a.published')." = ".$db->quote('1'));
		    $db->setQuery($query);
		    $review = $db->loadObject();
		    if($review) {
		    	$review->gravatar = md5($review->email);
	    		$review->created_date = SplmsHelper::timeago($review->created);
		    	return $review;
			}
		    return false;
		}

	    return false;
	}

	// get reviews by movie id
	public function getReviews($item_id) {
		$params = JComponentHelper::getParams('com_splms');
		$input 	= Factory::getApplication()->input;
		$start 	= $input->post->get('start', 0, 'INT');
		$limit 	= $params->get('review_limit', 12);
		$db 	= Factory::getDbo();
		$query 	= $db->getQuery(true);
		$query->select( array('a.*', 'b.email', 'b.name') );
	    $query->from($db->quoteName('#__splms_reviews', 'a'));
	    $query->join('INNER', $db->quoteName('#__users', 'b') . ' ON (' . $db->quoteName('a.created_by') . ' = ' . $db->quoteName('b.id') . ')');
	    $query->where($db->quoteName('a.course_id') . ' = ' . $db->quote($item_id));
	    $query->where($db->quoteName('a.published')." = ".$db->quote('1'));
	    $query->order($db->quoteName('a.created') . ' DESC');
	    $query->setLimit($limit, $start);
	    $db->setQuery($query);
		$reviews = $db->loadObjectList();

		return $reviews;
	}

	// get ratings by item id
	public function getRatings($item_id) {
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select( array('COUNT(a.rating) AS count', 'SUM(a.rating) AS total') );
	    $query->from($db->quoteName('#__splms_reviews', 'a'));
	    $query->where($db->quoteName('a.course_id') . ' = ' . $db->quote($item_id));
	    $query->where($db->quoteName('a.published')." = ".$db->quote('1'));
	    $db->setQuery($query);
		
		return $db->loadObject();
	}

	// Get total reviews by ote, id
	public function getTotalReviews($item_id) {
		$input = Factory::getApplication()->input;
		$start 	= $input->post->get('start', 0, 'INT');
		$limit 	= 1;
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select( array('COUNT(a.id)') );
	    $query->from($db->quoteName('#__splms_reviews', 'a'));
	    $query->join('INNER', $db->quoteName('#__users', 'b') . ' ON (' . $db->quoteName('a.created_by') . ' = ' . $db->quoteName('b.id') . ')');
	    $query->where($db->quoteName('a.course_id') . ' = ' . $db->quote($item_id));
	    $query->where($db->quoteName('a.published')." = ".$db->quote('1'));
	    $db->setQuery($query);
		
		return $db->loadResult();
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

	/**
	 * Get sub category list from parent ids
	 *
	 * @param  array|int $parent_ids
	 * @return mixed
	 * 
	 * @since 4.0.5
	 */
	public function getSubcategories($parent_ids)
	{
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('id');
		$query->from($db->quoteName('#__splms_coursescategories'));
		$query->where($db->quoteName('published') . " = 1");
		$query->where('parent_id IN (' . $db->escape($parent_ids) . ')');
		$query->order('ordering DESC');
		$db->setQuery($query);

		$rows = $db->loadColumn();

		return $rows;
	}

	/**
	 * Get a single column value from #__splms_coursescategories' table
	 *
	 * @param string $column 	Column name
	 * @param int 	 $id 		ID of a specific row
	 * @return void
	 */
	public function getCourseCategoryInfo($column, $id)
	{
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select($column);
		$query->from($db->quoteName('#__splms_coursescategories'));
		$query->where($db->quoteName('id') . " = " . $db->quote($id));
		$db->setQuery($query);

		$result = $db->loadColumn();
		return $result;
	}

}
