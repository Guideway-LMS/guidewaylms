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
use Joomla\CMS\MVC\Controller\FormController;

class SplmsControllerCourses extends FormController{

	public function __construct($config = array()) {
		parent::__construct($config);
	}

	public function display($cachable = false, $urlparams = false, $tpl = null){
		
		$cachable = true;
		if (!is_array($urlparams))
		{
			$urlparams = [];
		}
		$additionalParams = array(
			'catid' => 'INT',
			'id' => 'INT',
			'cid' => 'ARRAY',
			'year' => 'INT',
			'month' => 'INT',
			'limit' => 'UINT',
			'limitstart' => 'UINT',
			'showall' => 'INT',
			'return' => 'BASE64',
			'filter' => 'STRING',
			'filter_order' => 'CMD',
			'filter_order_Dir' => 'CMD',
			'filter-search' => 'STRING',
			'print' => 'BOOLEAN',
			'lang' => 'CMD',
			'Itemid' => 'INT');

		$urlparams = array_merge($additionalParams, $urlparams);
		parent::display($cachable, $urlparams, $tpl);
	}

	public function getModel($name = 'form', $prefix = '', $config = array('ignore_request' => true)) {
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}
/**
 * Retorna o progresso do aluno em um curso
 * GUIDEWAY CUSTOM - 2025-11-12 - Joshua - Barra de progresso
 */
public function getCourseProgress() {
    $user = Factory::getUser();
    $input = Factory::getApplication()->input;
    $output = array();
    
    if (!$user->id) {
        $output['success'] = false;
        $output['message'] = 'Usuário não autenticado';
        echo json_encode($output);
        die();
    }
    
    $courseId = $input->getInt('course_id', 0);
    
    if (!$courseId) {
        $output['success'] = false;
        $output['message'] = 'ID do curso não fornecido';
        echo json_encode($output);
        die();
    }
    
    try {
        $db = Factory::getDbo();
        
        $query = "
            SELECT 
                COUNT(DISTINCT l.id) as total_aulas,
                COUNT(DISTINCT p.lesson_id) as aulas_completas,
                ROUND(
                    (COUNT(DISTINCT p.lesson_id) / COUNT(DISTINCT l.id)) * 100, 
                    0
                ) as porcentagem
            FROM " . $db->quoteName('#__splms_lessons') . " l
            LEFT JOIN " . $db->quoteName('#__splms_lesson_progress') . " p 
                ON l.id = p.lesson_id 
                AND p.user_id = " . $db->quote($user->id) . "
            WHERE l.course_id = " . $db->quote($courseId);
        
        $db->setQuery($query);
        $result = $db->loadObject();
        
        $output['success'] = true;
        $output['data'] = array(
            'total_aulas' => (int)$result->total_aulas,
            'aulas_completas' => (int)$result->aulas_completas,
            'porcentagem' => (int)$result->porcentagem
        );
        
    } catch (Exception $e) {
        $output['success'] = false;
        $output['message'] = 'Erro: ' . $e->getMessage();
    }
    
    echo json_encode($output);
    die();
}	
}