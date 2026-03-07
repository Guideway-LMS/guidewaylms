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
use Joomla\CMS\Language\Text;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

class SplmsModelLessons extends ListModel {

	protected static $message = '';
	protected static $files = array();

	protected function getListQuery() {
		$app = Factory::getApplication();
		$user = Factory::getUser();

		$app 			= Factory::getApplication();
		$params   		= $app->getMenu()->getActive()->params;
		// Create a new query object.
		$db = $this->getDbo();
		$query = $db->getQuery(true);
		$query->select('a.*');
		$query->from($db->quoteName('#__splms_lessons', 'a'));
		//Authorised
		$groups = implode(',', $user->getAuthorisedViewLevels());
		$query->where('a.access IN (' . $groups . ')');
		// Filter by language
		$query->where('a.language in (' . $db->quote(Factory::getLanguage()->getTag()) . ',' . $db->quote('*') . ')');
		$query->where('a.published = 1');
		$query->order($db->quoteName('a.ordering') . ' DESC');

		return $query;
	}

	protected function populateState($ordering = null, $direction = null) {
		$app = Factory::getApplication('site');
		$params = $app->getParams();
		$this->setState('list.start', $app->input->get('limitstart', 0, 'uint'));
		$limit = $params->get('limit');
		$this->setState('list.limit', $limit);
	}

	//if item not found
	public function &getItem($id = null) {
		$item = parent::getItem($id);
		if(Factory::getApplication()->isSite()) {
			if($item->id) {
				return $item;
			} else {
				throw new \Exception(Text::_('COM_SPLMS_NO_ITEMS_FOUND'), 404);
			}
		} else {
			return $item;
		}
	}

	// Get Lessons by course ID
	public static function getLessons($course_id) {
		// Load Lessons model
		jimport('joomla.application.component.model');
		BaseDatabaseModel::addIncludePath(JPATH_SITE.'/components/com_splms/models');
		$teachers_model = BaseDatabaseModel::getInstance( 'Teachers', 'SplmsModel' );

		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select(array('a.*'));
		$query->from($db->quoteName('#__splms_lessons', 'a'));
		$query->where($db->quoteName('a.published')." = 1");
		$query->where($db->quoteName('a.course_id')." = ".$db->quote($course_id));
		$query->order('a.ordering DESC');
		$db->setQuery($query);
		$lessons = $db->loadObjectList();

		foreach ($lessons as &$lesson) {
			$lesson->teacher_name = $teachers_model->getTeacher($lesson->teacher_id);
			$lesson->teacher_url  = Route::_('index.php?option=com_splms&view=teacher&id='. $lesson->teacher_id);
			$lesson->lesson_url = Route::_('index.php?option=com_splms&view=lesson&id='.$lesson->id.':'.$lesson->alias . SplmsHelper::getItemid('courses'));
		}

		return $lessons;
	}

	// Get Free Lessons of course by course ID
	public static function getCourseFreeLesson($course_id) {
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select($db->quoteName(array('id', 'title', 'alias', 'video_url')));
		$query->from($db->quoteName('#__splms_lessons'));
		$query->where($db->quoteName('published')." = 1");
		$query->where($db->quoteName('lesson_type')." = 0");
		$query->where($db->quoteName('course_id')." = ".$db->quote($course_id));
		$query->setLimit(1);
		$query->order('ordering ASC');
		$db->setQuery($query);
		$results = $db->loadObjectList();
		return $results;
	}

	// Get Lessons By Teacher ID (Teachers Lessons)
	public static function getTeacherLessons($teacher_id) {

		jimport('joomla.application.component.model');
		BaseDatabaseModel::addIncludePath(JPATH_SITE.'/components/com_splms/models');
		$courses_model = BaseDatabaseModel::getInstance( 'Courses', 'SplmsModel' );
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select($db->quoteName(array('id', 'title', 'alias', 'course_id', 'video_duration', 'created', 'lesson_type')));
		$query->from($db->quoteName('#__splms_lessons'));
		$query->where($db->quoteName('published')." = 1");
		$query->where($db->quoteName('teacher_id')." = ".$db->quote($teacher_id));
		$query->order('ordering DESC');
		$db->setQuery($query);
		$results = $db->loadObjectList();

		foreach ($results as &$result) {
			$result->lesson_url = Route::_('index.php?option=com_splms&view=lesson&id='.$result->id.':'.$result->alias . SplmsHelper::getItemid('courses'));	
			$result->get_course_info = $courses_model->getCourse($result->course_id);
		}

		return $results;
	}

	// completedItem
	public function completedItem($item_id = 0, $item_type = NULL, $user_id = 0){
		$db = Factory::getDbo();
		    // Verifica se já está concluído
    if (self::hasCompleted($item_id, $user_id, $item_type)) {
        return true; // Já existe → evita duplicação
    }
		$query = $db->getQuery(true);
		$columns = array('user_id', 'item_id', 'item_type', 'published', 'created', 'modified');
		$values  = array($db->quote($user_id), $db->quote($item_id), $db->quote($item_type), $db->quote(1), $db->quote(Factory::getDate()->toSql()), $db->quote(Factory::getDate()->toSql()));
		$query
		    ->insert($db->quoteName('#__splms_useritems'))
		    ->columns($db->quoteName($columns))
		    ->values(implode(',', $values));
	    $db->setQuery($query);
		$db->execute();

		 // 👇 NOVO: verificar conclusão de curso ERICK 21-02
    	if ($item_type !== 'course') {
        	$this->checkCourseCompletion($item_id, $user_id);
    	}
		return true;
	}

