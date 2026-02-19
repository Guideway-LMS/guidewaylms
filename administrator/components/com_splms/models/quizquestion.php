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
use Joomla\CMS\Table\Table;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\String\StringHelper;
use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\MVC\Model\AdminModel;
class SplmsModelQuizquestion extends AdminModel {
	/**
	* Method to get a table object, load it if necessary.
	*
	* @param   string  $type    The table name. Optional.
	* @param   string  $prefix  The class prefix. Optional.
	* @param   array   $config  Configuration array for model. Optional.
	*
	* @return  JTable  A JTable object
	*
	* @since   1.6
	*/

	public function getTable($type = 'Quizquestion', $prefix = 'SplmsTable', $config = array()) {
		return Table::getInstance($type, $prefix, $config);
	}

	/**
	* Method to get the record form.
	*
	* @param   array    $data      Data for the form.
	* @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	*
	* @return  mixed    A JForm object on success, false on failure
	*
	* @since   1.6
	*/
	public function getForm($data = array(), $loadData = true) {
		// Get the form.
		$form = $this->loadForm('com_splms.quizquestion', 'quizquestion', array( 'control' => 'jform', 'load_data' => $loadData ) );
		if (empty($form)) {
			return false;
		}

		return $form;
	}

	/**
	* Method to get the data that should be injected in the form.
	*
	* @return  mixed  The data for the form.
	*
	* @since   1.6
	*/
	protected function loadFormData() {
		// Check the session for previously entered form data.
		$data = Factory::getApplication()->getUserState( 'com_splms.edit.quizquestion.data', array() );
		if (empty($data)) {
			$data = $this->getItem();
		}
		
		// check access if not then throw error
		$user = Factory::getUser();
		$jinput = Factory::getApplication()->input;
		$id = $jinput->get('a_id', $jinput->get('id', 0));
		if (!$user->authorise('core.edit', 'com_splms.teacher.' . (int) $id) && ($user->authorise('core.edit.own', 'com_splms.teacher.' . (int) $id) && $data->created_by != $user->id) && ($data->id && $id)) {
			$getView = $jinput->get('view', 'teacher');
			$error_message = Text::_('COM_SPLMS_ERROR_YOU_HAVE_NO_ACCESS') . '(#'. $id .')';
			$app = Factory::getApplication();
			$message = Text::sprintf('JERROR_SAVE_FAILED');
			$app->redirect(Route::_('index.php?option=com_splms&view='. $getView.'s', false), $error_message, 'error');
		}

		return $data;
	}

	public function save($data) {
		$input  = Factory::getApplication()->input;
		$filter = InputFilter::getInstance();

		// Automatic handling of alias for empty fields
		if (in_array($input->get('task'), array('apply', 'save')) && (!isset($data['id']) || (int) $data['id'] == 0)) {
			if ($data['alias'] == null) {
				if (Factory::getConfig()->get('unicodeslugs') == 1) {
					$data['alias'] = OutputFilter::stringURLUnicodeSlug($data['title']);
				} else {
					$data['alias'] = OutputFilter::stringURLSafe($data['title']);
				}

				$table = Table::getInstance('Quizquestion', 'SplmsTable');

				while ($table->load(array('alias' => $data['alias']))) {
					$data['alias'] = StringHelper::increment($data['alias'], 'dash');
				}
			}
		}

		if (isset($data['quizquestion_schedules']) && is_array($data['quizquestion_schedules'])) {
			$data['quizquestion_schedules'] = json_encode($data['quizquestion_schedules']);
		}

		if (parent::save($data)) {
			
			// GUIDEWAY CUSTOM: Auto-Create/Update Lesson
			$quizId = (int)$this->getState($this->getName() . '.id');
			$topicId = isset($data['topic_id']) ? (int)$data['topic_id'] : 0;

			if ($quizId > 0 && $topicId > 0) {
				$this->createOrUpdateLesson($quizId, $data);
			}

			return true;
		}

		return false;
	}

