<?php
/**
* @package com_splms
* @author JoomShaper http://www.joomshaper.com
* @copyright Copyright (c) 2010 - 2024 JoomShaper
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/
// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Component\Router\RouterView;
use Joomla\CMS\Component\Router\Rules\MenuRules;
use Joomla\CMS\Component\Router\Rules\NomenuRules;
use Joomla\CMS\Component\Router\Rules\StandardRules;
use Joomla\CMS\Component\Router\RouterViewConfiguration;



/**
 * Router class for com_splms
 *
 * @since	1.0.0
 */
class SplmsRouter extends RouterView
{
	protected $noIDs = false;

	/**
	 * The DB Object
	 *
	 * @var		DatabaseDriver
	 * @sine	4.0.0
	 */
	private $db = null;

	/**
	 * The query string generator.
	 *
	 * @var		object
	 * @since	4.0.0
	 */
	private $queryBuilder = null;

	/**
	 * LMS Component router constructor
	 *
	 * @param   JApplicationCms  $app   The application object
	 * @param   JMenu            $menu  The menu object to work with
	 */
	public function __construct($app = null, $menu = null)
	{
		$params = ComponentHelper::getParams('com_splms', true);
		$this->noIDs = (bool) $params->get('sef_ids');

		$this->db = Factory::getDbo();
		$this->queryBuilder = $this->db->getQuery(true);

		/**
		 * Registering the single views.
		 * We need not to write any get segment/id methods for these views.
		 */
		$this->registerView(new RouterViewConfiguration('purchases'));
		$this->registerView(new RouterViewConfiguration('cart'));
		$this->registerView(new RouterViewConfiguration('certificate'));
		$this->registerView(new RouterViewConfiguration('followings'));

		/**
		 * Registering the course categories (single, and list)
		 */
		$courseCategories = new RouterViewConfiguration('coursescategories');
		$this->registerView($courseCategories);
		$courseCategory = new RouterViewConfiguration('coursescategory');
		$courseCategory->setKey('id')->setParent($courseCategories);
		$this->registerView($courseCategory);

		/**
		 * Registering the course(s) views (single, and list)
		 */
		$courses = new RouterViewConfiguration('courses');
		$this->registerView($courses);
		$course = new RouterViewConfiguration('course');
		$course->setKey('id')->setParent($courses);
		$this->registerView($course);

		/**
		 * Registering the teachers views.
		 * Set the id as the teacher view's key and teachers view as parent.
		 */
		$teachers = new RouterViewConfiguration('teachers');
		$this->registerView($teachers);
		$teacher = new RouterViewConfiguration('teacher');
		$teacher->setKey('id')->setParent($teachers);
		$this->registerView($teacher);

		/**
		 * Registering the event categories views (single, and list).
		 */
		$eventCategories = new RouterViewConfiguration('eventcategories');
		$this->registerView($eventCategories);
		$eventCategory = new RouterViewConfiguration('eventcategory');
		$eventCategory->setKey('id')->setParent($eventCategories);
		$this->registerView($eventCategory);

		/**
		 * Registering the events views (single, and list).
		 */
		$events = new RouterViewConfiguration('events');
		$this->registerView($events);
		$event = new RouterViewConfiguration('event');
		$event->setKey('id')->setParent($events);
		$this->registerView($event);

		/**
		 * Registering the speakers views (single, and list).
		 */
		$speakers = new RouterViewConfiguration('speakers');
		$this->registerView($speakers);
		$speaker = new RouterViewConfiguration('speaker');
		$speaker->setKey('id')->setParent($speakers);
		$this->registerView($speaker);

		/**
		 * Registering the speakers views (single, and list).
		 */
		$speakers = new RouterViewConfiguration('speakers');
		$this->registerView($speakers);
		$speaker = new RouterViewConfiguration('speaker');
		$speaker->setKey('id')->setParent($speakers);
		$this->registerView($speaker);

		/**
		 * Registering the quizQuestions views (single, and list).
		 */
		$quizQuestions = new RouterViewConfiguration('quizquestions');
		$this->registerView($quizQuestions);
		$quizQuestion = new RouterViewConfiguration('quizquestion');
		$quizQuestion->setKey('id')->setParent($quizQuestions);
		$this->registerView($quizQuestion);

		/**
		 * Registering the certificates views (single, and list).
		 */
		$certificates = new RouterViewConfiguration('certificates');
		$this->registerView($certificates);
		$certificate = new RouterViewConfiguration('certificate');
		$certificate->setKey('id')->setParent($certificates);
		$this->registerView($certificate);


		parent::__construct($app, $menu);

		$this->attachRule(new MenuRules($this));

		if ($params->get('sef_advanced', 1))
		{
			$this->attachRule(new StandardRules($this));
			$this->attachRule(new NomenuRules($this));
		}
	}

