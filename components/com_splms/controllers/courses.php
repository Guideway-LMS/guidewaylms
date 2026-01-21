<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
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
public function getCourseProgress()
{
    $app   = Factory::getApplication();
    $input = $app->input;
    $user  = Factory::getUser();

    // 🔥 FORÇA JSON + RAW
    $app->setHeader('Content-Type', 'application/json', true);

    if (!$user->id) {
        echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
        $app->close();
    }

    $courseId = $input->getInt('course_id', 0);
    if (!$courseId) {
        echo json_encode(['success' => false, 'message' => 'ID do curso não fornecido']);
        $app->close();
    }

    try {
        $model = $this->getModel('Course', 'SplmsModel');
        if ($model === false) {
            throw new Exception('Model Course não encontrado');
        }

        // Legacy safety
        $model->setState('course.id', (int) $courseId);
        $model->setState('user.id', (int) $user->id);

        $progress = $model->getCourseProgress($courseId, $user->id);

        echo json_encode([
            'success'  => true,
            'progress' => (float) $progress
        ]);

    } catch (Throwable $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }

    // 🔥 MATA O JOOMLA ANTES DO TEMPLATE
    $app->close();
}   
public function getCourseProgressTest()
{
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    $input = \Joomla\CMS\Factory::getApplication()->input;
    $output = [];

    // Forçar user_id manualmente
    $userId = 3; // usuário que você sabe que existe
    $courseId = $input->getInt('course_id', 0);

    if (!$courseId) {
        echo json_encode(['success' => false, 'message' => 'ID do curso não fornecido']);
        jexit();
    }

    try {
        $model = $this->getModel('Course', 'SplmsModel');
        if ($model === false) {
            throw new Exception('Model Course não encontrado');
        }

        // Passando user_id fixo
        $progress = $model->getCourseProgress($courseId, $userId);

        echo json_encode([
            'success' => true,
            'user_id' => $userId,
            'course_id' => $courseId,
            'progress' => $progress
        ]);

    } catch (\Throwable $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]);
    }

    jexit();
}
}