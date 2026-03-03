<?php
/**
 * @package     com_splms
 * @subpackage  Helpers
 * @author      JoomShaper http://www.joomshaper.com
 * @copyright   Copyright (c) 2010 - 2024 JoomShaper
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Http\HttpFactory;

class SplmsHelperAi
{
    /**
     * Generate quiz content using AI
     *
     * @param   string  $topic       The topic of the quiz
     * @param   string  $difficulty  The difficulty level (Easy, Medium, Hard)
     * @param   int     $count       Number of questions
     *
     * @return  array   The generated quiz structure
     */
    public static function generateQuiz($topic, $difficulty, $count)
    {
        // Load the frontend helper
        $frontendHelperPath = JPATH_SITE . '/components/com_splms/helpers/GuidewayAIHelper.php';
        
        if (!file_exists($frontendHelperPath)) {
             throw new \Exception('GuidewayAIHelper not found at ' . $frontendHelperPath);
        }
        
        require_once $frontendHelperPath;
        
        if (!class_exists('GuidewayAIHelper')) {
            throw new \Exception('GuidewayAIHelper class not found.');
        }

        // prepare instructions
        $customInstruction = "Gere questões dissertativas sobre o conteúdo fornecido.
        Tópico: '{$topic}'
        Dificuldade: '{$difficulty}'
        Quantidade: {$count} perguntas.
        
        A saída deve ser EXATAMENTE em HTML, seguindo esta estrutura, sem adicionar crases (```html) ou textos fora do HTML:
        <div class=\"gw-quiz-container\">
            <h3>Questões para a Aula</h3>
            <div class=\"gw-quiz-question\">
                <h4>1. [Pergunta aqui]</h4>
                <br><br>
            </div>
            <!-- e assim por diante para a quantidade pedida -->
            <hr>
            <div class=\"gw-quiz-footer\">
                <h4>Gabarito Esperado</h4>
                <ul>
                    <li><strong>1)</strong> [Resposta esperada aqui]</li>
                    <!-- e assim por diante... -->
                </ul>
            </div>
        </div>

        O idioma deve ser Português do Brasil (pt-BR).";

        $contentToProcess = "Gerar questões sobre: " . $topic;

        // Call the helper
        $result = \GuidewayAIHelper::processarTexto($contentToProcess, 'custom', $customInstruction);

        if (!$result['success']) {
            throw new \Exception('Erro na IA: ' . $result['message']);
        }

        $content = $result['data'];
        
        // Clean up markdown code blocks if present (despite prompt)
        $content = preg_replace('/^```html\s*/', '', $content);
        $content = preg_replace('/^```\s*/', '', $content);
        $content = preg_replace('/\s*```$/', '', $content);

        return $content;
    } // End generateQuiz

    /**
     * Generate Course Structure using AI
     *
     * @param   string  $topic       The topic of the course
     * @param   string  $audience    Target audience
     * @param   string  $objectives  Learning objectives
     * @param   string  $language    Output language
     *
     * @return  array   The generated course structure
     */
    public static function generateStructure($topic, $audience, $objectives, $language)
    {
        // Load the frontend helper (if not already loaded, though generateQuiz check handles it mostly, strict check here too)
        $frontendHelperPath = JPATH_SITE . '/components/com_splms/helpers/GuidewayAIHelper.php';
        if (file_exists($frontendHelperPath)) {
            require_once $frontendHelperPath;
        }

        if (!class_exists('GuidewayAIHelper')) {
            throw new \Exception('GuidewayAIHelper class not found.');
        }

        $prompt = "Crie uma estrutura de curso online (Ementa) em formato JSON.
        Tópico: '{$topic}'
        Público-Alvo: '{$audience}'
        Objetivos de Aprendizado: '{$objectives}'
        Idioma de Saída: '{$language}'

        A estrutura JSON deve ser EXATAMENTE assim:
        {
            \"sections\": [
                {
                    \"title\": \"Título do Módulo\",
                    \"description\": \"Descrição do que será abordado no módulo.\",
                    \"lessons\": [
                        {
                            \"title\": \"Título da Aula\",
                            \"description\": \"Breve resumo do conteúdo da aula.\",
                            \"duration\": \"10 min\"
                        }
                    ]
                }
            ]
        }
        
        Gere pelo menos 3 a 5 módulos com 3 a 5 aulas cada.
        IMPORTANTE: Retorne APENAS o JSON. Não use blocos de código markdown.";

        $contentToProcess = "Gerar estrutura de curso sobre: " . $topic;

        // Call the helper
        $result = \GuidewayAIHelper::processarTexto($contentToProcess, 'custom', $prompt);

        if (!$result['success']) {
            throw new \Exception('Erro na IA: ' . $result['message']);
        }

        $content = $result['data'];
        
        // Clean up markdown code blocks if present
        $content = preg_replace('/^```json\s*/', '', $content);
        $content = preg_replace('/^```\s*/', '', $content);
        $content = preg_replace('/\s*```$/', '', $content);

        $structureData = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Falha ao processar JSON da Estrutura: ' . json_last_error_msg() . ' - Conteúdo: ' . $content);
        }

        return $structureData;
    }

}
