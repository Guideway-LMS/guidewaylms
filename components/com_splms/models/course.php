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

class SplmsModelCourse extends ItemModel {

	protected $_context = 'com_splms.course';

	protected function populateState() {
		$app = Factory::getApplication('site');
		$itemId = $app->input->getInt('id');
		$this->setState('course.id', $itemId);
		$this->setState('filter.language', Multilanguage::isEnabled());
	}

	public function getItem( $itemId = null ) {
		$user = Factory::getUser();

		$itemId = (!empty($itemId))? $itemId : (int)$this->getState('course.id');

		if ( $this->_item == null ) {
			$this->_item = array();
		}

		if (!isset($this->_item[$itemId])) {
			try {
				$db = $this->getDbo();
				$query = $db->getQuery(true);
				$query->select('a.*,a.duration as courseDuration');
				$query->from('#__splms_courses as a');
				$query->where('a.id = ' . (int) $itemId);
				
				// Filter by published state.
				$query->where('a.published = 1');

				if ($this->getState('filter.language')) {
					$query->where('a.language in (' . $db->quote(Factory::getLanguage()->getTag()) . ',' . $db->quote('*') . ')');
				}

				//Authorised
				$groups = implode(',', $user->getAuthorisedViewLevels());
				$query->where('a.access IN (' . $groups . ')');

				// lessons count
				$query->select("CASE WHEN lessons.count IS NULL THEN 0 ELSE lessons.count END as lessonsCount")
					->join('LEFT', '( SELECT c.teacher_id, COUNT(c.id) AS count FROM #__splms_lessons c WHERE c.published = 1 GROUP BY c.teacher_id ) AS lessons ON a.id = lessons.teacher_id');

				// teachers count
				$query->select("CASE WHEN teachers.count IS NULL THEN 0 ELSE teachers.count END as teachersCount")
					->join('LEFT', '( SELECT t.course_id, COUNT(t.id) AS count FROM #__splms_lessons t WHERE t.published = 1 GROUP BY t.teacher_id ) AS teachers ON a.id = teachers.course_id');
				
				// orders count
				$query->select("CASE WHEN orders.count IS NULL THEN 0 ELSE orders.count END as ordersCount")
					->join('LEFT', '( SELECT o.course_id, COUNT(o.id) AS count FROM #__splms_orders o WHERE o.published = 1 GROUP BY o.course_id ) AS orders ON a.id = orders.course_id');

				// join over course category
				$query->join('LEFT', $db->quoteName('#__splms_coursescategories', 'b') . ' ON (' . $db->quoteName('a.coursecategory_id') . ' = ' . $db->quoteName('b.id') . ')');

				// Duration
				$query->select("CASE WHEN lessonsDuration.timeSum IS NULL THEN '00:00:00' ELSE lessonsDuration.timeSum END as duration")
					->join('LEFT', '( SELECT b.course_id, SEC_TO_TIME( ROUND( SUM( TIME_TO_SEC(b.video_duration) ) ) ) AS timeSum FROM #__splms_lessons b WHERE b.published = 1 GROUP By b.course_id) AS lessonsDuration ON a.id = lessonsDuration.course_id');

				$db->setQuery($query);
				$data = $db->loadObject();

				if (empty($data)) {
					 throw new Exception(Text::_('COM_SPLMS_ERROR_ITEM_NOT_FOUND'), 404);
				}

				$user = Factory::getUser();
				$groups = $user->getAuthorisedViewLevels();
				if(!in_array($data->access, $groups)) {
					 throw new Exception(Text::_('COM_SPLMS_ERROR_NOT_AUTHORISED'), 404);
				}

				$data->link = Route::_('index.php?option=com_splms&view=course&id=' . $data->id . ':' . $data->alias . SplmsHelper::getItemid('courses'));

				$data->topics = $this->getTopics((int) $data->id);
				if(empty($data->topics)) {
					$data->lessons = $this->getLessons((int) $data->id);
				}

				$data->price 	  = SplmsHelper::formatPrice($data->price);
				$data->sale_price = SplmsHelper::formatPrice($data->sale_price) ?: $data->sale_price;

				$this->_item[$itemId] = $data;
			}
			catch (Exception $e) {
				if ($e->getCode() == 404 ) {
					throw new Exception($e->getMessage(), 404);
				} else {
					Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');
					$this->_item[$itemId] = false;
				}
			}
		}

		return $this->_item[$itemId];
	}

