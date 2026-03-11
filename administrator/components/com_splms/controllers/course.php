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

class SplmsControllerCourse extends FormController {

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
		if ($user->authorise('core.edit', 'com_splms.course.' . $recordId)) {
			return true;
		}
		// Check edit own on the record asset (explicit or inherited)
		if ($user->authorise('core.edit.own', 'com_splms.course.' . $recordId)){
			// Existing record already has an owner, get it
			$record = $this->getModel()->getItem($recordId);
			if (empty($record)) {
				return false;
			}
			// Grant if current user is owner of the record
			return $user->id == $record->created_by;
		} 
		return false;
	}

	public function generateAiStructure()
	{
		// Security Check
		$app = Factory::getApplication();
		$input = $app->input;
		$user = Factory::getUser();

		if (!$user->authorise('ai.architect', 'com_splms')) {
			echo json_encode(['success' => false, 'message' => 'Permissão negada (AI Architect Not Allowed).']);
			$app->close();
		}

		$topic = $input->getString('topic');
		$audience = $input->getString('audience');
		$objectives = $input->getString('objectives');
		$language = $input->getString('language', 'Portuguese');
		// Add context (usar raw para evitar que o filtro string nativo corrompa o payload de legendas gigantescas)
		$context = $input->get('context', '', 'raw');

		if (empty($topic)) {
			echo json_encode(['success' => false, 'message' => 'Tópico é obrigatório.']);
			$app->close();
		}

		// Load Helper
		require_once JPATH_COMPONENT_ADMINISTRATOR . '/helpers/ai.php';

		try {
			$structure = SplmsHelperAI::generateStructure($topic, $audience, $objectives, $language, $context);

			if (is_array($structure)) {
				echo json_encode(['success' => true, 'data' => $structure]);
			} else {
	            // fallback
				echo json_encode(['success' => false, 'message' => is_string($structure) ? $structure : 'Falha indefinida no parser da IA.']);
			}
		} catch (Exception $e) {
			// Captura exceções como Timeouts de API ou Excesso de Tokens do Gemini
			echo json_encode(['success' => false, 'message' => $e->getMessage()]);
		}

		$app->close();
	}

	public function saveAiStructure()
	{
		$app = Factory::getApplication();
		$input = $app->input;
		$user = Factory::getUser();
		$db = Factory::getDbo();

		if (!$user->authorise('core.create', 'com_splms') && !$user->authorise('ai.architect', 'com_splms')) {
			echo json_encode(['success' => false, 'message' => 'Permissão negada.']);
			$app->close();
		}

		$courseId = $input->getInt('id');
		$structureJson = $input->get('structure', '', 'raw');
		$structure = json_decode($structureJson, true);

		if (!$courseId || !$structure || !isset($structure['sections'])) {
			echo json_encode(['success' => false, 'message' => 'Dados inválidos.']);
			$app->close();
		}

		try {
			// Iterate Sections
			foreach ($structure['sections'] as $i => $section) {
				// Insert Topic (Section)
				$topic = new stdClass();
				$topic->course_id = $courseId;
				$topic->title = $section['title'];
				$topic->description = $section['description'] ?? '';
				$topic->published = 1;
				$topic->ordering = $i + 1;
				$topic->created = Factory::getDate()->toSql();
				$topic->created_by = $user->id;
				$topic->modified = Factory::getDate()->toSql();
				$topic->modified_by = $user->id;
				$topic->access = 1;
				$topic->language = '*';

				$db->insertObject('#__splms_lessiontopics', $topic);
				$topicId = $db->insertid();

				// Iterate Lessons
				if (isset($section['lessons']) && is_array($section['lessons'])) {
					foreach ($section['lessons'] as $k => $lesson) {
						$lObj = new stdClass();
						$lObj->course_id = $courseId;
						$lObj->topic_id = $topicId;
						$lObj->title = $lesson['title'];
						$lObj->description = $lesson['description'] ?? '';
						$lObj->video_duration = $lesson['duration'] ?? '';
						$lObj->teacher_id = $user->id; // Default to creator
						$lObj->published = 1;
						$lObj->ordering = $k + 1;
						$lObj->created = Factory::getDate()->toSql();
						$lObj->created_by = $user->id;
						$lObj->modified = Factory::getDate()->toSql();
						$lObj->modified_by = $user->id;
						$lObj->access = 1;
						$lObj->language = '*';
						$lObj->alias = \Joomla\CMS\Application\ApplicationHelper::stringURLSafe($lesson['title']);

						$db->insertObject('#__splms_lessons', $lObj);
					}
				}
			}

			echo json_encode(['success' => true]);

		} catch (Exception $e) {
			echo json_encode(['success' => false, 'message' => $e->getMessage()]);
		}

		$app->close();
	}

	public function extractYoutubeTranscript()
	{
		$app = Factory::getApplication();
		$input = $app->input;
		$user = Factory::getUser();

		if (!$user->authorise('ai.architect', 'com_splms')) {
			echo json_encode(['success' => false, 'message' => 'Permissão negada (AI Architect Not Allowed).']);
			$app->close();
		}

		$url = $input->getString('url');

		if (empty($url)) {
			echo json_encode(['success' => false, 'message' => 'URL do YouTube é obrigatória.']);
			$app->close();
		}

		require_once JPATH_COMPONENT_ADMINISTRATOR . '/helpers/ai.php';

		try {
			$transcript = SplmsHelperAi::extractYoutubeTranscript($url);
			echo json_encode(['success' => true, 'data' => $transcript]);
		} catch (Exception $e) {
			echo json_encode(['success' => false, 'message' => $e->getMessage()]);
		}

		$app->close();
	}
}