	/**
	 * Get missing alias from the provided ID.
	 *
	 * @param	string		$id		The ID with or without the alias.
	 * @param	string		$table	The table name.
	 *
	 * @return	string		The alias string.
	 * @since	4.0.0
	 */
	private function getAlias(string $id, string $table) : string
	{
		try
		{
			$this->queryBuilder->clear();
			$this->queryBuilder->select('alias')
				->from($this->db->quoteName($table))
				->where($this->db->quoteName('id') . ' = ' . (int) $id);
			$this->db->setQuery($this->queryBuilder);

			return (string) $this->db->loadResult();
		}
		catch (Exception $e)
		{
			echo $e->getMessage();

			return '';
		}
	}

	/**
	 * Get id from the alias.
	 *
	 * @param	string		$alias		The alias string.
	 * @param	string		$table		The table name.
	 *
	 * @return	int			The id.
	 * @since	4.0.0
	 */
	private function getId(string $alias, string $table) : int
	{
		try
		{
			$this->queryBuilder->clear();
			$this->queryBuilder->select('id')
				->from($this->db->quoteName($table))
				->where($this->db->quoteName('alias') . ' = ' . $this->db->quote($alias));
			$this->db->setQuery($this->queryBuilder);

			return (int) $this->db->loadResult();
		}
		catch (Exception $e)
		{
			echo $e->getMessage();

			return 0;
		}
	}

	/**
	 * Get the view segment for the common views.
	 *
	 * @param	string	$id		The ID with or without alias.
	 * @param	string	$table	The table name.
	 *
	 * @return	array	The segment array.
	 * @since	4.0.0
	 */
	private function getViewSegment(string $id, string $table) : array
	{
        // Certificates table does NOT have an "alias" column.
        // So we must not try to append / resolve alias for SEF segments.
        if ($table === '#__splms_certificates')
        {
                // If it comes as "123:anything", keep only the numeric id
                $numericId = (int) explode(':', $id, 2)[0];

                if ($this->noIDs)
                {
                        // When noIDs is enabled, router expects [id => segment]
                        // Use the numeric id as both key and value (safe fallback)
                        return [(string) $numericId => (string) $numericId];
                }

                return [$numericId => (string) $numericId];
        }

        // Default behavior for tables that support alias
        if (strpos($id, ':') === false)
        {
                $id .= ':' . $this->getAlias($id, $table);
        }

        if ($this->noIDs)
        {
                list ($key, $alias) = explode(':', $id, 2);

                return [$key => $alias];
        }

        return [(int) $id => $id];
	}

	/**
	 * get the view ID for the common pattern view.
	 *
	 * @param	string	$segment	The segment string.
	 * @param	string	$table		The table name.
	 *
	 * @return	int		The id.
	 * @since	4.0.0
	 */
	private function getViewId(string $segment, string $table) : int
	{
        // Certificates table does NOT have an "alias" column.
        // Accept both "123" and "123:anything" and return the numeric id.
        if ($table === '#__splms_certificates')
        {
                return (int) explode(':', $segment, 2)[0];
        }

        return $this->noIDs
                ? $this->getId($segment, $table)
                : (int) $segment;
	}

	/**
	 * Method to get the segment(s) for a course
	 *
	 * @param   string  $id     ID of the article to retrieve the segments for
	 * @param   array   $query  The request that is built right now
	 *
	 * @return  array|string  The segments of this item
	 */
	public function getCoursescategorySegment($id, $query)
	{
		return $this->getViewSegment($id, '#__splms_coursescategories');
	}

	/**
	 * Method to get the segment(s) for a teacher
	 *
	 * @param   string  $segment  Segment of the article to retrieve the ID for
	 * @param   array   $query    The request that is parsed right now
	 *
	 * @return  mixed   The id of this item or false
	 */
	public function getCoursescategoryId($segment, $query)
	{
		return $this->getViewId($segment, '#__splms_coursescategories');
	}

	/**
	 * Method to get the segment(s) for a course
	 *
	 * @param   string  $id     ID of the article to retrieve the segments for
	 * @param   array   $query  The request that is built right now
	 *
	 * @return  array|string  The segments of this item
	 */
	public function getCourseSegment($id, $query)
	{
		return $this->getViewSegment($id, '#__splms_courses');
	}

	/**
	 * Method to get the segment(s) for a teacher
	 *
	 * @param   string  $segment  Segment of the article to retrieve the ID for
	 * @param   array   $query    The request that is parsed right now
	 *
	 * @return  mixed   The id of this item or false
	 */
	public function getCourseId($segment, $query)
	{
		return $this->getViewId($segment, '#__splms_courses');
	}

	/**
	 * Method to get the segment(s) for a teacher
	 *
	 * @param   string  $id     ID of the article to retrieve the segments for
	 * @param   array   $query  The request that is built right now
	 *
	 * @return  array|string  The segments of this item
	 */
	public function getTeacherSegment($id, $query)
	{
		return $this->getViewSegment($id, '#__splms_teachers');
	}

