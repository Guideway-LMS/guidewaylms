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



		if (empty($topic)) {
			echo json_encode(['success' => false, 'message' => 'Tópico é obrigatório.']);
			$app->close();
		}

		// Load Helper
		require_once JPATH_COMPONENT_ADMINISTRATOR . '/helpers/ai.php';

		try {
			$structure = SplmsHelperAI::generateStructure($topic, $audience, $objectives, $language);

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



	public function downloadPdf()
	{
		$app = Factory::getApplication();
		$input = $app->input;
		$user = Factory::getUser();

		if (!$user->authorise('ai.architect', 'com_splms')) {
			http_response_code(403);
			echo 'Permissão negada.';
			$app->close();
		}

		$structureJson = $input->get('structure', '', 'raw');
		$topic = $input->getString('topic', 'Curso');
		$audience = $input->getString('audience', '');
		$objectives = $input->getString('objectives', '');

		$structure = json_decode($structureJson, true);

		if (!$structure || !isset($structure['sections'])) {
			http_response_code(400);
			echo 'Dados da estrutura inválidos.';
			$app->close();
		}

		// Load TCPDF
		$tcpdfPath = JPATH_SITE . '/components/com_splms/libraries/tcpdf/TCPDF-main/tcpdf.php';
		if (!file_exists($tcpdfPath)) {
			http_response_code(500);
			echo 'Biblioteca TCPDF não encontrada.';
			$app->close();
		}
		require_once $tcpdfPath;

		// Create PDF
		$pdf = new \TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

		$pdf->SetCreator('Guideway LMS');
		$pdf->SetAuthor($user->name);
		$pdf->SetTitle('Proposta de Curso: ' . $topic);
		$pdf->SetSubject('Estrutura Curricular');

		$pdf->setPrintHeader(false);
		$pdf->setPrintFooter(true);
		$pdf->setFooterData(array(0, 0, 0), array(200, 200, 200));

		$pdf->SetDefaultMonospacedFont('courier');
		$pdf->SetMargins(15, 15, 15);
		$pdf->SetAutoPageBreak(true, 20);
		$pdf->SetFont('helvetica', '', 11);

		$pdf->AddPage();

		// Build HTML content
		$html = '<style>
			h1 { color: #1e293b; font-size: 22pt; border-bottom: 2px solid #3b82f6; padding-bottom: 8px; margin-bottom: 15px; }
			h2 { color: #3b82f6; font-size: 14pt; margin-top: 20px; margin-bottom: 8px; }
			h3 { color: #334155; font-size: 12pt; margin-top: 10px; }
			.meta { color: #64748b; font-size: 10pt; margin-bottom: 15px; }
			.meta-label { font-weight: bold; color: #334155; }
			.section-desc { color: #475569; font-size: 10pt; margin-bottom: 10px; padding: 5px 0; border-bottom: 1px solid #e2e8f0; }
			.lesson-block { margin-left: 15px; margin-bottom: 10px; padding-left: 10px; border-left: 3px solid #3b82f6; }
			.lesson-title { font-weight: bold; color: #1e293b; font-size: 11pt; }
			.lesson-duration { color: #64748b; font-size: 9pt; }
			.lesson-desc { color: #475569; font-size: 10pt; }
			.divider { border-bottom: 1px solid #e2e8f0; margin: 15px 0; }
			.footer-note { color: #94a3b8; font-size: 8pt; text-align: center; margin-top: 30px; }
		</style>';

		$html .= '<h1>Proposta de Curso: ' . htmlspecialchars($topic) . '</h1>';

		if (!empty($audience) || !empty($objectives)) {
			$html .= '<div class="meta">';
			if (!empty($audience)) {
				$html .= '<p><span class="meta-label">Público-Alvo:</span> ' . htmlspecialchars($audience) . '</p>';
			}
			if (!empty($objectives)) {
				$html .= '<p><span class="meta-label">Objetivos de Aprendizado:</span> ' . htmlspecialchars($objectives) . '</p>';
			}
			$html .= '</div>';
		}

		$html .= '<div class="divider"></div>';

		foreach ($structure['sections'] as $i => $section) {
			$html .= '<h2>Módulo ' . ($i + 1) . ': ' . htmlspecialchars($section['title']) . '</h2>';

			if (!empty($section['description'])) {
				$html .= '<div class="section-desc">' . htmlspecialchars($section['description']) . '</div>';
			}

			if (isset($section['lessons']) && is_array($section['lessons'])) {
				foreach ($section['lessons'] as $j => $lesson) {
					$html .= '<div class="lesson-block">';
					$html .= '<div class="lesson-title">Aula ' . ($j + 1) . ': ' . htmlspecialchars($lesson['title']) . '</div>';
					if (!empty($lesson['duration'])) {
						$html .= '<div class="lesson-duration">Duração: ' . htmlspecialchars($lesson['duration']) . '</div>';
					}
					if (!empty($lesson['description'])) {
						$html .= '<div class="lesson-desc">' . htmlspecialchars($lesson['description']) . '</div>';
					}
					$html .= '</div>';
				}
			}

			if ($i < count($structure['sections']) - 1) {
				$html .= '<div class="divider"></div>';
			}
		}

		$html .= '<div class="footer-note">Documento gerado automaticamente pelo Arquiteto de Cursos IA — Guideway LMS</div>';

		$pdf->writeHTML($html, true, false, true, false, '');

		$filename = 'Proposta_Curso_' . preg_replace('/[^a-z0-9]/i', '_', $topic) . '.pdf';

		// Limpa todos os output buffers do Joomla antes de enviar o PDF
		// TCPDF::Output('D') verifica ob_get_contents() e falha se existir conteúdo bufferizado
		while (ob_get_level()) {
			ob_end_clean();
		}

		$pdf->Output($filename, 'D');
		$app->close();
	}
}

