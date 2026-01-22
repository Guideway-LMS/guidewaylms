<?php
/**
 * @package     SP LMS
 * @subpackage  Components
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Factory;

class SplmsModelForums extends ListModel
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id', 'a.id',
				'title', 'a.title',
				'author_name', 'u.name',
				'created_on', 'a.created_on',
                'course_title', 'c.title'
			);
		}

		parent::__construct($config);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @return  void
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		$app = Factory::getApplication();
		$courseId = $app->input->getInt('course_id');
		$this->setState('filter.course_id', $courseId);

		parent::populateState('a.id', 'desc');
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery
	 */
	protected function getListQuery()
	{
		$db = $this->getDbo();
		$query = $db->getQuery(true);

		// Select the required fields from the table.
		$query->select(
			$this->getState(
			'list.select',
				'a.id, a.title, a.created_on, a.votes, a.views, a.solved, ' .
                '(SELECT COUNT(ans.id) FROM #__splms_forum_answers AS ans WHERE ans.question_id = a.id) AS total_answers'
			)
		);
		$query->from($db->quoteName('#__splms_forum_questions', 'a'));

		// Join over the users for the author name.
		$query->select($db->quoteName('u.name', 'author_name'));
		$query->join('LEFT', $db->quoteName('#__users', 'u') . ' ON ' . $db->quoteName('u.id') . ' = ' . $db->quoteName('a.user_id'));

        // Join over the courses for the course title.
        $query->select($db->quoteName('c.title', 'course_title'));
        $query->join('LEFT', $db->quoteName('#__splms_courses', 'c') . ' ON ' . $db->quoteName('c.id') . ' = ' . $db->quoteName('a.course_id'));

        // Filter by Course ID
        $courseId = $this->getState('filter.course_id');
        if (is_numeric($courseId) && $courseId > 0)
        {
            $query->where('a.course_id = ' . (int) $courseId);
        }

		// Filter by search in title
		$search = $this->getState('filter.search');
		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where('a.id = ' . (int) substr($search, 3));
			}
			else
			{
				$search = $db->quote('%' . str_replace(' ', '%', $db->escape(trim($search), true) . '%'));
				$query->where('(a.title LIKE ' . $search . ')');
			}
		}

		// Add the list ordering clause.
		$orderCol  = $this->state->get('list.ordering', 'a.id');
		$orderDirn = $this->state->get('list.direction', 'desc');
		$query->order($db->escape($orderCol . ' ' . $orderDirn));

		return $query;
	}
}
