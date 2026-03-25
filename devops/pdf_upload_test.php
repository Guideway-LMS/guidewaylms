<?php
require_once __DIR__ . '/auth.php';

// define('_JEXEC', 1);

if (file_exists(__DIR__ . '/defines.php')) {
	include_once __DIR__ . '/defines.php';
}

// Bootstrap Joomla (Administrator Context)
if (!defined('_JDEFINES')) {
    define('JPATH_BASE', dirname(__DIR__) . '/administrator');
    require_once JPATH_BASE . '/includes/defines.php';
}
require_once JPATH_BASE . '/includes/framework.php';

// Boot the DI Container
$container = \Joomla\CMS\Factory::getContainer();

// Register the application in the container
$container->alias('session.web', 'session.web.administrator')
    ->alias('session', 'session.web.administrator')
    ->alias('JSession', 'session.web.administrator')
    ->alias(\Joomla\CMS\Session\Session::class, 'session.web.administrator')
    ->alias('Joomla\\Session\\SessionInterface', 'session.web.administrator');

// Instantiate the Application (Administrator)
$app = $container->get(\Joomla\CMS\Application\AdministratorApplication::class);
\Joomla\CMS\Factory::$application = $app;

$token = \Joomla\CMS\Session\Session::getFormToken();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teste Upload PDF</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, system-ui, sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 20px; min-height: 100vh; }
        .container { max-width: 900px; margin: 0 auto; }
        .header { background: #2c3e50; color: white; padding: 20px; border-radius: 12px 12px 0 0; display: flex; justify-content: space-between; align-items: center; }
        .header h1 { font-size: 24px; }
        .back-btn { color: white; text-decoration: none; background: rgba(255,255,255,0.2); padding: 8px 16px; border-radius: 6px; transition: background 0.2s; }
        .back-btn:hover { background: rgba(255,255,255,0.3); }
        
        .main-card { background: white; padding: 30px; border-radius: 0 0 12px 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); }
        
        .section { margin-bottom: 30px; }
        .section-title { font-size: 18px; color: #2c3e50; margin-bottom: 15px; padding-bottom: 10px; border-bottom: 2px solid #e2e8f0; }
        
        .form-group { margin-bottom: 20px; }
        .form-label { display: block; margin-bottom: 8px; font-weight: 600; color: #4a5568; }
        
        /* File Input Styling */
        .file-drop-area { position: relative; display: flex; align-items: center; width: 100%; max-width: 100%; padding: 25px; border: 2px dashed rgba(255, 255, 255, 0.4); border-radius: 6px; transition: 0.2s; background-color: #f7fafc; border-color: #cbd5e0; }
        .file-msg { font-size: 16px; font-weight: 500; color: #718096; width: 100%; text-align: center; }
        .file-input { position: absolute; left: 0; top: 0; height: 100%; width: 100%; cursor: pointer; opacity: 0; }
        .file-drop-area:hover { background-color: #edf2f7; border-color: #a0aec0; }
        
        .btn-submit { background: #3182ce; color: white; border: none; padding: 12px 24px; border-radius: 6px; font-size: 16px; font-weight: 600; cursor: pointer; transition: background 0.2s; width: 100%; }
        .btn-submit:hover { background: #2c5282; }
        .btn-submit:disabled { background: #a0aec0; cursor: not-allowed; }
        
        #response-container { margin-top: 20px; display: none; }
        .response-box { background: #2d3748; color: #e2e8f0; padding: 20px; border-radius: 8px; overflow-x: auto; font-family: monospace; font-size: 13px; line-height: 1.5; border: 1px solid #4a5568; }
        
        .info-box { background: #ebf8ff; border-left: 4px solid #3182ce; padding: 15px; margin-bottom: 25px; font-size: 14px; border-radius: 0 8px 8px 0; color: #2c5282; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📄 Teste Upload PDF (Backend)</h1>
            <a href="index.php" class="back-btn">← Voltar</a>
        </div>
        
        <div class="main-card">
            <div class="info-box">
                <strong>ℹ️ Limites:</strong> Arquivos PDF até 5MB. O texto é extraído usando <code>smalot/pdfparser</code>.
            </div>

            <div class="section">
                <h2 class="section-title">📤 Enviar Arquivo</h2>
                
                <form id="uploadForm" enctype="multipart/form-data">
                    <input type="hidden" name="<?php echo $token; ?>" value="1" />
                    <input type="hidden" name="option" value="com_splms" />
                    <input type="hidden" name="task" value="lesson.uploadPDF" />
                    
                    <div class="form-group">
                        <div class="file-drop-area">
                            <span class="file-msg">Arraste seu PDF aqui ou clique para selecionar</span>
                            <input class="file-input" type="file" name="gw_ai_file" accept=".pdf" required>
                        </div>
                        <div id="selected-file-name" style="margin-top: 10px; font-size: 14px; color: #2c5282; display: none;">Arquivo selecionado: <b></b></div>
                    </div>
                    
                    <button type="submit" class="btn-submit" id="submitBtn">Extrair Texto do PDF 🚀</button>
                </form>
            </div>

            <div id="response-container">
                <h2 class="section-title">🔍 Resposta do Servidor</h2>
                <pre id="response" class="response-box">Aguardando envio...</pre>
            </div>
            
            <div class="section" style="margin-top: 40px;">
                <h2 class="section-title">📚 Como funciona este teste</h2>
                <div class="info-box">
                    <strong>Objetivo:</strong> Validar se o backend consegue receber um arquivo PDF, extrair seu texto e retornar para o frontend.
                </div>
                <p style="color: #4a5568; font-size: 14px; margin-bottom: 20px;">
                    1. O arquivo é enviado via AJAX para o endpoint <code>lesson.uploadPDF</code>.<br>
                    2. O backend valida: Tamanho (Max 5MB), Extensão (.pdf) e MIME Type.<br>
                    3. A biblioteca <code>Smalot\PdfParser</code> lê o binário e extrai o texto cru.<br>
                    4. O texto é limpo (remoção de caracteres de controle) e convertido para UTF-8.<br>
                    5. 🤖 <strong>IA Integrada:</strong> O texto vai para o <code>GuidewayAIHelper</code>. Se tiver prompt, usa a ação CUSTOM; se não, usa FORMATAR.<br>
                    6. O JSON retorna com <code>success: true</code> e o texto processado em <code>data</code>.
                </p>

                <h3 style="font-size: 16px; color: #2c3e50; margin-bottom: 10px;">🧪 Recomendações de Teste</h3>
                <ul style="list-style-type: none; font-size: 14px; color: #4a5568; line-height: 1.6;">
                    <li>✅ <strong>Teste Feliz:</strong> Envie um PDF simples com texto selecionável. Deve retornar o texto extraído.</li>
                    <li>❌ <strong>MIME Type:</strong> Tente enviar uma imagem (.jpg) renomeada para .pdf. Deve falhar.</li>
                    <li>❌ <strong>Limite de 5MB:</strong> Tente enviar um PDF muito grande. Deve retornar erro de tamanho.</li>
                    <li>⚠️ <strong>PDF Digitalizado:</strong> Envie um PDF que é apenas imagem ("scaneado"). O parser deve retornar vazio, mas sem erro fatal.</li>
                </ul>
            </div>
        </div>
    </div>

    <script>
        // File input UX
        const fileInput = document.querySelector('.file-input');
        const fileMsg = document.querySelector('.file-msg');
        const fileNameDiv = document.getElementById('selected-file-name');
        
        fileInput.addEventListener('change', function() {
            if (this.files && this.files.length > 0) {
                const name = this.files[0].name;
                fileNameDiv.style.display = 'block';
                fileNameDiv.querySelector('b').textContent = name;
                fileMsg.textContent = 'Arquivo selecionado!';
            }
        });

        // Form Submit
        document.getElementById('uploadForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const btn = document.getElementById('submitBtn');
            const responseContainer = document.getElementById('response-container');
            const responseDiv = document.getElementById('response');
            
            btn.disabled = true;
            btn.textContent = 'Processando... ⏳';
            responseContainer.style.display = 'block';
            responseDiv.textContent = 'Enviando arquivo e extraindo texto...';
            
            const formData = new FormData(this);
            
            try {
                const response = await fetch('../administrator/index.php?option=com_splms&task=lesson.uploadPDF', {
                    method: 'POST',
                    body: formData
                });
                
                const text = await response.text();
                try {
                    const json = JSON.parse(text);
                    
                    if(json.success) {
                        // Mostrar apenas o texto extraído
                        responseDiv.textContent = json.data;
                        responseDiv.style.borderColor = '#48bb78'; // Green border
                        document.querySelector('#response-container h2').textContent = '📄 Conteúdo Extraído';
                    } else {
                        // Mostrar erro/debug se falhar
                        responseDiv.textContent = JSON.stringify(json, null, 2);
                        responseDiv.style.borderColor = '#f56565'; // Red border
                        document.querySelector('#response-container h2').textContent = '❌ Erro no Processamento';
                    }
                } catch (e) {
                    responseDiv.textContent = 'Erro ao fazer parse JSON (Resposta bruta):\n' + text;
                    responseDiv.style.borderColor = '#ed8936'; // Orange
                }
                
            } catch (err) {
                responseDiv.textContent = 'Erro na requisição: ' + err.message;
                responseDiv.style.borderColor = '#f56565';
            } finally {
                btn.disabled = false;
                btn.textContent = 'Extrair Texto do PDF 🚀';
            }
        });
    </script>
</body>
</html>
