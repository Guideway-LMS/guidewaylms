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
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\MVC\Controller\FormController;

jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');

class SplmsControllerLesson extends FormController {

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
		if ($user->authorise('core.edit', 'com_splms.lesson.' . $recordId)) {
			return true;
		}
		// Check edit own on the record asset (explicit or inherited)
		if ($user->authorise('core.edit.own', 'com_splms.lesson.' . $recordId)){
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

	 // Delete File
    public function delete_media() {

        $model      = $this->getModel();
        $input      = Factory::getApplication()->input;
        $filePath   = $input->post->get('filePath', NULL, 'STRING');
        $itemID     = $input->post->get('itemId', NULL, 'INT');

        $report = array();
        $report['status'] = false;

        $report['itemID'] = $itemID;

        if(isset($filePath) && $filePath) {
            $report['delete'] = $model->removeAttachmentByID($itemID);
            if(File::exists($filePath)) {
                // Delete thumb
                if (File::delete($filePath)) {
                    $report['status']   = true;
                    $report['message']  = Text::_('SPLMS_ATTACHMENT_SUCCESSFULLY_REMOVED');
                }

            } else {
                $report['status'] = false;
                $report['message']  = Text::_('SPLMS_ATTACHMENT_ISNOT_EXIST');
            }
        } else {
            $report['status'] = false;
            $report['message']  = Text::_('SPLMS_NO_ATTACHMENT_FOUND');
        }

        echo json_encode($report);
        die;
    }

	/**
	 * Endpoint AJAX para processamento de texto via IA
	 * 
	 * Recebe texto e ação do admin, valida CSRF token,
	 * e retorna resposta processada pelo GuidewayAIHelper.
	 * 
	 * @return void Outputs JSON response
	 * @since  1.0.0
	 */
	public function callAI()
	{
		// Force JSON response header
		header('Content-Type: application/json');
		$output = ['success' => false, 'message' => ''];

		try {
			// 1. Verificação de segurança CSRF
			if (!Session::checkToken('post')) {
				throw new Exception(Text::_('JINVALID_TOKEN'));
			}

			// 2. Verificação de usuário logado e com permissão de edição
			$user = Factory::getUser();
			if (!$user->id) {
				throw new Exception('Usuário não autenticado.');
			}

			// 3. Obter parâmetros do request
			$input = Factory::getApplication()->input;
			$texto = $input->post->get('texto', '', 'RAW');
			$acao  = $input->post->get('acao', '', 'STRING');

			// Validação básica
			if (empty($texto) || empty($acao)) {
				throw new Exception('Parâmetros inválidos: texto e ação são obrigatórios.');
			}

			// 4. Carregar e chamar o Helper de IA (do componente site)
			$helperPath = JPATH_ROOT . '/components/com_splms/helpers/GuidewayAIHelper.php';
			if (!file_exists($helperPath)) {
				throw new Exception('Helper não encontrado em: ' . $helperPath);
			}
			require_once $helperPath;

			if (!class_exists('GuidewayAIHelper')) {
				throw new Exception('Classe GuidewayAIHelper não encontrada.');
			}

			// Mapeamento de Ação -> Permissão
			$permMap = [
				GuidewayAIHelper::ACTION_CUSTOM     => 'ai.generate',
				GuidewayAIHelper::ACTION_FORMATAR   => 'ai.generate',
				GuidewayAIHelper::ACTION_REVISAR    => 'ai.refine',
				GuidewayAIHelper::ACTION_RESUMIR    => 'ai.refine',
				GuidewayAIHelper::ACTION_REESCREVER => 'ai.refine',
			];

			$requiredPerm = isset($permMap[$acao]) ? $permMap[$acao] : null;

			// Se a ação não estiver mapeada, nega por padrão ou trata como erro
			if (!$requiredPerm) {
				throw new Exception('Ação não reconhecida ou sem permissão definida.');
			}

			// Verificação de ACL
			if (!$user->authorise($requiredPerm, 'com_splms')) {
				http_response_code(403);
				throw new Exception(Text::_('JERROR_ALERTNOAUTHOR') . ' (' . $requiredPerm . ')');
			}

			$result = GuidewayAIHelper::processarTexto($texto, $acao);

			// 5. Retornar resposta JSON
			echo json_encode($result);
			die();

		} catch (\Throwable $e) {
			// Catch ALL errors (including parse errors/fatal errors if possible)
			$output['message'] = 'Erro no servidor: ' . $e->getMessage();
			$output['debug_file'] = $e->getFile();
			$output['debug_line'] = $e->getLine();
			http_response_code(500); // Still 500 but with content
			echo json_encode($output);
			die();
		}
	}

	/**
	 * Endpoint de upload de PDF para extração de texto
	 */
	public function uploadPDF()
	{
		header('Content-Type: application/json');
		$result = ['success' => false, 'message' => 'Erro desconhecido', 'data' => ''];

		try {
			// 1. Segurança CSRF e Sessão
			if (!Session::checkToken('post')) {
				throw new Exception(Text::_('JINVALID_TOKEN'));
			}

			$user = Factory::getUser();
			if (!$user->id) {
				throw new Exception('Faça login para realizar esta ação.');
			}

			// Verificação de Permissão de IA (Generate) para Upload/Formatação
			if (!$user->authorise('ai.generate', 'com_splms')) {
				http_response_code(403);
				throw new Exception(Text::_('JERROR_ALERTNOAUTHOR') . ' (ai.generate)');
			}

			// 2. Validação do Arquivo
			if (!isset($_FILES['gw_ai_file']) || $_FILES['gw_ai_file']['error'] != UPLOAD_ERR_OK) {
				throw new Exception('Nenhum arquivo enviado ou erro no upload. Código: ' . ($_FILES['gw_ai_file']['error'] ?? 'N/A'));
			}

			$file = $_FILES['gw_ai_file'];
			$maxSize = 5 * 1024 * 1024; // 5MB

			// Validação Tamanho
			if ($file['size'] > $maxSize) {
				throw new Exception('O arquivo excede o tamanho máximo de 5MB.');
			}

			// Validação Extensão
			$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
			if ($ext !== 'pdf') {
				throw new Exception('Apenas arquivos .pdf são permitidos.');
			}

			// Validação MIME Type (mais confiável)
			$finfo = finfo_open(FILEINFO_MIME_TYPE);
			$mime = finfo_file($finfo, $file['tmp_name']);
			finfo_close($finfo);

			if ($mime !== 'application/pdf') {
				throw new Exception('O arquivo enviado não parece ser um PDF válido.');
			}

			// 3. Processamento e Extração com Smalot\PdfParser
			
			// Tenta carregar o autoloader local do componente, caso não esteja no global
			$autoloadPath = JPATH_ROOT . '/components/com_splms/assets/vendor/autoload.php';
			if (file_exists($autoloadPath)) {
				require_once $autoloadPath;
			} 
			// Se não achar local, assume que o global já carregou ou vai falhar class not found
			
			if (!class_exists('Smalot\PdfParser\Parser')) {
				throw new Exception('Biblioteca de PDF Parser não encontrada. Instale "smalot/pdfparser".');
			}

			$parser = new \Smalot\PdfParser\Parser();
			
			try {
				$pdf = $parser->parseFile($file['tmp_name']);
				$text = $pdf->getText();
			} catch (\Exception $e) {
				throw new Exception('Não foi possível ler o conteúdo do PDF. O arquivo pode estar corrompido ou protegido.');
			}

			// 4. Tratamento de Encoding (UTF-8) e Limpeza
			// Remover caracteres nulos e de controle que podem quebrar o JSON
			$text = preg_replace('/[\x00-\x09\x0B\x0C\x0E-\x1F\x7F]/', '', $text);
			
			// Converter para UTF-8 se não estiver
			if (!mb_check_encoding($text, 'UTF-8')) {
				$text = mb_convert_encoding($text, 'UTF-8', 'auto');
			}

			$input = Factory::getApplication()->input;
			$prompt = $input->post->get('gw_ai_prompt', '', 'RAW');
			$difficulty = $input->post->get('gw_ai_difficulty', '', 'STRING');
			$qcount = $input->post->get('gw_ai_qcount', 5, 'INT');
			$qtype = $input->post->get('gw_ai_qtype', 'optativa', 'STRING');
			
			// Carrega helper se necessário
			if (!class_exists('GuidewayAIHelper')) {
				$helperPath = JPATH_ROOT . '/components/com_splms/helpers/GuidewayAIHelper.php';
				if (file_exists($helperPath)) require_once $helperPath;
			}
			
			if (class_exists('GuidewayAIHelper')) {
				
				if (!empty($difficulty)) {
					// --- Fluxo de Geração de Questões ---
					
					// Validação Allowlist de Dificuldade
					$allowedDifficulties = ['facil', 'medio', 'dificil'];
					if (!in_array($difficulty, $allowedDifficulties)) {
						throw new Exception('Dificuldade inválida. Permitido: facil, medio, dificil.');
					}

					// Validação Quantidade (1 a 20)
					if ($qcount < 1 || $qcount > 20) {
						throw new Exception('Quantidade de questões deve ser entre 1 e 20.');
					}

					// Validação Tipo
					$allowedTypes = ['optativa', 'dissertativa'];
					if (!in_array($qtype, $allowedTypes)) {
						$qtype = 'optativa'; // Fallback seguro
					}

					// Prepara params para o Helper
					$params = json_encode([
						'difficulty' => $difficulty,
						'count' => $qcount,
						'type' => $qtype
					]);

					$aiResult = GuidewayAIHelper::processarTexto($text, GuidewayAIHelper::ACTION_CRIAR_QUESTOES, $params);
					$msgPrefix = 'Questões geradas com sucesso!';

				} elseif (!empty($prompt)) {
					// --- Fluxo Customizado (Prompt do Usuário) ---
					$aiResult = GuidewayAIHelper::processarTexto($text, GuidewayAIHelper::ACTION_CUSTOM, $prompt);
					$msgPrefix = 'Texto processado com sua instrução!';
				} else {
					// --- Fluxo Padrão (Formatação) ---
					$aiResult = GuidewayAIHelper::processarTexto($text, GuidewayAIHelper::ACTION_FORMATAR);
					$msgPrefix = 'Texto formatado com sucesso!';
				}
				
				if ($aiResult['success']) {
					$text = $aiResult['data'];
					$msg = $msgPrefix;
				} else {
					// Se falhar a IA, mantém o texto bruto mas avisa
					$msg = 'Texto extraído (bruto), mas houve erro na IA: ' . $aiResult['message'];
				}
			} else {
				$msg = 'Texto extraído (bruto). Helper de IA não disponível.';
			}

			$result['success'] = true;
			$result['data'] = $text; 
			$result['message'] = $msg;

		} catch (\Exception $e) {
			$result['success'] = false;
			$result['message'] = $e->getMessage();
		}

		echo json_encode($result);
		die();
	}

}
