<?php
require_once __DIR__ . '/auth.php';

require_once JPATH_BASE . '/includes/defines.php';
require_once JPATH_BASE . '/includes/framework.php';

use Joomla\CMS\Factory;
use Joomla\CMS\Session\Session;

// Carregar aplicação para gerar token CSRF válido da sessão
$app = Factory::getApplication('administrator');
$csrfToken = Session::getFormToken();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Suíte Endpoint callAI - Guideway LMS</title>
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
            background: radial-gradient(circle, rgba(168,85,247,0.08) 0%, transparent 70%);
            border-radius: 50%;
        }
        .header-inner { max-width: 1000px; width: 100%; margin: 0 auto; display: flex; justify-content: space-between; align-items: center; position: relative; z-index: 1; }
        .header h1 { font-size: 30px; font-weight: 800; letter-spacing: -0.5px; }
        .header h1 span { background: linear-gradient(135deg, var(--accent-purple), var(--accent-red)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .header p { color: var(--text-secondary); font-size: 14px; margin-top: 6px; }

        .btn-back {
            background: var(--bg-card); border: 1px solid var(--border); color: var(--text-primary);
            padding: 10px 20px; border-radius: 8px; text-decoration: none; font-size: 14px; transition: 0.2s;
        }
        .btn-back:hover { background: var(--bg-hover); }

        /* === CONTENT === */
        .main { max-width: 1000px; margin: 0 auto; padding: 40px 50px; }

        .doc-panel { background: var(--bg-card); border: 1px solid var(--border); border-radius: 12px; padding: 32px; box-shadow: 0 4px 20px rgba(0,0,0,0.15); }
        .doc-panel h2 { color: var(--accent-purple); font-size: 18px; margin: 0 0 15px; display: flex; align-items: center; gap: 8px;}
        .doc-panel p { color: var(--text-secondary); line-height: 1.6; font-size: 14px; margin-bottom: 15px; }
        .doc-panel strong { color: var(--text-primary); font-weight: 600; }
        .doc-panel code { background: rgba(0,0,0,0.2); border: 1px solid var(--border); padding: 2px 6px; border-radius: 4px; color: var(--accent-blue); }

        .section-header {
            display: flex; align-items: center; gap: 12px; margin: 40px 0 24px; padding-bottom: 16px; border-bottom: 1px solid var(--border);
            justify-content: space-between;
        }
        .section-header-left { display: flex; align-items: center; gap: 12px; }
        .section-header .icon {
            width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 18px;
            background: rgba(168,85,247,0.15);
        }
        .section-header h2 { font-size: 18px; font-weight: 700; }

        .btn-action {
            background: linear-gradient(135deg, var(--accent-purple), var(--accent-red));
            border: none; color: white; padding: 12px 24px; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; transition: all 0.2s;
            display: flex; align-items: center; gap: 8px;
        }
        .btn-action:hover { transform: translateY(-2px); box-shadow: 0 4px 15px rgba(168,85,247,0.4); }
        .btn-action:disabled { opacity: 0.7; cursor: not-allowed; transform: none; box-shadow: none; }

        /* === CARDS === */
        .cards { display: grid; grid-template-columns: repeat(1, 1fr); gap: 16px; margin-bottom: 40px; }
        .card {
            background: var(--bg-card); border: 1px solid var(--border); border-radius: 12px; padding: 24px;
            position: relative; overflow: hidden; transition: all 0.25s;
        }
        .card-top { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 14px; }
        .card-emoji { font-size: 24px; display: flex; align-items: center; gap: 12px; }
        .card-emoji span.card-title { font-size: 16px; font-weight: 700; color: var(--text-primary); }
        .card-tag { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; padding: 4px 10px; border-radius: 6px; }
        
        .tag-pending { background: rgba(148,163,184,0.15); color: var(--text-secondary); }
        .tag-testing { background: rgba(59,130,246,0.15); color: var(--accent-blue); animation: pulse 1.5s infinite; }
        .tag-ok { background: rgba(34,197,94,0.15); color: var(--accent-green); }
        .tag-error { background: rgba(244,63,94,0.15); color: var(--accent-red); }

        .card p.desc { font-size: 13px; color: var(--text-secondary); margin-bottom: 15px; }
        
        .log-box { 
            background: #0f172a; border: 1px solid var(--border); border-radius: 8px; padding: 12px;
            font-family: monospace; font-size: 12px; color: var(--text-secondary); white-space: pre-wrap; word-break: break-all;
            display: none;
        }
        .log-box.has-content { display: block; }
        .log-box .log-title { color: var(--text-primary); font-weight: 600; margin-bottom: 6px; display: block; border-bottom: 1px dashed var(--border); padding-bottom: 4px; }

        .card-stripe { position: absolute; bottom: 0; left: 0; right: 0; height: 3px; background: var(--border); transition: 0.3s; }
        .card.status-testing .card-stripe { background: var(--accent-blue); }
        .card.status-ok .card-stripe { background: linear-gradient(90deg, var(--accent-green), var(--accent-cyan)); }
        .card.status-error .card-stripe { background: linear-gradient(90deg, var(--accent-red), var(--accent-orange)); }

        @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.5; } }
    </style>
