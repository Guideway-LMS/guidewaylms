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

class SplmsControllerQuizquestion extends FormController {

	public function __construct($config = array()) {
		parent::__construct($config);
	}

	protected function allowAdd($data = array()) {
		return parent::allowAdd($data);
	}

	protected function allowEdit($data = array(), $key = 'id') {
		$recordId = (int) isset($data[$key]) ? $data[$key] : 0;
		$user = Factory::getUser();
		// Zero record (id:0), return component edit permission by calling parent controller method
		if (!$recordId) {
			return parent::allowEdit($data, $key);
		}
		// Check edit on the record asset (explicit or inherited)
		if ($user->authorise('core.edit', 'com_splms.quizquestion.' . $recordId)) {
			return true;
		}
		// Check edit own on the record asset (explicit or inherited)
		if ($user->authorise('core.edit.own', 'com_splms.quizquestion.' . $recordId)){
			// Existing record already has an owner, get it
			$record = $this->getModel()->getItem($recordId);
			if (empty($record)) {
				return false;
			}
			// Grant if current user is owner of the record
			return $user->id == $record->created_by;
		} 
	}

	public function generateAI()
	{
		// Verifica falsificações de requisição
		if (!Factory::getSession()->checkToken())
		{
			echo new \Joomla\CMS\Response\JsonResponse(null, 'Invalid Token', true);
			Factory::getApplication()->close();
		}

		$input = Factory::getApplication()->input;
		$text  = $input->get('text', '', 'raw');
		
		// Load Helper
    require_once JPATH_SITE . '/components/com_splms/helpers/GuidewayAIHelper.php';

    $pdfText = ''; // Initialize pdfText

    // PDF Processing Check
    if (isset($_FILES['gw_ai_file']) && $_FILES['gw_ai_file']['error'] == UPLOAD_ERR_OK) {
        $file = $_FILES['gw_ai_file'];
        // Validate PDF
        if (strtolower(pathinfo($file['name'], PATHINFO_EXTENSION)) === 'pdf' && $file['size'] <= 5 * 1024 * 1024) {
             // Load Parser
             $autoloadPath = JPATH_ROOT . '/components/com_splms/assets/vendor/autoload.php';
             if (file_exists($autoloadPath)) require_once $autoloadPath;
             
             if (class_exists('Smalot\PdfParser\Parser')) {
                 try {
                     $parser = new \Smalot\PdfParser\Parser();
                     $pdf = $parser->parseFile($file['tmp_name']);
                     $pdfText = $pdf->getText();
                     
                     // Clean text
                     $pdfText = preg_replace('/[\x00-\x09\x0B\x0C\x0E-\x1F\x7F]/', '', $pdfText);
                     
                     $text .= "\n\n--- Conteúdo do PDF Anexo ---\n" . $pdfText;
                 } catch (Exception $e) {
                     // Log error but proceed if there is text? Or fail? 
                     // Failing seems safer to avoid partial context.
                     echo new \Joomla\CMS\Response\JsonResponse(null, 'Erro ao ler PDF: ' . $e->getMessage(), true);
                     Factory::getApplication()->close();
                 }
             }
        }
    }

    if (empty($text) && empty($pdfText)) {
        echo new \Joomla\CMS\Response\JsonResponse(null, 'Texto ou PDF vazio', true);
        Factory::getApplication()->close();
    }

    // Configuration
    $difficulty = $input->get('difficulty', 'medio', 'string');
    $count      = $input->get('count', 5, 'int');

    $config = [
        'difficulty' => $difficulty,
        'count'      => $count,
        'type'       => 'optativa'
    ];

    $result = GuidewayAIHelper::processarTexto($text, GuidewayAIHelper::ACTION_CRIAR_QUESTOES, json_encode($config));

		if ($result['success'])
		{
			// Limpa possíveis blocos markdown se a IA os adicionou apesar das instruções
			$cleanData = $result['data'];
			$cleanData = str_replace(['```json', '```'], '', $cleanData);
			
			$data = json_decode($cleanData);
			
			if (json_last_error() === JSON_ERROR_NONE)
			{
				echo new \Joomla\CMS\Response\JsonResponse($data);
			}
			else
			{
				echo new \Joomla\CMS\Response\JsonResponse(null, 'Erro ao decodificar JSON da IA: ' . json_last_error_msg(), true);
			}
		}
		else
		{
			echo new \Joomla\CMS\Response\JsonResponse(null, $result['message'], true);
		}

		Factory::getApplication()->close();
	} // Fim generateAI


}