	/**
	 * Creates or Updates a Lesson "wrapper" for the Quiz.
	 */
	protected function createOrUpdateLesson($quizId, $data) {
		
		// Load Lesson Table
		$lessonTable = Table::getInstance('Lesson', 'SplmsTable');
		
		// Check if a lesson already exists for this quiz
		$db = Factory::getDbo();
		$query = $db->getQuery(true)
			->select('id')
			->from('#__splms_lessons')
			->where('quiz_id = ' . (int)$quizId)
			->where('lesson_format = ' . $db->quote('quiz'));
		$db->setQuery($query);
		$existingId = (int)$db->loadResult();

		if ($existingId) {
			$lessonTable->load($existingId);
		} else {
			// New Lesson Defaults
			$lessonTable->lesson_format = 'quiz';
			$lessonTable->quiz_id = $quizId;
			$lessonTable->published = 1;
			$lessonTable->created = Factory::getDate()->toSql();
			$lessonTable->created_by = Factory::getUser()->id;
			$lessonTable->lesson_type = isset($data['quiz_type']) ? $data['quiz_type'] : 1;
			
			// Generate unique alias
			if (Factory::getConfig()->get('unicodeslugs') == 1) {
				$lessonTable->alias = OutputFilter::stringURLUnicodeSlug($data['title']) . '-quiz';
			} else {
				$lessonTable->alias = OutputFilter::stringURLSafe($data['title']) . '-quiz';
			}
		}

		// Sync Fields
		$lessonTable->title = $data['title'];
		$lessonTable->course_id = isset($data['course_id']) ? $data['course_id'] : 0;
		$lessonTable->topic_id = isset($data['topic_id']) ? $data['topic_id'] : 0;
		$lessonTable->description = isset($data['description']) ? $data['description'] : '';
		
		// Set Defaults for required fields to avoid SQL errors
		if (empty($lessonTable->vdo_thumb)) $lessonTable->vdo_thumb = '';
		if (empty($lessonTable->video_url)) $lessonTable->video_url = '';
		if (empty($lessonTable->attachment)) $lessonTable->attachment = '';

		// GUIDEWAY CUSTOM: Sync New Fields
		$lessonTable->passing_score = isset($data['passing_score']) ? $data['passing_score'] : null;
		$lessonTable->is_optional = isset($data['is_optional']) ? $data['is_optional'] : 0;

		// Attempt to Save
		try {
			if ($lessonTable->store()) {
				Factory::getApplication()->enqueueMessage('Lição vinculada ao Quiz criada/atualizada com sucesso!', 'message');
			} else {
				Factory::getApplication()->enqueueMessage('Erro ao salvar Lição Automática: ' . $lessonTable->getError(), 'error');
			}
		} catch (Exception $e) {
			Factory::getApplication()->enqueueMessage('Aviso: Não foi possível sincronizar a Lição automaticamente. ' . $e->getMessage(), 'warning');
		}
	}


	/**
	* Method to check if it's OK to delete a message. Overwrites JModelAdmin::canDelete
	*/
	protected function canDelete($record) {
		if (!empty($record->id)) {
			if ($record->published != -2) {
				return false;
			}

			return Factory::getUser()->authorise('core.delete', 'com_splms.quizquestion.' . (int) $record->id);
		}

		return false;
	}
	/**
	 * Handle old data and convert to new
	 * @param data the old data
	 * @param keys key exists on the data
	 * @param prefix index name prefix
	*/
	public function old2new($data, $keys = array(), $prefix = '' )
	{
		foreach($keys as $key)
			if (!array_key_exists($key, $data)) 
				return false;

		$index = 0;
		$arr = array();
		foreach($data[$keys[0]] as $i => $d)
		{
			$temp = array();
			foreach($keys as $k) 
				$temp[$k] = $data[$k][$i];

			$arr[$prefix.$index] = $temp;
			$index++;
		}
		return $arr;
	}

	public function getItem($pk = null)
	{
		if ($item = parent::getItem($pk))
		{
			$item->list_answers = ($item->list_answers) ? json_decode($item->list_answers, true) : '';
			if (!is_bool($list_answers = $this->old2new((array)$item->list_answers, ['qes_title', 'ans_one','ans_two','ans_three','ans_four','right_ans'], 'list_answers')))
			{
				$item->list_answers = $list_answers;
			}
			
			if (is_array($item->list_answers))
			{
				$item->list_answers = json_encode($item->list_answers);
			}
			return $item;
		}
		return parent::getItem($pk);
	}
}