	// has completed
	public static function hasCompleted($item_id, $user_id, $item_type) {
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('COUNT(*)');
		$query->from($db->quoteName('#__splms_useritems'));
		$query->where($db->quoteName('published')." = 1");
		$query->where($db->quoteName('item_id')." = ".$db->quote($item_id));
		$query->where($db->quoteName('user_id')." = ".$db->quote($user_id));
		$query->where($db->quoteName('item_type')." = ".$db->quote($item_type));
		$db->setQuery($query);
		$result = $db->loadResult();

		return $result;
	}
	//Checa se tiver curso ta com 100%   ERICK 21-02
	private function checkCourseCompletion($lesson_id, $user_id)
	{
    $courseId = $this->getCourseIdFromLesson($lesson_id);

    if (!$courseId) {
        return;
 		}

    // Instancia o CourseModel
    \Joomla\CMS\MVC\Model\BaseDatabaseModel::addIncludePath(JPATH_SITE . '/components/com_splms/models');
    $courseModel = \Joomla\CMS\MVC\Model\BaseDatabaseModel::getInstance('Course', 'SplmsModel');

    if (!$courseModel) {
        return; // Prevents calling method on boolean error
    }

    $progress = $courseModel->getCourseProgress($courseId, $user_id);

    if ($progress >= 100 && !self::hasCompleted($courseId, $user_id, 'course')) {
        $this->completedItem($courseId, 'course', $user_id);
   	 }
	}
	
	private function getCourseIdFromLesson($lesson_id)
	{
    $db = Factory::getDbo();
    $query = $db->getQuery(true);

    $query->select($db->quoteName('course_id'))
          ->from($db->quoteName('#__splms_lessons'))
          ->where($db->quoteName('id') . ' = ' . (int) $lesson_id);

    $db->setQuery($query);

    $courseId = $db->loadResult();

    return $courseId ? (int) $courseId : null;
	}

	// **** Attachment Upload system **** //
	// remove attachment by id
	public static function removeAttachmentByID($lessonid = '') {
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$fields = array($db->quoteName('attachment') . ' = ' . $db->quote(NULL) );
		$conditions = array($db->quoteName('splms_lesson_id') . ' = '. $db->quote($lessonid) );
		$query->update($db->quoteName('#__splms_lessons'))->set($fields)->where($conditions);
		$db->setQuery($query);
		$result = $db->execute();

        return true;
	}
	// check exist attachment
	protected static function existAttachment($lesson_id){
		$lmsdb = Factory::getDbo();
		$lmsquery = $lmsdb->getQuery(true);
		$lmsquery->select('attachment');
		$lmsquery->from($lmsdb->quoteName('#__splms_lessons'));
		$lmsquery->where($lmsdb->quoteName('splms_lesson_id')." = ".$lesson_id);
		$lmsdb->setQuery($lmsquery);
		$result = $lmsdb->loadObject();

		return $result;
	}

	// check file type
	protected static function fileExtensionCheck($file,$allowed){
		$ext = pathinfo($file['name'], PATHINFO_EXTENSION);
		if(in_array( strtolower($ext), $allowed) ) {
			return true;
		}

		return false;
	}

	protected function onBeforeSave(&$model, &$data){
		$jinput 	= Factory::getApplication()->input;
		$file = $jinput->files->get('attachment');
		$lesson_id = $model['splms_lesson_id'];

		if ( $file['name']!= '' && $file['name']) {
			if ( self::fileExtensionCheck($file, array('png', 'jpg', 'pdf', 'zip') )) {
				self::$files['attachment'] = $file;
			}else{
				throw new RuntimeException(Text::_('INVALID_FILE_TYPE'), 1);
			}

			// has acchatment before
			$exist_attachment = '';
			$exist_attachment = self::existAttachment($lesson_id)->attachment;

			// delete attachment
			if ($exist_attachment != '') {
				File::delete(JPATH_ROOT. '/media/com_splms/lessons/attachments/' .$exist_attachment);
			}
		}
		// unset attachment
		unset($model['attachment']);

		return true;
	}

	protected function onAfterSave(&$model){

		$app = Factory::getApplication();
		$jinput 	= Factory::getApplication()->input;
		// Get Lesson ID
		$lesson_id = $model->splms_lesson_id;
		// Get attachment file
		$attachment_file = (self::$files) ? self::$files['attachment'] : '' ;

		$db = Factory::getDbo();
		$query = $db->getQuery(true);

		// has file
		if ($attachment_file != '') {
			$folder_path = JPATH_ROOT . '/media/com_splms/lessons/attachments/' . $lesson_id;

			if (!Folder::exists( $folder_path )) {
				Folder::create($folder_path);
			}

			// Cleans the name of teh file by removing weird characters
			$filename = File::makeSafe($attachment_file['name']);

			$src = $attachment_file['tmp_name'];
			//Folder::create($folder_path);

			if (File::upload($src, $folder_path. '/' .$attachment_file['name'])) {
				// Fields to update.
				$fields = array(
				    $db->quoteName('attachment') . ' = ' . $db->quote('/' . $lesson_id . '/' . $attachment_file['name'] )
				);
				//$query->update($db->quoteName('#__splms_lessons'))->set('attachment')->where();
				$query->update($db->quoteName('#__splms_lessons'))->set($fields)->where($db->quoteName('splms_lesson_id') . ' = ' . $lesson_id);
				$db->setQuery($query);
				$result = $db->execute();
			}else{
				return Factory::getApplication()->enqueueMessage('Couldn\'t update file ', 'error');
			}
		} // END:: has file

		return true;
	}


}
