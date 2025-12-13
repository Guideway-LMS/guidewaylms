<?php
define('_JEXEC', 1);
define('JPATH_BASE', dirname(__DIR__));

// Carregar o framework do Joomla
require_once JPATH_BASE . '/includes/defines.php';
require_once JPATH_BASE . '/includes/framework.php';

use Joomla\CMS\Factory;

// Bootstrap Joomla - Mínimo necessário para DB
// Não iniciamos a aplicação completa ($app) para evitar erros 500 em scripts standalone
$db = Factory::getDbo();

// Carregar o helper
require_once JPATH_BASE . '/components/com_splms/helpers/GuidewayAIHelper.php';

// Buscar a API Key diretamente do banco de dados (tabela #__extensions)
// Isso evita a necessidade de carregar todo o stack do Joomla (ComponentHelper/Application)
try {
    $query = $db->getQuery(true)
        ->select($db->quoteName('params'))
        ->from($db->quoteName('#__extensions'))
        ->where($db->quoteName('element') . ' = ' . $db->quote('com_splms'));
    
    $db->setQuery($query);
    $paramsJson = $db->loadResult();
    
    $params = json_decode($paramsJson, true);
    $apiKey = isset($params['groq_api_key']) ? $params['groq_api_key'] : '';
    
    if ($apiKey) {
        // Injeta a chave recuperada no Helper
        GuidewayAIHelper::setOverrideKey($apiKey);
    }
} catch (Exception $e) {
    // Falha silenciosa na busca do banco, o teste vai falhar mais a frente se não tiver chave
    $apiKey = '';
}

echo "<!DOCTYPE html><html><head><title>Groq Smoke Test</title><style>body{background:#2d2d2d;color:#f8f8f2;font-family:monospace;padding:20px;}</style></head><body><pre>";

try {
    echo "=== GROQ API SMOKE TEST ===\n";
    
    if (empty($apiKey)) {
        echo "⚠️ AVISO: Não foi possível recuperar a API Key do banco de dados.\n";
        echo "Verifique se o componente SP LMS está configurado corretamente.\n\n";
    } else {
        echo "API Key (Recuperada do DB): " . substr($apiKey, 0, 10) . "...\n\n";
    }
    
    $result = GuidewayAIHelper::smokeTest();
    
    echo "✅ Teste executado com sucesso!\n\n";
    echo "Resposta da API:\n";
    print_r($result);
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
}

echo "</pre></body></html>";
