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
// GUIDEWAY CUSTOM - 2025-11-19 - Joshua - Atualizado para usar Model
    /**
     * Retorna o progresso do aluno em um curso
     * GUIDEWAY CUSTOM - 2025-11-19 - Joshua - Integrado com Model da Vitória
     */
    public function getCourseProgress() {
        $user = Factory::getUser();
        $input = Factory::getApplication()->input;
        $output = array();

        if (!$user->id) {
            $output['success'] = false;
            $output['message'] = 'Usuario nao autenticado';
            echo json_encode($output);
            die();
        }

        $courseId = $input->getInt('course_id', 0);

        if (!$courseId) {
            $output['success'] = false;
            $output['message'] = 'ID do curso nao fornecido';
            echo json_encode($output);
            die();
        }

        try {
            // CHAMA A FUNÇÃO DO MODEL (da Vitória)
            $model = $this->getModel('Course');
           // $result = $model->getCourseProgress($user->id, $courseId);alterei
	   $result = $model->getCourseProgress($courseId, $user->id);

            $output['success'] = true;
            $output['data'] = $result;

        } catch (Exception $e) {
            $output['success'] = false;
            $output['message'] = 'Erro: ' . $e->getMessage();
        }

        echo json_encode($output);
        die();
    }
}
