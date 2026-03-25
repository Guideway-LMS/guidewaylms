<?php
require_once __DIR__ . '/auth.php';

// define('_JEXEC', 1);
// define('JPATH_BASE', dirname(__DIR__));

// Carregar o framework do Joomla
require_once JPATH_BASE . '/includes/defines.php';
require_once JPATH_BASE . '/includes/framework.php';

use Joomla\CMS\Factory;

// --- Backend Logic (AJAX Handler) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    $db = Factory::getDbo();
    require_once JPATH_BASE . '/components/com_splms/helpers/GuidewayAIHelper.php';

    try {
        $query = $db->getQuery(true)
            ->select($db->quoteName('params'))
            ->from($db->quoteName('#__extensions'))
            ->where($db->quoteName('element') . ' = ' . $db->quote('com_splms'));
        $db->setQuery($query);
        $params = json_decode($db->loadResult(), true);
        $apiKey = $params['groq_api_key'] ?? '';
        if ($apiKey) GuidewayAIHelper::setOverrideKey($apiKey);
    } catch (Exception $e) { /* Ignore */ }

    $input = json_decode(file_get_contents('php://input'), true);
    $text = $input['text'] ?? '';
    $action = $input['action'] ?? '';

    if (!$text || !$action) {
        echo json_encode(['success' => false, 'message' => 'Parâmetros inválidos']);
        exit;
    }

    try {
        $start = microtime(true);
        $result = GuidewayAIHelper::processarTexto($text, $action);
        $end = microtime(true);
        $result['time'] = number_format($end - $start, 2);
        
        if ($action === 'acao_inexistente' && !$result['success']) {
             $result['success'] = true;
             $result['data'] = "Erro esperado: " . $result['message'];
             unset($result['message']);
        }
        echo json_encode($result);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teste de Prompts - Guideway LMS</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, system-ui, sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #1a202c; padding: 20px; min-height: 100vh; }
        .container { max-width: 900px; margin: 0 auto; }
        .header { background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%); color: white; padding: 25px; border-radius: 12px 12px 0 0; display: flex; justify-content: space-between; align-items: center; }
        .main-card { background: white; border-radius: 0 0 12px 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.2); padding: 30px; }
        .back-btn { color: white; text-decoration: none; background: rgba(255,255,255,0.2); padding: 8px 16px; border-radius: 6px; }
        .back-btn:hover { background: rgba(255,255,255,0.3); }
        
        textarea { width: 100%; height: 120px; padding: 12px; border: 1px solid #e2e8f0; border-radius: 8px; font-family: monospace; font-size: 14px; margin-bottom: 10px; resize: vertical; }
        .bg-toolbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; flex-wrap: wrap; gap: 10px; }
        .btn-link { background: none; border: none; color: #3182ce; cursor: pointer; text-decoration: underline; font-size: 14px; }
        
        .action-buttons { display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 10px; margin-bottom: 20px; }
        .btn { padding: 12px; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; transition: transform 0.1s; color: white; text-align: center; }
        .btn:active { transform: scale(0.98); }
        .btn:disabled { opacity: 0.6; cursor: wait; }
        
        .btn-review { background: #4299e1; }
        .btn-summarize { background: #ed8936; }
        .btn-rewrite { background: #48bb78; }
        .btn-invalid { background: #e53e3e; }
        .btn-all { background: #2d3748; grid-column: 1 / -1; margin-top: 10px; font-size: 16px; padding: 15px;}

        .results-area { display: flex; flex-direction: column; gap: 15px; }
        .result-card { border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; display: none; }
        .result-header { padding: 10px 15px; font-weight: 600; display: flex; justify-content: space-between; align-items: center; }
        .result-body { padding: 15px; background: #f7fafc; font-family: monospace; white-space: pre-wrap; font-size: 13px; border-top: 1px solid #e2e8f0; }
        
        .status-success { background: #c6f6d5; color: #22543d; }
        .status-error { background: #fed7d7; color: #822727; }
        .status-loading { background: #ebf8ff; color: #2b6cb0; }

        .token-warning { font-size: 12px; color: #e53e3e; display: flex; align-items: center; gap: 5px; background: #fff5f5; padding: 5px 10px; border-radius: 20px; border: 1px solid #feb2b2; }

        .docs-section { margin-top: 30px; border: 1px solid #e2e8f0; border-radius: 8px; }
        .docs-section summary { padding: 15px; cursor: pointer; background: #f7fafc; font-weight: 600; list-style: none; }
        .docs-section summary::-webkit-details-marker { display: none; }
        .docs-section summary::before { content: '▶ '; font-size: 12px; }
        .docs-section[open] summary::before { content: '▼ '; }
        .docs-content { padding: 20px; line-height: 1.6; font-size: 14px; }
        .docs-content ul { margin-left: 20px; }
        .docs-content li { margin: 5px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📝 Teste de Prompts Interativo</h1>
            <a href="index.php" class="back-btn">← Voltar</a>
        </div>
        
        <div class="main-card">
            <div class="controls">
                <div class="bg-toolbar">
                    <label style="font-weight: 600; color: #4a5568;">Texto de Entrada:</label>
                    <div style="display: flex; gap: 15px; align-items: center;">
                        <span class="token-warning">🪙 Cada ação consome tokens</span>
                        <button class="btn-link" onclick="fillSuggestedText()">📋 Preencher Texto Sugerido</button>
                    </div>
                </div>
                <textarea id="inputText" placeholder="Digite seu texto aqui para testar as ações..."></textarea>
                
                <div class="action-buttons">
                    <button class="btn btn-review" onclick="runAction('revisar', 'Revisar')">🔍 Revisar</button>
                    <button class="btn btn-summarize" onclick="runAction('resumir', 'Resumir')">📝 Resumir</button>
                    <button class="btn btn-rewrite" onclick="runAction('reescrever', 'Reescrever')">✍️ Reescrever</button>
                    <button class="btn btn-invalid" onclick="runAction('acao_inexistente', 'Teste Inválido')">🚫 Teste Inválido</button>
                    <button class="btn btn-all" onclick="runAll()">🚀 TESTAR TUDO (Sequencial)</button>
                </div>
            </div>

            <div id="resultsContainer" class="results-area"></div>

            <details class="docs-section">
                <summary>📖 Entenda os Testes (Documentação)</summary>
                <div class="docs-content">
                    <h3>🎯 Objetivo</h3>
                    <p>Interface interativa para validar o <code>GuidewayAIHelper</code>. Permite testar prompts individuais para economizar tokens ou executar a suíte completa.</p>
                    <h3>💡 Funcionalidades</h3>
                    <ul>
                        <li><strong>Revisar:</strong> Corrige erros ortográficos/gramaticais.</li>
                        <li><strong>Resumir:</strong> Sintetiza o texto.</li>
                        <li><strong>Reescrever:</strong> Melhora estilo e tom.</li>
                        <li><strong>Teste Inválido:</strong> Envia ação inexistente para validar tratamento de erros (Espera-se que falhe graciosamente).</li>
                    </ul>
                </div>
            </details>
        </div>
    </div>

    <script>
        const suggestedText = "Este é um texto de teste. Ele contem algums erros de ortografia propositais para testar a funcionalidade de revisão. Também é um pouco longo para que possamos testar o resumo, explicando coisas que poderiam ser ditas de forma mais simples.";

        function fillSuggestedText() {
            document.getElementById('inputText').value = suggestedText;
        }

        function createResultCard(id, title) {
            const div = document.createElement('div');
            div.className = 'result-card';
            div.id = 'card-' + id;
            div.innerHTML = `
                <div class="result-header status-loading" id="header-${id}"><span>${title}</span><span id="time-${id}">⏳ Processando...</span></div>
                <div class="result-body" id="body-${id}">Aguardando resposta...</div>
            `;
            return div;
        }

        async function runAction(action, friendlyName, append = false) {
            const text = document.getElementById('inputText').value;
            if (!text.trim()) { alert('Por favor, insira um texto.'); return; }

            const container = document.getElementById('resultsContainer');
            if (!append) container.innerHTML = '';
            
            const cardId = action + '-' + Date.now();
            const card = createResultCard(cardId, friendlyName);
            container.appendChild(card);
            card.style.display = 'block';

            try {
                const response = await fetch('groq_prompt_test.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ text: text, action: action })
                });
                const data = await response.json();
                const header = document.getElementById(`header-${cardId}`);
                const body = document.getElementById(`body-${cardId}`);

                if (data.success) {
                    header.className = 'result-header status-success';
                    header.innerHTML = `<span>✅ ${friendlyName}</span><span>${data.time}s</span>`;
                    body.textContent = data.data;
                } else {
                    header.className = 'result-header status-error';
                    header.innerHTML = `<span>❌ ${friendlyName}</span><span>${data.time || ''}s</span>`;
                    body.textContent = data.message;
                }
            } catch (error) {
                document.getElementById(`header-${cardId}`).className = 'result-header status-error';
                document.getElementById(`header-${cardId}`).innerHTML = `<span>❌ Erro de Rede</span>`;
                document.getElementById(`body-${cardId}`).textContent = error.message;
            }
        }

        async function runAll() {
            if (!document.getElementById('inputText').value.trim()) { alert('Por favor, insira um texto.'); return; }
            document.getElementById('resultsContainer').innerHTML = '';
            await runAction('revisar', 'Revisar', true);
            await runAction('resumir', 'Resumir', true);
            await runAction('reescrever', 'Reescrever', true);
            await runAction('acao_inexistente', 'Teste Inválido', true);
        }
    </script>
</body>
</html>