	public function getTopics($course_id) {
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select(array('a.id', 'a.title', 'a.description'));
		$query->from($db->quoteName('#__splms_lessiontopics', 'a'));
		$query->where($db->quoteName('a.course_id')." = ".$db->quote($course_id));
		$query->where($db->quoteName('a.published')." = 1");
		$query->order('a.ordering DESC');
		$db->setQuery($query);
		$topics = $db->loadObjectList();

		if(!empty($topics) && count($topics)) {
			foreach($topics as &$topic) {
				$topic->lessons = $this->getLessons($course_id, $topic->id);
			}

			// Others lessons
			// $other = new stdClass();
			// $other->id = 0;
			// $other->title = 'Others';
			// $other->description = '';
			// $other->lessons = $this->getLessons($course_id);

			// array_push($topics, $other);
		}

		return $topics;
	}

	private function getLessons($course_id, $topic_id = 0) {
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select(array('a.*', 'b.title AS teacher_name', 'b.alias AS teacheralias'));
		$query->from($db->quoteName('#__splms_lessons', 'a'));
		$query->join('LEFT', $db->quoteName('#__splms_teachers', 'b') . ' ON (' . $db->quoteName('a.teacher_id') . ' = ' . $db->quoteName('b.id') . ')');
		$query->where($db->quoteName('a.published')." = 1");
		$query->where($db->quoteName('a.course_id')." = ".$db->quote($course_id));
		$query->where($db->quoteName('a.topic_id')." = ".$db->quote($topic_id));
		$query->order('a.ordering DESC');
		$db->setQuery($query);
		$lessons = $db->loadObjectList();

		if(!empty($lessons) && count($lessons)) {
			foreach ($lessons as &$lesson) {
				$lesson->teacher_url  = Route::_('index.php?option=com_splms&view=teacher&id='. $lesson->teacher_id . ':' . $lesson->teacheralias . SplmsHelper::getItemid('courses'));
				$lesson->lesson_url = Route::_('index.php?option=com_splms&view=lesson&id='.$lesson->id.':'.$lesson->alias . SplmsHelper::getItemid('courses'));
			}
		}

		return $lessons;
	}
	
	/**
 * Calcula o percentual de progresso de um usuário em um curso.
 * Esta é a função para a [SPRINT 2] Criar função de cálculo da barra de progresso (%).
 *
 * @param int $userId ID do usuário.
 * @param int $courseId ID do curso.
 * @return float O percentual de progresso (ex: 75.00).
 */
    /**
     * Calcula o percentual de progresso de um usuário em um curso.
     * GUIDEWAY CUSTOM - Integração Vitória + Joshua + Eric
     * 
     * @param int $userId ID do usuário.
     * @param int $courseId ID do curso.
     * @return array Dados do progresso completo
     */
    public function getCourseProgress($userId, $courseId)
    {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $query->select('COUNT(id)')
            ->from($db->quoteName('#__splms_lessons'))
            ->where($db->quoteName('course_id') . ' = ' . (int)$courseId)
            ->where($db->quoteName('published') . ' = 1');

        $db->setQuery($query);
        $totalLessons = (int) $db->loadResult();

        $query->clear();
        $query->select('COUNT(DISTINCT p.lesson_id)')
            ->from($db->quoteName('#__splms_course_progress', 'p'))
            ->join('INNER', $db->quoteName('#__splms_lessons', 'l') . ' ON p.lesson_id = l.id')
            ->where($db->quoteName('p.user_id') . ' = ' . (int)$userId)
            ->where($db->quoteName('p.status') . ' = ' . $db->quote('Concluido'))
            ->where($db->quoteName('l.course_id') . ' = ' . (int)$courseId);

        $db->setQuery($query);
        $completedLessons = (int) $db->loadResult();

        $progress = 0.00;
        if ($totalLessons > 0) {
            $progress = round(($completedLessons / $totalLessons) * 100, 2);
        }

        return [
            'total_aulas' => $totalLessons,
            'aulas_completas' => $completedLessons,
            'porcentagem' => (int)round($progress, 0)
        ];
    }
}
