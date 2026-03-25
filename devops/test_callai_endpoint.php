<?php
require_once __DIR__ . '/auth.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teste Endpoint callAI</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, system-ui, sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 20px; min-height: 100vh; }
        .container { max-width: 900px; margin: 0 auto; }
        .header { background: #2c3e50; color: white; padding: 20px; border-radius: 12px 12px 0 0; display: flex; justify-content: space-between; align-items: center; }
        .back-btn { color: white; text-decoration: none; background: rgba(255,255,255,0.2); padding: 8px 16px; border-radius: 6px; }
        .main-card { background: white; padding: 30px; border-radius: 0 0 12px 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); }
        
        .section { margin-bottom: 30px; }
        .section-title { font-size: 18px; color: #2c3e50; margin-bottom: 15px; padding-bottom: 10px; border-bottom: 2px solid #e2e8f0; }
        
        .test-btn { padding: 12px 20px; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; margin: 5px 5px 5px 0; color: white; }
        .btn-red { background: #e53e3e; }
        .btn-green { background: #38a169; }
        .btn-blue { background: #3182ce; }
        
        .result-box { margin-top: 15px; padding: 15px; border-radius: 8px; font-family: monospace; white-space: pre-wrap; display: none; font-size: 13px; }
        .result-success { background: #c6f6d5; border: 1px solid #9ae6b4; }
        .result-error { background: #fed7d7; border: 1px solid #feb2b2; }
        
        .info-box { background: #ebf8ff; border-left: 4px solid #3182ce; padding: 15px; margin-bottom: 20px; font-size: 14px; border-radius: 0 8px 8px 0; }
        .success-box { background: #c6f6d5; border-left: 4px solid #38a169; padding: 15px; margin-bottom: 20px; font-size: 14px; border-radius: 0 8px 8px 0; }
        .warning-box { background: #fffaf0; border-left: 4px solid #ed8936; padding: 15px; margin-bottom: 20px; font-size: 14px; border-radius: 0 8px 8px 0; }
        
        code { background: #edf2f7; padding: 2px 6px; border-radius: 3px; font-family: monospace; font-size: 13px; }
        pre { background: #2d3748; color: #e2e8f0; padding: 15px; border-radius: 8px; overflow-x: auto; font-size: 13px; margin: 10px 0; }
        
        .checklist { list-style: none; padding: 0; }
        .checklist li { padding: 8px 0; border-bottom: 1px solid #e2e8f0; }
        .checklist li:last-child { border-bottom: none; }
        .check { color: #38a169; margin-right: 8px; }
        
        .flow-diagram { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; text-align: center; margin: 20px 0; }
        .flow-step { background: #667eea; color: white; padding: 15px 10px; border-radius: 8px; font-size: 12px; }
        .flow-arrow { display: flex; align-items: center; justify-content: center; color: #667eea; font-size: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔐 Teste Endpoint callAI</h1>
            <a href="index.php" class="back-btn">← Voltar</a>
        </div>
        
        <div class="main-card">
            <!-- Seção 1: Teste de Segurança -->
            <div class="section">
                <h2 class="section-title">🛡️ Teste de Segurança CSRF</h2>
                
                <div class="info-box">
                    <strong>Endpoint:</strong> <code>/index.php?option=com_splms&task=lesson.callAI</code><br>
                    <strong>Método:</strong> POST
                </div>

                <button class="test-btn btn-red" onclick="testWithoutToken()">🚫 Testar SEM Token (Espera 403)</button>
                
                <div id="result-csrf" class="result-box"></div>
            </div>

            <!-- Seção 2: Fluxo de Integração -->
            <div class="section">
                <h2 class="section-title">🔄 Fluxo de Integração Completo</h2>
                
                <div class="flow-diagram">
                    <div class="flow-step">
                        <strong>Frontend</strong><br>
                        toolbar_ai.php<br>
                        (Botões UI)
                    </div>
                    <div class="flow-step">
                        <strong>JavaScript</strong><br>
                        guideway_ai.js<br>
                        (AJAX + Token)
                    </div>
                    <div class="flow-step">
                        <strong>Controller</strong><br>
                        lesson.php<br>
                        (callAI)
                    </div>
                    <div class="flow-step">
                        <strong>Helper</strong><br>
                        GuidewayAIHelper<br>
                        (Groq API)
                    </div>
                </div>

                <div class="success-box">
                    <strong>✅ Integração Ativa</strong><br>
                    O JavaScript do Michel (<code>guideway_ai.js</code>) já está configurado para chamar o endpoint real.
                </div>
            </div>

            <!-- Seção 3: Código do Frontend -->
            <div class="section">
                <h2 class="section-title">📝 Código JavaScript (Michel)</h2>
                
                <p style="margin-bottom: 10px; color: #718096;">Arquivo: <code>administrator/components/com_splms/assets/js/guideway_ai.js</code></p>
                
                <pre>// Chamada real para o endpoint callAI
async callAI(acao, texto) {
    const token = Joomla.getOptions('csrf.token');
    
    const formData = new URLSearchParams();
    formData.append('texto', texto);
    formData.append('acao', acao);
    formData.append(token, '1');

    const response = await fetch(
        '/index.php?option=com_splms&task=lesson.callAI',
        { method: 'POST', body: formData.toString() }
    );
    return await response.json();
}</pre>
            </div>

            <!-- Seção 4: Checklist -->
            <div class="section">
                <h2 class="section-title">✅ Critérios de Aceite (Sprint 8)</h2>
                
                <ul class="checklist">
                    <li><span class="check">✅</span> Endpoint acessível via URL/AJAX</li>
                    <li><span class="check">✅</span> Requisições sem Token CSRF são rejeitadas (403)</li>
                    <li><span class="check">✅</span> Retorno em JSON válido</li>
                    <li><span class="check">✅</span> Integração com frontend do Michel</li>
                    <li><span class="check">✅</span> Token CSRF via <code>Joomla.getOptions('csrf.token')</code></li>
                </ul>
            </div>

            <!-- Seção 5: Como Testar na Prática -->
            <div class="section">
                <h2 class="section-title">🧪 Como Testar na Prática</h2>
                
                <div class="warning-box">
                    <strong>Para testar a integração completa:</strong><br>
                    1. Acesse o admin do Joomla<br>
                    2. Vá em <strong>SP LMS → Lições → Editar uma lição</strong><br>
                    3. Use os botões da toolbar de IA (Revisar/Resumir/Reescrever)<br>
                    4. O texto do editor será processado pela Groq API
                </div>
            </div>
        </div>
    </div>

    <script>
        const baseUrl = '../index.php?option=com_splms&task=lesson.callAI';

        async function testWithoutToken() {
            const resultBox = document.getElementById('result-csrf');
            resultBox.style.display = 'block';
            resultBox.className = 'result-box';
            resultBox.textContent = '⏳ Enviando requisição SEM token...';

            try {
                const response = await fetch(baseUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'texto=Teste&acao=revisar'
                });

                const text = await response.text();
                const passed = response.status === 403;
                resultBox.className = 'result-box ' + (passed ? 'result-success' : 'result-error');
                resultBox.textContent = `HTTP Status: ${response.status}\n\n${passed ? '✅ PASSOU! Requisição sem token foi corretamente rejeitada (403).' : '❌ FALHOU! Esperava status 403.'}\n\nResposta:\n${text}`;
            } catch (e) {
                resultBox.className = 'result-box result-error';
                resultBox.textContent = '❌ Erro de rede: ' + e.message;
            }
        }
    </script>
</body>
</html>
