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
use Joomla\CMS\Filesystem\File;
use Joomla\String\StringHelper;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\MVC\Model\AdminModel;

class SplmsModelLesson extends AdminModel {
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

	public function getTable($type = 'Lesson', $prefix = 'SplmsTable', $config = array()) {
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
		$form = $this->loadForm('com_splms.lesson', 'lesson', array( 'control' => 'jform', 'load_data' => $loadData ) );

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
		$data = Factory::getApplication()->getUserState( 'com_splms.edit.lesson.data', array() );
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

		$app = Factory::getApplication();
		$input  = $app->input;
		$filter = InputFilter::getInstance();

		// Automatic handling of alias for empty fields
		if (in_array($input->get('task'), array('apply', 'save')) && (!isset($data['id']) || (int) $data['id'] == 0)) {
			if ($data['alias'] == null) {
				if (Factory::getConfig()->get('unicodeslugs') == 1) {
					$data['alias'] = OutputFilter::stringURLUnicodeSlug($data['title']);
				} else {
					$data['alias'] = OutputFilter::stringURLSafe($data['title']);
				}

				$table = Table::getInstance('Lesson', 'SplmsTable');

				while ($table->load(array('alias' => $data['alias'], 'course_id' => $data['course_id']))) {
					$data['alias'] = StringHelper::increment($data['alias'], 'dash');
				}
			}
		}

		// Upload Attachment
		if(isset($_FILES['jform']) && $_FILES['jform']) {
			$attachment = $_FILES['jform'];

			if((isset($attachment['name']['attachment']) && $attachment['name']['attachment']) && (isset($attachment['tmp_name']['attachment']) && $attachment['tmp_name']['attachment'])) {
				$name = $attachment['name']['attachment'];
				$path = $attachment['tmp_name']['attachment'];

				if ($attachment['error']['attachment'] == UPLOAD_ERR_OK) {

					$contentLength = (int) $_SERVER['CONTENT_LENGTH'];
					$mediaHelper = new JHelperMedia;
					$postMaxSize = $mediaHelper->toBytes(ini_get('post_max_size'));
					$memoryLimit = $mediaHelper->toBytes(ini_get('memory_limit'));

					// Check for the total size of post back data.
					if (($postMaxSize > 0 && $contentLength > $postMaxSize) || ($memoryLimit != -1 && $contentLength > $memoryLimit)) {
						throw new RuntimeException(Text::_('COM_SPLMS_IMAGE_TOTAL_SIZE_EXCEEDS'), 1);
					}

					$uploadMaxFileSize = $mediaHelper->toBytes(ini_get('upload_max_filesize'));

					if (($attachment['error']['attachment'] == 1) || ($uploadMaxFileSize > 0 && $attachment['size']['attachment'] > $uploadMaxFileSize)) {
						throw new RuntimeException(Text::_('COM_SPLMS_IMAGE_LARGE'), 1);
					}

					$accepted_formats = array('jpg', 'jpeg', 'png', 'gif', 'bmp', 'pdf', 'zip', 'ppt');
					$file_ext = strtolower(File::getExt($name));
					$file_name = File::stripExt($name);

					if(in_array($file_ext, $accepted_formats)) {

						$folder = 'media/com_splms/lessons/attachments';

						if(!Folder::exists( JPATH_ROOT . '/' . $folder )) {
							Folder::create( JPATH_ROOT . '/' . $folder, 0755 );
						}

						if (Factory::getConfig()->get('unicodeslugs') == 1) {
							$filename = OutputFilter::stringURLUnicodeSlug($file_name);
						} else {
							$filename = OutputFilter::stringURLSafe($file_name);
						}

						$i = 0;
						do {
							$base_name = $filename . ($i ? "$i" : "");
							$media_name = $base_name . '.' . $file_ext;
							$i++;
							$dest = JPATH_ROOT . '/' . $folder . '/' . $media_name;
							$relative_path = $folder . '/' . $media_name;
							$src = $folder . '/'  . $media_name;
						} while(File::exists($dest));

						if(File::upload($path, $dest, false, true)) {
							$data['attachment'] = $relative_path;
						} else {
							throw new RuntimeException(Text::_('COM_SPLMS_ATTACHMENT_UPLOAD_FAILED'), 1);
						}
					} else {
						throw new RuntimeException(Text::_('COM_SPLMS_ATTACHMENT_INVALID_FILE_TYPE'), 1);
					}

				}
			}
		}
		// END UPLOAD FILE

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

			return Factory::getUser()->authorise('core.delete', 'com_splms.lesson.' . (int) $record->id);
		}

		return false;
	}

	// Remove attachment
	public static function removeAttachmentByID($lessonid = '') {
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$fields = array($db->quoteName('attachment') . ' = ' . $db->quote(NULL) );
		$conditions = array($db->quoteName('id') . ' = '. $db->quote($lessonid) );
		$query->update($db->quoteName('#__splms_lessons'))->set($fields)->where($conditions);
		$db->setQuery($query);
		$result = $db->execute();

        return true;
	}
}