</head>
<body>

    <div class="header">
        <div class="header-inner">
            <div>
                <h1>🔐 Endpoint <span>callAI Test</span></h1>
                <p>Análise de End-to-End da Integração de IA e Token de Sessão</p>
            </div>
            <a href="index.php" class="btn-back">⬅ Voltar ao Dashboard</a>
        </div>
    </div>

    <div class="main">
        
        <!-- EXPLANATION SECTION -->
        <div class="doc-panel">
            <h2>🛡️ Sobre a Validação CSRF e o Endpoint callAI</h2>
            <p><strong>O que é?</strong> CSRF (<em>Cross-Site Request Forgery</em>) Token é uma assinatura digital de uso único vinculada à sessão do administrador logado no Joomla.</p>
            <p><strong>Por que é importante?</strong> Como a API do Groq e o módulo da Guideway executam chamadas pagas baseadas em consumo de Tokens de IA, o endpoint <code>lesson.callAI</code> exige essa validação do Joomla para impedir requisições maliciosas. Sem a verificação de Token, scripts externos de domínio cruzado poderiam esgotar nossas cotas de inteligência artificial ou manipular dados remotamente.</p>
            <p style="margin-bottom: 0;"><strong>Como é usado?</strong> O frontend (botões de revisar, resumir etc.) captura o carimbo de segurança escondido via <code>Joomla.getOptions('csrf.token')</code> e envia dentro da chamada AJAX (Fetch POST). O Controller da extensão então valida. Se o Token não for submetido, a conexão sofre uma rejeição HTTP tipo <code>403 Forbidden</code> instaneamente, barrando qualquer execução.</p>
        </div>

        <div class="section-header">
            <div class="section-header-left">
                <div class="icon">🧪</div>
                <h2>Suíte E2E de Testes Dinâmicos</h2>
            </div>
            <button class="btn-action" id="btn-run" onclick="runAllEndpointTests()">
                <span>▶</span> Executar Bateria de Testes
            </button>
        </div>

        <!-- CARDS GRID -->
        <div class="cards" id="tests-container">
            <!-- Test 1: Sem Token -->
            <div class="card" id="card-test-1">
                <div class="card-top">
                    <div class="card-emoji">🚫 <span class="card-title">Cenário 1: Segurança Anti-CSRF (Negativo)</span></div>
                    <span class="card-tag tag-pending" id="badge-1">Pendente</span>
                </div>
                <p class="desc">Acessa a API retendo de forma forçada o Token na requisição. Verifica se o bloqueio do backend está ativo.</p>
                <div class="log-box" id="log-1">
                    <div style="margin-bottom: 8px; color: var(--accent-blue);"><strong>Esperado:</strong> Erro via JSON (HTTP 500/403) informando a rejeição do Token CSRF</div>
                    <div><strong style="color: var(--text-primary);">Recebido:</strong> <br><span id="logtext-1"></span></div>
                </div>
                <div class="card-stripe" id="stripe-1"></div>
            </div>

            <!-- Test 2: Com Token (End-to-End Positivo) -->
            <div class="card" id="card-test-2">
                <div class="card-top">
                    <div class="card-emoji">🚀 <span class="card-title">Cenário 2: Chamada Completa (Positivo)</span></div>
                    <span class="card-tag tag-pending" id="badge-2">Pendente</span>
                </div>
                <p class="desc">Injeta o Token de Segurança atual, enviando um comando válido de <strong>resumir</strong> via POST. Valida o payload de sucesso.</p>
                <div class="log-box" id="log-2">
                    <div style="margin-bottom: 8px; color: var(--accent-blue);"><strong>Esperado:</strong> Status 200 OK com payload JSON validando Sucesso (`{"success": true}`).</div>
                    <div><strong style="color: var(--text-primary);">Recebido:</strong> <br><span id="logtext-2"></span></div>
                </div>
                <div class="card-stripe" id="stripe-2"></div>
            </div>

            <!-- Test 3: Fallback Ação Inválida (Validação) -->
            <div class="card" id="card-test-3">
                <div class="card-top">
                    <div class="card-emoji">🛡️ <span class="card-title">Cenário 3: Input Hacker / Ação Inexistente</span></div>
                    <span class="card-tag tag-pending" id="badge-3">Pendente</span>
                </div>
                <p class="desc">Solicita o JSON da IA enviando um token válido mas uma regra de input corrompida (<code>acao=hack_system</code>). Garante tratamento de Erro sem crash.</p>
                <div class="log-box" id="log-3">
                    <div style="margin-bottom: 8px; color: var(--accent-blue);"><strong>Esperado:</strong> Erro tratado via JSON informando bloqueio explícito da Ação (HTTP 200 ou 500)</div>
                    <div><strong style="color: var(--text-primary);">Recebido:</strong> <br><span id="logtext-3"></span></div>
                </div>
                <div class="card-stripe" id="stripe-3"></div>
            </div>
        </div>
    </div>

    <script>
        const baseUrl = '../administrator/index.php?option=com_splms&task=lesson.callAI';
        const validCsrfToken = '<?php echo $csrfToken; ?>';

        const updateCard = (id, status, badgeText, logText) => {
            const card = document.getElementById('card-test-' + id);
            const badge = document.getElementById('badge-' + id);
            const logBox = document.getElementById('log-' + id);
            const logSpan = document.getElementById('logtext-' + id);

            if (status === 'testing') {
                card.className = 'card status-testing';
                badge.className = 'card-tag tag-testing';
            } else if (status === 'ok') {
                card.className = 'card status-ok';
                badge.className = 'card-tag tag-ok';
            } else if (status === 'error') {
                card.className = 'card status-error';
                badge.className = 'card-tag tag-error';
            } else {
                card.className = 'card';
                badge.className = 'card-tag tag-pending';
            }

            badge.innerText = badgeText;
            
            if (logText !== null) {
                logSpan.innerText = logText;
                logBox.classList.add('has-content');
            } else {
                logSpan.innerText = '';
                logBox.classList.remove('has-content');
            }
        };

        async function runAllEndpointTests() {
            const btn = document.getElementById('btn-run');
            btn.disabled = true;
            btn.innerHTML = '<span>⏳</span> Executando Bateria...';

            [1, 2, 3].forEach(id => updateCard(id, 'testing', 'Processando...', null));

            await testCSRF();
            await testValidRequest();
            await testInvalidAction();

            btn.disabled = false;
            btn.innerHTML = '<span>▶</span> Refazer Bateria';
        }

        // Teste 1: Falha Esperada no CSRF (Sem Token)
        async function testCSRF() {
            try {
                const formData = new URLSearchParams();
                formData.append('texto', 'Teste');
                formData.append('acao', 'revisar');

                const response = await fetch(baseUrl, {
                    method: 'POST',
                    body: formData,
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
                });

                const text = await response.text();
                
                // Aceita 403 HTML ou JSON 500 devolvido pelo ErrorHandler do Joomla
                if (response.status === 403) {
                    updateCard(1, 'ok', 'Bloqueado OK (403)', `HTTP 403 Retornado com sucesso!\n\nCorpo:\n${text.substring(0, 150)}...`);
                } else if (response.status === 500 && text.includes('token')) {
                    updateCard(1, 'ok', 'Proteção Ativa (JSON)', `O Controller bloqueou devolvendo JSON formatado e blindado:\n\n${text}`);
                } else {
                    updateCard(1, 'error', `Inesperado (Status: ${response.status})`, `ERRO! Endpoint deveria ter retornado bloqueio do CSRF token.\n\nRetornou:\n${text}`);
                }
            } catch (e) {
                updateCard(1, 'error', 'Erro JS', e.message);
            }
        }

        // Teste 2: Payload Correto (Com Token)
        async function testValidRequest() {
            try {
                const formData = new URLSearchParams();
                formData.append('texto', 'This is a single tiny connectivity test. Say precisely "Hello".');
                formData.append('acao', 'resumir');
                formData.append(validCsrfToken, '1'); // O Joomla lê a chave (token hash) => value(1)

                const start = performance.now();
                const response = await fetch(baseUrl, {
                    method: 'POST',
                    body: formData.toString(),
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
                });

                const text = await response.text();
                const timeStr = Math.round(performance.now() - start) + 'ms';

                if (response.status === 200) {
                    try {
                        const json = JSON.parse(text);
                        if (json.success === true) {
                            updateCard(2, 'ok', `Sucesso (${timeStr})`, `JSON Válido: \n\n${JSON.stringify(json, null, 2)}`);
                        } else {
                            updateCard(2, 'error', 'Falha Interna (JSON)', `AI Respondeu:\n\n${JSON.stringify(json, null, 2)}`);
                        }
                    } catch(err) {
                        updateCard(2, 'error', 'Parse Error (Não é JSON)', `Endpoint não retornou JSON! Foi:\n\n${text}`);
                    }
                } else {
                    updateCard(2, 'error', `Erro HTTP ${response.status}`, `Acesso Negado (ou outro erro) ao tentar requisição autorizada.\n\n${text}`);
                }
            } catch (e) {
                updateCard(2, 'error', 'Erro JS', e.message);
            }
        }

        // Teste 3: Ação Inválida Tratada (Com Token)
        async function testInvalidAction() {
            try {
                const formData = new URLSearchParams();
                formData.append('texto', 'Ignorado');
                formData.append('acao', 'hack_system');
                formData.append(validCsrfToken, '1'); // Token Válido

                const response = await fetch(baseUrl, {
                    method: 'POST',
                    body: formData.toString(),
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
                });

                const text = await response.text();

                // Pode voltar 200 com falso JSON ou 500 Exception enviada pelo próprio script de segurança
                if (response.status === 200 || response.status === 500) {
                    try {
                        const json = JSON.parse(text);
                        // Verifica se capturou a palavra Ação ou Permissão bloqueada
                        if (json.success === false && (json.message.includes('Ação') || json.message.includes('A\u00e7\u00e3o') || json.message.includes('permiss\u00e3o'))) {
                            updateCard(3, 'ok', 'Ação Rejeitada (API Seguro)', `Script da IA abortou corretamente via JSON e não quebrou a estrutura:\n\n${JSON.stringify(json, null, 2)}`);
                        } else {
                            updateCard(3, 'error', 'Comportamento Inesperado', `JSON Retornado incorreto:\n\n${JSON.stringify(json, null, 2)}`);
                        }
                    } catch(err) {
                        updateCard(3, 'error', 'Crash! (Não é JSON)', `Endpoint provavelmente Quebrou retornando HTML em vez de JSON erro. Texto:\n\n${text}`);
                    }
                } else {
                    updateCard(3, 'error', `Erro HTTP ${response.status}`, `Retornou Server Error bruto, falhou tratar no formato JSON.\n\n${text}`);
                }
            } catch (e) {
                updateCard(3, 'error', 'Erro de JS', e.message);
            }
        }
    </script>
</body>
</html>
