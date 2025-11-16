<?php
/**
 * @package com_splms
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2024 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
 **/

defined('_JEXEC') or die('Restricted Access');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\FormController;

class SplmsControllerLesson extends FormController {

	public function __construct($config = array()) {
		parent::__construct($config);
	}

	public function getModel($name = 'Lessons', $prefix = 'SplmsModel', $config = array()) {
		return parent::getModel($name, $prefix, $config);
	}
	public function completeditem() {
    $model  = $this->getModel();
    $user   = Factory::getUser();
    $input  = Factory::getApplication()->input;
    $output = array();
    
    if(!$user->id) {
        $output['status'] = false;
        $output['content'] = Text::_('COM_SPLMS_LOGIN_TO_REVIEW');
        echo json_encode($output);
        die();
    }
    
    $userId = $input->getInt('user_id', 0);
    $itemId = $input->getInt('item_id', 0);
    $itemType = $input->getString('item_type', '');
    
    if (!$userId || !$itemId || $itemType !== 'lesson') {
        $output['status'] = false;
        $output['content'] = 'Dados inválidos';
        echo json_encode($output);
        die();
    }
    
    if ($user->id != $userId) {
        $output['status'] = false;
        $output['content'] = 'Usuário não autorizado';
        echo json_encode($output);
        die();
    }
    
    try {
        $db = Factory::getDbo();
        $query = "INSERT INTO " . $db->quoteName('#__splms_course_progress') . " 
    (" . $db->quoteName('user_id') . ", " . $db->quoteName('lesson_id') . ", " . $db->quoteName('status') . ", " . $db->quoteName('progress') . ")
    VALUES (" . $db->quote($userId) . ", " . $db->quote($itemId) . ", 'Concluido', 100.00)
    ON DUPLICATE KEY UPDATE 
        " . $db->quoteName('status') . " = 'Concluido',
        " . $db->quoteName('progress') . " = 100.00,
        " . $db->quoteName('updated_at') . " = NOW()";
        
        $db->setQuery($query);
        $result = $db->execute();
        
        $output['status'] = true;
        $output['content'] = 'Aula marcada como concluída!';
        
    } catch (Exception $e) {
        $output['status'] = false;
        $output['content'] = 'Erro ao salvar: ' . $e->getMessage();
    }
    
    echo json_encode($output);
    die();
}

public function hascompleted() {
    $user = Factory::getUser();
    $input = Factory::getApplication()->input;
    $output = array();
    
    if(!$user->id) {
        $output['completed'] = false;
        echo json_encode($output);
        die();
    }
    
    $userId = $input->getInt('user_id', 0);
    $lessonId = $input->getInt('lesson_id', 0);
    
    if (!$userId || !$lessonId) {
        $output['completed'] = false;
        echo json_encode($output);
        die();
    }
    
    $db = Factory::getDbo();
    $query = $db->getQuery(true);
    
    $query->select($db->quoteName('status'))
    ->from($db->quoteName('#__splms_course_progress'))
          ->where($db->quoteName('user_id') . ' = ' . $db->quote($userId))
          ->where($db->quoteName('lesson_id') . ' = ' . $db->quote($lessonId));
    
    $db->setQuery($query);
    $status = $db->loadResult();
    
    $completed = ($status === 'Concluido');
    
    $output['completed'] = $completed;
    $output['status'] = $status ? $status : 'não iniciado';
    
    echo json_encode($output);
    die();
}
}