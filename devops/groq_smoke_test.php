<?php
require_once __DIR__ . '/auth.php';

require_once JPATH_BASE . '/includes/defines.php';
require_once JPATH_BASE . '/includes/framework.php';

use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;

$db = Factory::getDbo();
require_once JPATH_BASE . '/components/com_splms/helpers/GuidewayAIHelper.php';

// Endpoint AJAX para testar as chaves
if (isset($_GET['action']) && $_GET['action'] === 'test_key') {
    header('Content-Type: application/json');
    $index = isset($_POST['key_index']) ? (int)$_POST['key_index'] : 0;
    
    if ($index < 1 || $index > 5) {
        echo json_encode(['status' => 'error', 'message' => 'Índice de chave inválido.']);
        exit;
    }

    try {
        $query = $db->getQuery(true)
            ->select($db->quoteName('params'))
            ->from($db->quoteName('#__extensions'))
            ->where($db->quoteName('element') . ' = ' . $db->quote('com_splms'));
        $db->setQuery($query);
        $paramsJson = $db->loadResult();
        $params = json_decode($paramsJson, true);
        
        $keyName = 'groq_api_key_' . $index;
        $apiKey = isset($params[$keyName]) ? trim($params[$keyName]) : '';

        if (empty($apiKey) && $index === 1) {
            // BACKWARD COMPATIBILITY
            $legacyKey = isset($params['groq_api_key']) ? trim($params['groq_api_key']) : '';
            if (!empty($legacyKey)) {
                $apiKey = $legacyKey;
            }
        }

        if (empty($apiKey)) {
            echo json_encode(['status' => 'empty', 'message' => 'Chave não configurada no Painel do LMS.']);
            exit;
        }

        // Preview de segurança da chave
        $keyPreview = substr($apiKey, 0, 8) . '...' . substr($apiKey, -4);

        $start = microtime(true);
        GuidewayAIHelper::setOverrideKey($apiKey);
        
        $result = GuidewayAIHelper::smokeTest();
        $latency = round((microtime(true) - $start) * 1000);

        echo json_encode([
            'status' => 'ok', 
            'latency' => $latency, 
            'key_preview' => $keyPreview
        ]);

    } catch (Exception $e) {
        $latency = round((microtime(true) - $start) * 1000);
        echo json_encode([
            'status' => 'error', 
            'message' => $e->getMessage(), 
            'latency' => $latency,
            'key_preview' => $keyPreview ?? 'Desconhecida'
        ]);
    }
    exit;
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Groq Smoke Test - Guideway LMS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-primary: #0f172a;
            --bg-secondary: #1e293b;
            --bg-card: #1e293b;
            --bg-hover: #334155;
            --border: #334155;
            --text-primary: #f1f5f9;
            --text-secondary: #94a3b8;
            --text-muted: #64748b;
            --accent-red: #f43f5e;
            --accent-orange: #f97316;
            --accent-yellow: #eab308;
            --accent-green: #22c55e;
            --accent-blue: #3b82f6;
            --accent-purple: #a855f7;
            --accent-cyan: #06b6d4;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', -apple-system, sans-serif; background: var(--bg-primary); color: var(--text-primary); min-height: 100vh; }

        /* === HEADER === */
        .header {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 50%, #1e1b4b 100%);
            padding: 40px 50px;
            border-bottom: 1px solid var(--border);
            position: relative;
            overflow: hidden;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(249,115,22,0.08) 0%, transparent 70%);
            border-radius: 50%;
        }
        .header-inner { max-width: 1000px; width: 100%; margin: 0 auto; display: flex; justify-content: space-between; align-items: center; position: relative; z-index: 1; }
        .header h1 { font-size: 30px; font-weight: 800; letter-spacing: -0.5px; }
        .header h1 span { background: linear-gradient(135deg, var(--accent-orange), var(--accent-yellow)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .header p { color: var(--text-secondary); font-size: 14px; margin-top: 6px; }

        .btn-back {
            background: var(--bg-card); border: 1px solid var(--border); color: var(--text-primary);
            padding: 10px 20px; border-radius: 8px; text-decoration: none; font-size: 14px; transition: 0.2s;
        }
        .btn-back:hover { background: var(--bg-hover); }

        /* === CONTENT === */
        .main { max-width: 1000px; margin: 0 auto; padding: 40px 50px; }

        .section-header {
            display: flex; align-items: center; gap: 12px; margin-bottom: 24px; padding-bottom: 16px; border-bottom: 1px solid var(--border);
            justify-content: space-between;
        }
        .section-header-left { display: flex; align-items: center; gap: 12px; }
        .section-header .icon {
            width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 18px;
            background: rgba(249,115,22,0.15);
        }
        .section-header h2 { font-size: 18px; font-weight: 700; }

        .btn-action {
            background: linear-gradient(135deg, var(--accent-orange), #ea580c);
            border: none; color: white; padding: 12px 24px; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; transition: all 0.2s;
            display: flex; align-items: center; gap: 8px;
        }
        .btn-action:hover { transform: translateY(-2px); box-shadow: 0 4px 15px rgba(249,115,22,0.4); }
        .btn-action:disabled { opacity: 0.7; cursor: not-allowed; transform: none; box-shadow: none; }

        /* === CARDS === */
        .cards { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 16px; margin-bottom: 40px; }
        .card {
            background: var(--bg-card); border: 1px solid var(--border); border-radius: 12px; padding: 24px;
            position: relative; overflow: hidden; transition: all 0.25s;
        }
        .card-top { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 14px; }
        .card-emoji { font-size: 28px; }
        .card-tag { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; padding: 4px 10px; border-radius: 6px; }
        
        .tag-pending { background: rgba(148,163,184,0.15); color: var(--text-secondary); }
        .tag-testing { background: rgba(234,179,8,0.15); color: var(--accent-yellow); animation: pulse 1.5s infinite; }
        .tag-ok { background: rgba(34,197,94,0.15); color: var(--accent-green); }
        .tag-error { background: rgba(244,63,94,0.15); color: var(--accent-red); }
        .tag-empty { background: rgba(100,116,139,0.15); color: var(--text-muted); }

        .card h3 { font-size: 15px; font-weight: 700; margin-bottom: 6px; }
        .card p { font-size: 13px; color: var(--text-secondary); line-height: 1.5; word-wrap: break-word; }
        .card-stripe { position: absolute; bottom: 0; left: 0; right: 0; height: 3px; background: var(--border); transition: 0.3s; }
        
        .card.status-testing .card-stripe { background: var(--accent-yellow); }
        .card.status-ok .card-stripe { background: linear-gradient(90deg, var(--accent-green), var(--accent-cyan)); }
        .card.status-error .card-stripe { background: linear-gradient(90deg, var(--accent-red), var(--accent-orange)); }
        .card.status-empty { opacity: 0.6; }

        .latency-badge { 
            display: inline-block; margin-top: 10px; padding: 4px 8px; border-radius: 4px; 
            font-size: 11px; font-weight: 600; font-family: monospace; background: var(--bg-primary); 
            display: none;
        }

        @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.5; } }
    </style>
</head>
<body>

    <div class="header">
        <div class="header-inner">
            <div>
                <h1>🤖 Groq <span>Smoke Test</span></h1>
                <p>Verificação de Status do Fallback System (5 Chaves)</p>
            </div>
            <a href="index.php" class="btn-back">⬅ Voltar ao Dashboard</a>
        </div>
    </div>

    <div class="main">
        <div class="section-header">
            <div class="section-header-left">
                <div class="icon">⚡</div>
                <h2>Status das Chaves / Load Balancer</h2>
            </div>
            <button class="btn-action" id="btn-run" onclick="runAllTests()">
                <span>🚀</span> Iniciar Verificação
            </button>
        </div>

        <div class="cards" id="cards-container">
            <!-- Gerado via JS -->
        </div>
    </div>

    <script>
        const keysConfig = [
            { id: 1, name: "Chave Primária", desc: "A chave primária que será usada sempre pelas requisições regulares de IA." },
            { id: 2, name: "Reserva 1", desc: "Usada automaticamente se a primária der limite excedido/erro." },
            { id: 3, name: "Reserva 2", desc: "Fallback Secundário caso a Reserva 1 também estoure limite." },
            { id: 4, name: "Reserva 3", desc: "Penúltimo Recurso de sistema." },
            { id: 5, name: "Reserva 4", desc: "A última esperança de fallback para conexão." }
        ];

        const container = document.getElementById('cards-container');

        function renderCards() {
            container.innerHTML = '';
            keysConfig.forEach(k => {
                const card = document.createElement('div');
                card.className = 'card status-empty'; 
                card.id = 'card-' + k.id;
                
                card.innerHTML = `
                    <div class="card-top">
                        <div class="card-emoji">🔑</div>
                        <span class="card-tag tag-pending" id="badge-${k.id}">Aguardando</span>
                    </div>
                    <h3>${k.name}</h3>
                    <p style="margin-bottom: 6px;">${k.desc}</p>
                    <p id="desc-${k.id}" style="font-family: monospace; color: var(--text-muted); font-size: 12px; margin-top: 10px;"></p>
                    <div class="latency-badge" id="latency-${k.id}"></div>
                    <div class="card-stripe" id="stripe-${k.id}"></div>
                `;
                container.appendChild(card);
            });
        }

        renderCards();

        async function runAllTests() {
            const btn = document.getElementById('btn-run');
            btn.disabled = true;
            btn.innerHTML = '<span>⏳</span> Testando...';

            keysConfig.forEach(k => {
                const card = document.getElementById('card-' + k.id);
                card.className = 'card status-testing';
                document.getElementById('badge-' + k.id).className = 'card-tag tag-testing';
                document.getElementById('badge-' + k.id).innerText = 'Testando...';
                document.getElementById('desc-' + k.id).innerText = '';
                document.getElementById('desc-' + k.id).style.color = 'var(--text-muted)';
                document.getElementById('latency-' + k.id).style.display = 'none';
            });

            // Executa os testes em paralelo iterando pelo array
            const promises = keysConfig.map(k => testKey(k.id));
            await Promise.all(promises);

            btn.disabled = false;
            btn.innerHTML = '<span>🚀</span> Refazer Teste';
        }

        async function testKey(id) {
            try {
                const formData = new FormData();
                formData.append('key_index', id);

                const response = await fetch('?action=test_key', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                const card = document.getElementById('card-' + id);
                const badge = document.getElementById('badge-' + id);
                const desc = document.getElementById('desc-' + id);
                const latencyBadge = document.getElementById('latency-' + id);

                if (data.status === 'empty') {
                    card.className = 'card status-empty';
                    badge.className = 'card-tag tag-empty';
                    badge.innerText = 'Vazio';
                    desc.innerText = data.message;
                } else if (data.status === 'ok') {
                    card.className = 'card status-ok';
                    badge.className = 'card-tag tag-ok';
                    badge.innerText = 'Conectado / OK';
                    desc.innerText = 'Chave: ' + data.key_preview;
                    desc.style.color = 'var(--text-secondary)';
                    latencyBadge.innerText = data.latency + ' ms';
                    latencyBadge.style.color = data.latency > 1500 ? 'var(--accent-yellow)' : 'var(--accent-green)';
                    latencyBadge.style.display = 'inline-block';
                } else {
                    card.className = 'card status-error';
                    badge.className = 'card-tag tag-error';
                    badge.innerText = 'Falha na IA';
                    desc.innerText = data.message + (data.key_preview ? ' (' + data.key_preview + ')' : '');
                    desc.style.color = 'var(--accent-red)';
                    if (data.latency) {
                        latencyBadge.innerText = data.latency + ' ms';
                        latencyBadge.style.color = 'var(--accent-red)';
                        latencyBadge.style.display = 'inline-block';
                    }
                }

            } catch (err) {
                const card = document.getElementById('card-' + id);
                card.className = 'card status-error';
                document.getElementById('badge-' + id).className = 'card-tag tag-error';
                document.getElementById('badge-' + id).innerText = 'Erro Script';
                document.getElementById('desc-' + id).innerText = 'Erro de requisição: ' + err.message;
                document.getElementById('desc-' + id).style.color = 'var(--accent-red)';
            }
        }
    </script>
</body>
</html>
