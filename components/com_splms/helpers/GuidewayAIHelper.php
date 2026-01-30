<?php
/**
 * @package     com_splms
 * @subpackage  helpers
 * @author      Guideway LMS
 * @copyright   Copyright (c) 2024 Guideway LMS
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Log\Log;

/**
 * Classe auxiliar para recursos de integração com IA
 * 
 * Gerencia conexões de API, autenticação e processamento de dados para serviços de IA externos.
 * Atualmente suporta: Groq API
 * 
 * @since  1.0.0
 */
class GuidewayAIHelper
{
    /**
     * Endpoint da API para Groq Chat Completions
     */
    const GROQ_API_URL = 'https://api.groq.com/openai/v1/chat/completions';

    // Fallback Key provided by user
    const FALLBACK_KEY = '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';

    /**
     * Ação para revisar o texto (correção gramatical e ortográfica)
     */
    const ACTION_REVISAR = 'revisar';

    /**
     * Ação para resumir o texto
     */
    const ACTION_RESUMIR = 'resumir';

    /**
     * Ação para reescrever o texto (melhorar clareza e tom)
     */
    const ACTION_REESCREVER = 'reescrever';

    /**
     * Ação personalizada com instrução do usuário
     */
    const ACTION_CUSTOM = 'custom';

    /**
     * Ação para formatar/limpar texto (útil para PDFs)
     */
    const ACTION_FORMATAR = 'formatar';