	/**
	 * Method to get the segment(s) for a teacher
	 *
	 * @param   string  $segment  Segment of the article to retrieve the ID for
	 * @param   array   $query    The request that is parsed right now
	 *
	 * @return  mixed   The id of this item or false
	 */
	public function getTeacherId($segment, $query)
	{
		return $this->getViewId($segment, '#__splms_teachers');
	}

	/**
	 * Method to get the segment(s) for a event categories
	 *
	 * @param   string  $id     ID of the article to retrieve the segments for
	 * @param   array   $query  The request that is built right now
	 *
	 * @return  array|string  The segments of this item
	 */
	public function getEventcategorySegment($id, $query)
	{
		return $this->getViewSegment($id, '#__splms_eventcategories');
	}

	/**
	 * Method to get the id for a event categories
	 *
	 * @param   string  $segment  Segment of the article to retrieve the ID for
	 * @param   array   $query    The request that is parsed right now
	 *
	 * @return  mixed   The id of this item or false
	 */
	public function getEventcategoryId($segment, $query)
	{
		return $this->getViewId($segment, '#__splms_eventcategories');
	}

	/**
	 * Method to get the segment(s) for a events
	 *
	 * @param   string  $id     ID of the article to retrieve the segments for
	 * @param   array   $query  The request that is built right now
	 *
	 * @return  array|string  The segments of this item
	 */
	public function getEventSegment($id, $query)
	{
		return $this->getViewSegment($id, '#__splms_events');
	}

	/**
	 * Method to get the id for a events
	 *
	 * @param   string  $segment  Segment of the article to retrieve the ID for
	 * @param   array   $query    The request that is parsed right now
	 *
	 * @return  mixed   The id of this item or false
	 */
	public function getEventId($segment, $query)
	{
		return $this->getViewId($segment, '#__splms_events');
	}

	/**
	 * Method to get the segment(s) for a quiz question
	 *
	 * @param   string  $id     ID of the article to retrieve the segments for
	 * @param   array   $query  The request that is built right now
	 *
	 * @return  array|string  The segments of this item
	 */
	public function getQuizquestionSegment($id, $query)
	{
		return $this->getViewSegment($id, '#__splms_quizquestions');
	}

	/**
	 * Method to get the id for a quiz question
	 *
	 * @param   string  $segment  Segment of the article to retrieve the ID for
	 * @param   array   $query    The request that is parsed right now
	 *
	 * @return  mixed   The id of this item or false
	 */
	public function getQuizquestionId($segment, $query)
	{
		return $this->getViewId($segment, '#__splms_quizquestions');
	}

	/**
	 * Method to get the segment(s) for a speaker
	 *
	 * @param   string  $id     ID of the article to retrieve the segments for
	 * @param   array   $query  The request that is built right now
	 *
	 * @return  array|string  The segments of this item
	 */
	public function getSpeakerSegment($id, $query)
	{
		return $this->getViewSegment($id, '#__splms_speakers');
	}

	/**
	 * Method to get the id for a speaker
	 *
	 * @param   string  $segment  Segment of the article to retrieve the ID for
	 * @param   array   $query    The request that is parsed right now
	 *
	 * @return  mixed   The id of this item or false
	 */
	public function getSpeakerId($segment, $query)
	{
		return $this->getViewId($segment, '#__splms_speakers');
	}

	/**
	 * Method to get the segment(s) for a speaker
	 *
	 * @param   string  $id     ID of the article to retrieve the segments for
	 * @param   array   $query  The request that is built right now
	 *
	 * @return  array|string  The segments of this item
	 */
	public function getCertificateSegment($id, $query)
	{
		return $this->getViewSegment($id, '#__splms_certificates');
	}

	/**
	 * Method to get the id for a speaker
	 *
	 * @param   string  $segment  Segment of the article to retrieve the ID for
	 * @param   array   $query    The request that is parsed right now
	 *
	 * @return  mixed   The id of this item or false
	 */
	public function getCertificateId($segment, $query)
	{
		return $this->getViewId($segment, '#__splms_certificates');
	}
}

/**
 * Content router functions
 *
 * These functions are proxys for the new router interface
 * for old SEF extensions.
 *
 * @param   array  &$query  An array of URL arguments
 *
 * @return  array  The URL arguments to use to assemble the subsequent URL.
 *
 * @deprecated  4.0  Use Class based routers instead
 */
function splmsBuildRoute(&$query)
{
	$app = Factory::getApplication();
	$router = new SplmsRouter($app, $app->getMenu());

	return $router->build($query);
}

/**
 * Parse the segments of a URL.
 *
 * This function is a proxy for the new router interface
 * for old SEF extensions.
 *
 * @param   array  $segments  The segments of the URL to parse.
 *
 * @return  array  The URL attributes to be used by the application.
 *
 * @since   3.3
 * @deprecated  4.0  Use Class based routers instead
 */
function splmsParseRoute($segments)
{
	$app = Factory::getApplication();
	$router = new SplmsRouter($app, $app->getMenu());

	return $router->parse($segments);
}
