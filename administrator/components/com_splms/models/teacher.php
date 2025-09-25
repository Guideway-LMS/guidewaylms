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

class SplmsModelTeacher extends AdminModel {
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

	public function getTable($type = 'Teacher', $prefix = 'SplmsTable', $config = array()) {
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
		$form = $this->loadForm('com_splms.teacher', 'teacher', array( 'control' => 'jform', 'load_data' => $loadData ) );

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
	protected function loadFormData($data = array(), $loadData = true) {

		// Check the session for previously entered form data.
		$data = Factory::getApplication()->getUserState( 'com_splms.edit.teacher.data', array() );
		if (empty($data)) {
			$data = $this->getItem();
		}
		// check access if not then throw error
		$user = Factory::getUser();
		$jinput = Factory::getApplication()->input;
		$id = $jinput->get('a_id', $jinput->get('id', 0));
		if (!$user->authorise('core.edit', 'com_splms.teacher.' . (int) $id) && ($user->authorise('core.edit.own', 'com_splms.teacher.' . (int) $id) && $data->created_by != $user->id) ) {
			$getView = $jinput->get('view', 'teacher');
			$error_message = Text::_('COM_SPLMS_ERROR_YOU_HAVE_NO_ACCESS') . '(#'. $id .')';
			$app = Factory::getApplication();
			$app->redirect(Route::_('index.php?option=com_splms&view='. $getView.'s', false), $error_message, 'error');
		}

		if(isset($data->specialist_in) && $data->specialist_in){
			// course schedules
			$specialist_in_decode =  json_decode($data->specialist_in, true);
			
			if ($specialist_in_decode) {
				$this->specialist_in =  $specialist_in_decode;
			} else {
				$specialist_explodes = explode(', ', $data->specialist_in);
				$special_items = array();
				foreach ($specialist_explodes as $key => $specialist_explode) {
					$key ++;
					$special_items['specialist_in'.$key] = array('specialist_text' => $specialist_explode, 'specialist_number' => '');

				}
				$data->specialist_in = json_encode($special_items);
			}
			
			if(is_null($data->specialist_in) || empty($data->specialist_in)) {
				$data->specialist_in = array();
			}
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
				
				$table = Table::getInstance('Teacher', 'SplmsTable');
				while ($table->load(array('alias' => $data['alias']))) {
					$data['alias'] = StringHelper::increment($data['alias'], 'dash');
				}
			}
		}

		if (parent::save($data)) {
			return true;
		}

		return false;
	}

	/**
	* Method to check if it's OK to delete a message. Overwrites JModelAdmin::canDelete
	*/
	protected function canDelete($record) {
		if (!empty($record->id)) {
			if ($record->published != -2) {
				return false;
			}

			return Factory::getUser()->authorise('core.delete', 'com_splms.teacher.' . (int) $record->id);
		}

		return false;
	}
}