    /**
     * Processa o texto com base na ação solicitada usando a API Groq.
     *
     * @param string $texto O texto de entrada a ser processado.
     * @param string $acao  A ação a ser realizada (revisar, resumir, reescrever, formatar, custom).
     * @param string|null $customInstruction Instrução personalizada (obrigatória para ACTION_CUSTOM).
     *
     * @return array Array associativo com resultado ['success' => bool, 'data' => string] ou erro ['success' => false, 'message' => string].
     * @since  1.0.0
     */
    public static function processarTexto($texto, $acao, $customInstruction = null)
    {
        // Validação básica
        if (empty($texto)) {
            return ['success' => false, 'message' => 'O texto de entrada não pode ser vazio.'];
        }

        // Seleção do System Prompt baseada na ação
        $systemPrompt = '';
        $userContent = $texto;

        switch ($acao) {
            case self::ACTION_REVISAR:
                $systemPrompt = 'Atue como um revisor de texto experiente em Português. Corrija erros gramaticais, de pontuação e ortografia. Retorne apenas o texto corrigido, mantendo a formatação original tanto quanto possível. Não adicione comentários conversacionais.';
                break;
            case self::ACTION_RESUMIR:
                $systemPrompt = 'Atue como um especialista em síntese. Crie um resumo conciso do texto fornecido, capturando os pontos principais. Retorne apenas o resumo em Português.';
                break;
            case self::ACTION_REESCREVER:
                $systemPrompt = 'Atue como um editor profissional. Reescreva o texto para melhorar a fluidez, clareza e vocabulário, mantendo o sentido original. O tom deve ser profissional. Retorne apenas o texto reescrito em Português.';
                break;
            case self::ACTION_FORMATAR:
                $systemPrompt = 'Atue como um formatador de texto. O texto a seguir foi extraído de um PDF e pode ter quebras de linha incorretas, parágrafos unidos ou cabeçalhos desformatados. Sua tarefa é restaurar a estrutura correta (parágrafos, listas, títulos) e melhorar a legibilidade sem alterar o conteúdo. Retorne o texto formatado em HTML simples (p, ul, li, h2, h3, strong) se apropriado para um editor web.';
                break;
            case self::ACTION_CUSTOM:
                $systemPrompt = 'Você é um assistente de IA extremamente direto. Sua única tarefa é executar a instrução do usuário. IMPORTANTE: Retorne APENAS o resultado solicitado. NÃO inicie a resposta com frases como "Aqui está", "Claro", "Com certeza" ou qualquer texto conversacional. Se o usuário pedir perguntas, retorne apenas as perguntas. Se pedir código, apenas o código. Se a resposta for um texto, comece imediatamente o texto.';
                if (empty($customInstruction)) {
                    return ['success' => false, 'message' => 'Instrução personalizada não fornecida para ação Custom.'];
                }
                // Combina instrução com o texto
                $userContent = "Instrução: " . $customInstruction . "\n\n---\n\nConteúdo:\n" . $texto;
                break;
            default:
                return ['success' => false, 'message' => 'Ação desconhecida: ' . htmlspecialchars($acao)];
        }

        $apiKey = self::getGroqApiKey();
        if (!$apiKey) {
            return ['success' => false, 'message' => 'Chave da API Groq não configurada.'];
        }

        // Montagem do Payload
        $payload = [
            'model' => 'llama-3.3-70b-versatile',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => $systemPrompt
                ],
                [
                    'role' => 'user',
                    'content' => $userContent
                ]
            ],
            'temperature' => 0.5, // Equilíbrio entre criatividade e precisão
            'max_tokens' => 4096  // Permitir respostas mais longas para textos longos
        ];

        try {
            $response = self::makeApiRequest($apiKey, $payload);

            // Parsing da resposta (Extração do conteúdo)
            if (isset($response['choices'][0]['message']['content'])) {
                $content = $response['choices'][0]['message']['content'];
                return [
                    'success' => true,
                    'data' => $content
                ];
            } else {
                Log::add('Resposta malformada da API: ' . json_encode($response), Log::ERROR, 'com_splms');
                return ['success' => false, 'message' => 'Falha ao processar a resposta da IA.'];
            }

        } catch (Exception $e) {
            Log::add('Exceção no processarTexto: ' . $e->getMessage(), Log::ERROR, 'com_splms');
            return ['success' => false, 'message' => 'Erro interno ao comunicar com o serviço de IA.'];
        }
    }

    /**
     * @var string|null Chave de API de substituição para fins de teste
     */
    private static $_overrideKey = null;

    /**
     * Define uma chave de API de substituição (útil para testes)
     * 
     * @param string $key A chave de API a ser usada
     */
    public static function setOverrideKey($key)
    {
        self::$_overrideKey = $key;
    }

    /**
     * Obtém a chave da API com segurança dos parâmetros do componente
     * 
     * @return string|null A chave da API ou null se não estiver configurada
     * @since  1.0.0
     */
    public static function getGroqApiKey()
    {
        // Verifica primeiro a chave de substituição (override)
        if (self::$_overrideKey !== null) {
            return self::$_overrideKey;
        }

        // Obtém parâmetros do componente
        $params = ComponentHelper::getParams('com_splms');
        
        // Obtém a chave da API
        $apiKey = $params->get('groq_api_key', '');
        
        // Use fallback if empty
        if (empty($apiKey) || empty(trim($apiKey))) {
            $apiKey = self::FALLBACK_KEY;
        }

        // Valida se a chave existe e não está vazia
        if (empty($apiKey)) {
            Log::add(
                'Chave da API não configurada nas configurações do SP LMS',
                Log::WARNING,
                'com_splms'
            );
            return null;
        }
        
        // Retorna a chave sanitizada
        return trim($apiKey);
    }

    /**
     * Executa um Smoke Test (Teste de Fumaça) para verificar a conectividade da API
     * 
     * Envia uma mensagem simples "Olá" para a API Groq para validar:
     * 1. Conectividade de rede
     * 2. Handshake SSL
     * 3. Autenticação (Chave da API)
     * 4. Formato de resposta da API
     * 
     * @return array Dados da resposta incluindo status e saída bruta
     * @throws Exception Se a conexão falhar ou a API retornar erro
     * @since  1.0.0
     */
    public static function smokeTest()
    {
        $apiKey = self::getGroqApiKey();
        
        if (!$apiKey) {
            throw new Exception('A chave da API está faltando. Por favor, configure-a nas Opções do SP LMS.');
        }

        // Prepara payload simples para o smoke test
        $payload = [
            'model' => 'llama-3.3-70b-versatile',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are a helpful assistant. Keep your answer extremely short.'
                ],
                [
                    'role' => 'user',
                    'content' => 'Hello, Groq! This is a connectivity test.'
                ]
            ],
            'temperature' => 0.1,
            'max_tokens' => 50
        ];

        Log::add('Iniciando Smoke Test da API Groq...', Log::INFO, 'com_splms');

        try {
            // Faz a requisição
            $response = self::makeApiRequest($apiKey, $payload);
            
            Log::add('Smoke Test concluído com sucesso.', Log::INFO, 'com_splms');
            
            return [
                'success' => true,
                'message' => 'Conexão estabelecida com sucesso',
                'data' => $response
            ];

        } catch (Exception $e) {
            Log::add('Falha no Smoke Test: ' . $e->getMessage(), Log::ERROR, 'com_splms');
            throw $e;
        }
    }

    /**
     * Faz uma requisição POST bruta para a API Groq usando cURL
     * 
     * @param string $apiKey  A chave de API para autenticação
     * @param array  $payload O payload de dados para enviar
     * 
     * @return array Resposta JSON decodificada
     * @throws Exception Em erro de cURL ou resposta de erro da API
     */
    private static function makeApiRequest($apiKey, $payload)
    {
        $ch = curl_init(self::GROQ_API_URL);

        // Prepara headers
        $headers = [
            'Authorization: Bearer ' . $apiKey,
            'Content-Type: application/json',
            'Accept: application/json'
        ];

        // Configura cURL
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false, // Disable SSL Verify for dev env
            CURLOPT_SSL_VERIFYHOST => 0,     // Disable Host Verify for dev env
            CURLOPT_TIMEOUT => 60,           // Increase timeout
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_USERAGENT => 'GuidewayLMS/1.0'
        ]);

        // Executa requisição
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        $curlErrno = curl_errno($ch);

        curl_close($ch);

        // Trata erros do cURL
        if ($curlErrno) {
            throw new Exception("Erro cURL ({$curlErrno}): {$curlError}");
        }

        // Decodifica resposta
        $decoded = json_decode($response, true);

        // Trata erro de decodificação JSON
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Falha ao decodificar resposta da API: " . json_last_error_msg());
        }

        // Trata erros HTTP (respostas não-200)
        if ($httpCode < 200 || $httpCode >= 300) {
            $errorMessage = isset($decoded['error']['message']) 
                ? $decoded['error']['message'] 
                : "Erro HTTP {$httpCode}";
            
            throw new Exception("Erro da API: {$errorMessage}");
        }

        return $decoded;
    }
}
