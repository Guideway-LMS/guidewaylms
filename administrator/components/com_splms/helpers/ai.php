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
    public static function generateStructure($topic, $audience, $objectives, $language, $context = '')
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
        if (!empty($context)) {
            // Truncate context to ~25,000 chars to avoid Groq model limits
            if (mb_strlen($context, 'UTF-8') > 25000) {
                $context = mb_substr($context, 0, 25000, 'UTF-8') . " ... [Transcrição truncada devido ao tamanho máximo da IA]";
            }
            $contentToProcess .= "\n\nBaseado na seguinte transcrição de vídeo (Use como contexto primário para o conteúdo das aulas):\n" . $context;
        }

        // Call the helper
        $result = \GuidewayAIHelper::processarTexto($contentToProcess, 'custom', $prompt);

        if (!$result['success']) {
            throw new \Exception('Erro na IA: ' . $result['message']);
        }

        $content = $result['data'];
        
        // Anti-Bug: Strip any rogue HTML that might have been wrapped by the response or the helper nl2br parser
        $content = html_entity_decode(strip_tags($content), ENT_QUOTES, 'UTF-8');

        // Clean up markdown code blocks if present
        $content = preg_replace('/^```json\s*/i', '', $content);
        $content = preg_replace('/^```\s*/', '', $content);
        $content = preg_replace('/\s*```$/', '', $content);

        $structureData = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Falha ao processar JSON da Estrutura: ' . json_last_error_msg() . ' - Conteúdo: ' . $content);
        }

        return $structureData;
    }

    /**
     * Extrai transcrição / legendas de um vídeo do YouTube
     *
     * @param string $url URL do video do youtube
     * @return string O texto extraído das legendas
     * @throws \Exception
     */
    public static function extractYoutubeTranscript($url)
    {
        // Extract Video ID
        preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/\s]{11})%i', $url, $match);
        $videoId = isset($match[1]) ? $match[1] : null;

        if (!$videoId) {
            throw new \Exception('URL do YouTube inválida.');
        }

        // Tenta usar a biblioteca mrmysql (Composer) de preferencia se instalada
        try {
            if (file_exists(JPATH_ROOT . '/vendor/autoload.php')) {
                require_once JPATH_ROOT . '/vendor/autoload.php';
            }
            // Verifica se a classe TranscriptListFetcher foi carregada
            if (class_exists('\\MrMySQL\\YoutubeTranscript\\TranscriptListFetcher') && class_exists('\\GuzzleHttp\\Client')) {
                return self::extractComMrMysql($videoId);
            }
        } catch (\Exception $e) {
            // Se der erro de TranscriptsDisabled, apenas cai pro Except nativo
            if (strpos(get_class($e), 'TranscriptsDisabledException') !== false) {
                 throw new \Exception('O vídeo informado tem as legendas DESATIVADAS.');
            }
            if (strpos(get_class($e), 'TooManyRequestsException') !== false) {
                 throw new \Exception('Bloqueio Anti-Bot (Robô) acionado pelo YouTube devido a excesso de buscas deste servidor.');
            }
            throw new \Exception($e->getMessage());
        }

        try {
            return self::extractNativeYoutube($videoId);
        } catch (\Exception $e) {
            return self::extractPipedYoutube($videoId, $e->getMessage());
        }
    }

    private static function extractComMrMysql($videoId)
    {
        $http_client = new \GuzzleHttp\Client([
           'verify' => false,
           'timeout' => 15
        ]);
        $http_factory = new \GuzzleHttp\Psr7\HttpFactory();
        
        $fetcher = new \MrMySQL\YoutubeTranscript\TranscriptListFetcher($http_client, $http_factory, $http_factory);
        $transcript_list = $fetcher->fetch($videoId);
        
        $transcript = $transcript_list->findTranscript(['pt', 'pt-BR', 'en']);
        if ($transcript) {
            $transcript_text = $transcript->fetch();
            $fullText = '';
            foreach ($transcript_text as $caption) {
                if (isset($caption['text'])) {
                     $fullText .= strip_tags($caption['text']) . ' ';
                }
            }
            if (trim($fullText)) return trim($fullText);
        }
        
        throw new \Exception('Não foi possível encontrar legendas (CC) compatíveis extraíveis neste vídeo usando o motor nativo.');
    }

    private static function extractPipedYoutube($videoId, $originalError)
    {
        $instances = [
            "https://pipedapi.kavin.rocks",
            "https://pipedapi.tokhmi.xyz",
            "https://api.piped.projectsegfau.lt"
        ];

        $context = stream_context_create([
            'http' => [
                'header' => "User-Agent: Mozilla/5.0\r\n",
                'timeout' => 5
            ],
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
            ]
        ]);

        foreach ($instances as $instance) {
            $json = @file_get_contents($instance . "/streams/" . $videoId, false, $context);
            if ($json) {
                $data = json_decode($json, true);
                if (isset($data['subtitles']) && count($data['subtitles']) > 0) {
                    $captionUrl = '';
                    foreach ($data['subtitles'] as $sub) {
                        if (strpos(strtolower($sub['code']), 'pt') !== false) {
                             $captionUrl = $sub['url'];
                             break;
                        }
                    }
                    if (!$captionUrl) {
                        $captionUrl = $data['subtitles'][0]['url'];
                    }

                    $vtt = @file_get_contents($captionUrl, false, $context);
                    if ($vtt) {
                        // Limpa VTT basic
                        $vttClean = preg_replace('/WEBVTT\r?\n|\r?\n\d{2}:\d{2}:\d{2}\.\d{3} --> \d{2}:\d{2}:\d{2}\.\d{3}\r?\n/', ' ', $vtt);
                        $vttClean = strip_tags($vttClean);
                        $vttClean = preg_replace('/\s+/', ' ', $vttClean);
                        return trim($vttClean);
                    }
                }
            }
        }

        // Se falhou no fallback tbm, retorna o erro primario
        throw new \Exception($originalError);
    }

    private static function extractNativeYoutube($videoId)
    {
        // Trick to bypass consent pages: use a known User-Agent and Language
        $context = stream_context_create([
            'http' => [
                'header' => "Accept-Language: pt-BR,pt;q=0.9,en-US;q=0.8,en;q=0.7\r\n" .
                            "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36\r\n"
            ]
        ]);
        
        $html = @file_get_contents('https://www.youtube.com/watch?v=' . $videoId, false, $context);
        
        if (!$html) {
            throw new \Exception('Não foi possível acessar a página do YouTube (verifique conexão ou bloqueio).');
        }

        // Busca pela string que carrega as legendas (mais seguro do que parse completo do YT response que pode não existir)
        if (preg_match('/"captionTracks":\[(.*?)\]/', $html, $matches)) {
            $tracks = json_decode('[' . $matches[1] . ']', true);
            
            if ($tracks && count($tracks) > 0) {
                // Seleciona PT-BR se existir, senão usa a primeira (geralmente gerada ou default)
                $captionUrl = $tracks[0]['baseUrl'];
                
                // Se o link precisa de decodificação criptografada da assinatura, file_get_contents falha (403)
                if (strpos($captionUrl, 'signature=') === false && strpos($captionUrl, 'sig=') === false && isset($tracks[0]['signatureCipher'])) {
                     throw new \Exception('O YouTube bloqueou a requisição (Link Assinado Requirido).');
                }

                foreach ($tracks as $t) {
                    if (isset($t['languageCode']) && strpos(strtolower($t['languageCode']), 'pt') !== false) {
                        $captionUrl = $t['baseUrl'];
                        break;
                    }
                }

                if (strpos($captionUrl, 'fmt=') === false) {
                    $captionUrl .= '&fmt=json3'; // Json is easier and doesn't trigger some XML blocks
                } else {
                    $captionUrl = preg_replace('/fmt=[^&]+/', 'fmt=json3', $captionUrl);
                }

                $jsonContext = stream_context_create([
                    'http' => [
                        'header' => "Accept-Language: pt-BR,pt;q=0.9,en-US;q=0.8,en;q=0.7\r\n" .
                                    "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36\r\n"
                    ],
                    'ssl' => [
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                    ]
                ]);

                // Limpa o último erro para não pegar um warning obsoleto do Joomla ("getUser() deprecated")
                error_clear_last();
                
                // Captura erro sem crashar silenciosamente
                $jsonStr = @file_get_contents($captionUrl, false, $jsonContext);
                
                if ($jsonStr !== false) {
                    $data = json_decode($jsonStr, true);
                    if (!$data || !isset($data['events'])) {
                        // Resposta em JSON3 inválida, possivelmente bloqueada
                        throw new \Exception('O YouTube bloqueou a requisição final (Acesso Negado 403). Conteúdo recebido não é válido. URL: ' . substr($captionUrl,0,50) . '...');
                    }

                    $lines = [];
                    foreach($data['events'] as $event) {
                        if (isset($event['segs'])) {
                            foreach($event['segs'] as $seg) {
                                if (isset($seg['utf8']) && trim($seg['utf8']) !== '') {
                                     $lines[] = trim($seg['utf8']);
                                }
                            }
                        }
                    }
                    if (!empty($lines)) {
                        return implode(' ', $lines);
                    }
                } else {
                     $err = error_get_last();
                     $errMsg = $err ? $err['message'] : 'Desconhecido';
                     // Ignora erros Deprecated do proprio joomla que sobreescrevem o file_get_contents err.
                     if (strpos($errMsg, 'is deprecated') !== false) {
                          $errMsg = 'HTTP Request failed (403 or Timeout)';
                     }
                     throw new \Exception('Falha ao baixar o arquivo final de legendas. Erro de conexão: ' . $errMsg);
                }
            }
        } else {
            // Check if it's an error video
            if (preg_match('/"status":"ERROR","reason":"([^"]+)"/', $html, $errorMatch)) {
                 throw new \Exception('Vídeo indisponível: ' . $errorMatch[1]);
            }
        }

        throw new \Exception('Não foi possível encontrar legendas (CC) disponíveis para extração neste vídeo.');
    }

}
