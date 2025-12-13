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
     * Obtém a chave da API Groq com segurança dos parâmetros do componente
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
        
        // Valida se a chave existe e não está vazia
        if (empty($apiKey)) {
            Log::add(
                'Chave da API Groq não configurada nas configurações do SP LMS',
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
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_TIMEOUT => 30,
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
