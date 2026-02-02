<?php
define('_JEXEC', 1);

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

// --- INTERNAL REQUEST HANDLER ---
// Checks if this is a POST request from the tool itself and executes the controller logic directly.
// This avoids issues with AJAX calls to /administrator which might trigger login redirects or session mismatches.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Define JSON header immediately
    header('Content-Type: application/json');
    
    try {
        // Load the Controller
        $controllerPath = JPATH_BASE . '/components/com_splms/controllers/lesson.php';
        if (!file_exists($controllerPath)) {
            throw new Exception('Controller not found at: ' . $controllerPath);
        }
        require_once $controllerPath;

        if (!class_exists('SplmsControllerLesson')) {
            throw new Exception('Controller Class SplmsControllerLesson not found.');
        }

        // Initialize Controller
        $config = ['base_path' => JPATH_BASE . '/components/com_splms'];
        $controller = new SplmsControllerLesson($config);
        
        // Execute uploadPDF directly
        // Note: The controller method "uploadPDF" echoes JSON and dies. 
        // We capture it just in case, but usually it outputs directly.
        ob_start();
        $controller->uploadPDF();
        $output = ob_get_clean();
        
        echo $output;
        exit;

    } catch (Exception $e) {
        // Fallback error handler
        echo json_encode(['success' => false, 'message' => 'Internal Test Error: ' . $e->getMessage()]);
        exit;
    }
}

$token = \Joomla\CMS\Session\Session::getFormToken();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guideway LMS - Teste de Quiz IA</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', system-ui, sans-serif; background: #1a1a2e; color: #e2e8f0; min-height: 100vh; }
        
        /* Layout Structure */
        .container { max-width: 1000px; margin: 0 auto; padding: 20px; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 1px solid #0f3460; }
        .header h1 { font-size: 24px; color: #e94560; font-weight: 700; }
        .header h1 span { color: #a0aec0; font-weight: 400; font-size: 18px; }
        
        .back-btn { background: #16213e; color: #a0aec0; text-decoration: none; padding: 10px 20px; border-radius: 8px; font-size: 14px; font-weight: 500; transition: all 0.2s; border: 1px solid #0f3460; }
        .back-btn:hover { background: #0f3460; color: #fff; border-color: #e94560; }
        
        /* Main Layout Grid */
        .main-grid { display: grid; grid-template-columns: 350px 1fr; gap: 30px; }
        
        /* Cards */
        .card { background: #16213e; border-radius: 16px; padding: 25px; border: 1px solid #0f3460; box-shadow: 0 4px 20px rgba(0,0,0,0.2); }
        .card-title { font-size: 16px; color: #fff; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; font-weight: 600; }
        
        /* Form Elements */
        .form-group { margin-bottom: 20px; }
        .form-label { display: block; margin-bottom: 8px; color: #a0aec0; font-size: 13px; font-weight: 500; }
        
        .form-control, .form-select { width: 100%; background: #0f3460; border: 1px solid #2a2a4a; color: #fff; padding: 12px; border-radius: 8px; font-size: 14px; transition: border-color 0.2s; font-family: inherit; }
        .form-control:focus, .form-select:focus { outline: none; border-color: #e94560; }
        
        /* Custom File Input */
        .file-drop-area { position: relative; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 30px 20px; border: 2px dashed #2a2a4a; border-radius: 12px; background: rgba(15, 52, 96, 0.5); transition: all 0.2s; text-align: center; cursor: pointer; }
        .file-drop-area:hover { border-color: #e94560; background: rgba(233, 69, 96, 0.05); }
        .file-drop-area.has-file { border-color: #48bb78; background: rgba(72, 187, 120, 0.05); }
        .file-icon { font-size: 24px; margin-bottom: 10px; color: #a0aec0; }
        .file-msg { color: #a0aec0; font-size: 13px; }
        .file-input { position: absolute; inset: 0; opacity: 0; cursor: pointer; width: 100%; height: 100%; }
        
        /* Submit Button */
        .btn-submit { background: linear-gradient(135deg, #e94560 0%, #c5304a 100%); color: white; border: none; padding: 14px; border-radius: 8px; font-weight: 600; width: 100%; cursor: pointer; font-size: 15px; margin-top: 10px; transition: transform 0.2s, box-shadow 0.2s; box-shadow: 0 4px 15px rgba(233, 69, 96, 0.3); }
        .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(233, 69, 96, 0.4); }
        .btn-submit:disabled { opacity: 0.7; cursor: not-allowed; transform: none; }
        
        /* Results Area */
        .results-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .badge-count { background: #e94560; color: white; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; }
        
        .question-card { background: #1a1a2e; border: 1px solid #2a2a4a; border-radius: 12px; padding: 20px; margin-bottom: 15px; position: relative; overflow: hidden; transition: all 0.2s; }
        .question-card:hover { border-color: #4a4a7a; }
        /* Badge de questão correta ao lado */
        .question-number { position: absolute; top: 0; left: 0; background: #0f3460; color: #a0aec0; padding: 5px 12px; border-bottom-right-radius: 12px; font-size: 12px; font-weight: 600; }
        
        .q-text { font-size: 16px; color: #fff; margin: 25px 0 15px; line-height: 1.5; font-weight: 500; }
        
        .options-grid { display: grid; gap: 8px; }
        .option-item { display: flex; align-items: center; padding: 10px 15px; background: rgba(15, 52, 96, 0.3); border-radius: 8px; border: 1px solid transparent; color: #cbd5e0; font-size: 14px; }
        .option-marker { width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; background: rgba(255,255,255,0.1); border-radius: 50%; margin-right: 12px; font-size: 12px; font-weight: 600; }
        
        .option-item.correct { background: rgba(72, 187, 120, 0.15); border-color: #48bb78; color: #48bb78; }
        .option-item.correct .option-marker { background: #48bb78; color: #0f3460; }
        
        /* JSON Toggle */
        .json-toggle { background: none; border: none; color: #a0aec0; cursor: pointer; font-size: 12px; display: flex; align-items: center; gap: 5px; margin-top: 20px; }
        .json-toggle:hover { color: #e94560; }
        .json-container { margin-top: 15px; display: none; }
        .json-box { background: #0a0a15; padding: 20px; border-radius: 8px; color: #48bb78; font-family: monospace; font-size: 12px; overflow-x: auto; border: 1px solid #2a2a4a; white-space: pre-wrap; }
        
        /* States */
        .empty-state { text-align: center; padding: 60px 20px; color: #a0aec0; }
        .empty-icon { font-size: 48px; margin-bottom: 15px; opacity: 0.5; }
        
        .loading-state { text-align: center; padding: 60px 20px; display: none; }
        .spinner { width: 40px; height: 40px; border: 3px solid rgba(233, 69, 96, 0.3); border-radius: 50%; border-top-color: #e94560; animation: spin 1s ease-in-out infinite; margin: 0 auto 20px; }
        @keyframes spin { to { transform: rotate(360deg); } }

        @media (max-width: 800px) {
            .main-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <header class="header">
            <h1>Guideway LMS <span>/ Gerador de Quiz IA</span></h1>
            <a href="index.php" class="back-btn">← Voltar ao Dashboard</a>
        </header>

        <div class="main-grid">
            <!-- Sidebar / Settings -->
            <aside>
                <form id="quizForm">
                    <input type="hidden" name="<?php echo $token; ?>" value="1" />
                    <input type="hidden" name="option" value="com_splms" />
                    <input type="hidden" name="task" value="lesson.uploadPDF" />

                    <div class="card">
                        <h3 class="card-title">⚙️ Configurações</h3>
                        
                        <div class="form-group">
                            <label class="form-label">Dificuldade</label>
                            <select name="gw_ai_difficulty" class="form-select">
                                <option value="facil">Iniciante (Fácil)</option>
                                <option value="medio" selected>Intermediário (Médio)</option>
                                <option value="dificil">Avançado (Difícil)</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Quantidade de Questões</label>
                            <select name="gw_ai_qcount" class="form-select">
                                <option value="3">3 Questões</option>
                                <option value="5" selected>5 Questões</option>
                                <option value="10">10 Questões</option>
                                <option value="15">15 Questões (Pode demorar)</option>
                                <option value="20">20 Questões (Máximo)</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Arquivo Fonte (PDF)</label>
                            <div class="file-drop-area" id="dropZone">
                                <div class="file-icon">📄</div>
                                <div class="file-msg">Clique ou arraste seu PDF</div>
                                <div id="fileName" style="font-size: 12px; margin-top: 5px; color: #e94560; font-weight: 600;"></div>
                                <input class="file-input" type="file" name="gw_ai_file" accept=".pdf" required>
                            </div>
                        </div>

                        <button type="submit" class="btn-submit" id="submitBtn">
                            Gerar Quiz 
                        </button>
                    </div>
                </form>

                <div class="card" style="margin-top: 20px; border-left: 3px solid #ff9f43;">
                    <h3 class="card-title" style="font-size: 14px; margin-bottom: 10px;">💡 Dica</h3>
                    <p style="font-size: 13px; color: #cbd5e0; line-height: 1.5;">O texto é extraído do PDF e enviado para a IA. Certifique-se de que o PDF tenha texto selecionável (não seja apenas imagem).</p>
                </div>
            </aside>

            <!-- Main Content / Results -->
            <main>
                <div class="card" style="min-height: 500px;">
                    <div id="emptyState" class="empty-state">
                        <div class="empty-icon">🧬</div>
                        <h3>Aguardando Geração</h3>
                        <p style="font-size: 14px; margin-top: 10px;">Configure as opções ao lado e envie um PDF para gerar o quiz.</p>
                    </div>

                    <div id="loadingState" class="loading-state">
                        <div class="spinner"></div>
                        <h3>Analisando Conteúdo...</h3>
                        <p style="color: #a0aec0; font-size: 13px; margin-top: 10px;">Isso pode levar alguns segundos. A IA está lendo o PDF e criando as questões.</p>
                    </div>

                    <div id="resultsContent" style="display: none;">
                        <div class="results-header">
                            <h3 style="color: #fff; font-size: 18px;">Questões Geradas</h3>
                            <span class="badge-count" id="qCountBadge">0 Questões</span>
                        </div>
                        
                        <div id="questionsList"></div>
                        
                        <!-- Debug / JSON Section -->
                        <button type="button" class="json-toggle" onclick="toggleJson()">
                            <span id="jsonIcon">▶</span> Ver Resposta JSON (Debug)
                        </button>
                        <div class="json-container" id="jsonContainer">
                            <pre class="json-box" id="jsonBox"></pre>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        // File Upload UX
        const dropZone = document.getElementById('dropZone');
        const fileInput = dropZone.querySelector('.file-input');
        const fileName = document.getElementById('fileName');
        const fileMsg = dropZone.querySelector('.file-msg');

        fileInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                fileName.textContent = this.files[0].name;
                fileMsg.style.display = 'none';
                dropZone.classList.add('has-file');
                dropZone.querySelector('.file-icon').textContent = '✅';
            }
        });

        // JSON Toggle
        function toggleJson() {
            const container = document.getElementById('jsonContainer');
            const icon = document.getElementById('jsonIcon');
            if (container.style.display === 'none' || container.style.display === '') {
                container.style.display = 'block';
                icon.textContent = '▼';
            } else {
                container.style.display = 'none';
                icon.textContent = '▶';
            }
        }

        // Form Submit Logic
        document.getElementById('quizForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const btn = document.getElementById('submitBtn');
            const emptyState = document.getElementById('emptyState');
            const loadingState = document.getElementById('loadingState');
            const resultsContent = document.getElementById('resultsContent');
            const questionsList = document.getElementById('questionsList');
            const jsonBox = document.getElementById('jsonBox');
            
            // UI State: Loading
            btn.disabled = true;
            btn.textContent = 'Processando...';
            emptyState.style.display = 'none';
            resultsContent.style.display = 'none';
            loadingState.style.display = 'block';
            
            try {
                const formData = new FormData(this);
                // We send the request to specific script itself, handled by the PHP block at the top
                const response = await fetch('', {
                    method: 'POST',
                    body: formData
                });
                
                const text = await response.text();
                let json;
                
                try {
                    json = JSON.parse(text);
                } catch(e) {
                    throw new Error('Erro ao processar resposta do servidor. Resposta bruta: ' + text.substring(0, 100));
                }

                loadingState.style.display = 'none';
                resultsContent.style.display = 'block';
                jsonBox.textContent = JSON.stringify(json, null, 2);

                if (json.success && json.data) {
                    let questions = [];
                    // Handle if data is string JSON or object
                    if (typeof json.data === 'string') {
                        try {
                            questions = JSON.parse(json.data);
                        } catch (e) {
                            questionsList.innerHTML = `<div style="color:#e94560; padding:20px; border:1px solid #e94560; border-radius:8px;">
                                ⚠️ <strong>Aviso:</strong> A IA retornou texto mas não está no formato JSON esperado. <br><br> ${json.data}
                            </div>`;
                            return;
                        }
                    } else {
                        questions = json.data;
                    }

                    if (Array.isArray(questions)) {
                        document.getElementById('qCountBadge').textContent = questions.length + ' Questões';
                        questionsList.innerHTML = '';
                        
                        questions.forEach((q, index) => {
                            const letters = ['A', 'B', 'C', 'D', 'E'];
                            
                            let optionsHtml = '<div class="options-grid">';
                            if (q.options && Array.isArray(q.options)) {
                                q.options.forEach((opt, idx) => {
                                    const isCorrect = idx === q.correct_answer;
                                    const letter = letters[idx] || '?';
                                    optionsHtml += `
                                        <div class="option-item ${isCorrect ? 'correct' : ''}">
                                            <div class="option-marker">${letter}</div>
                                            <span>${opt}</span>
                                            ${isCorrect ? '<span style="margin-left:auto; font-size:12px;">✅ Resposta Correta</span>' : ''}
                                        </div>
                                    `;
                                });
                            }
                            optionsHtml += '</div>';

                            const html = `
                                <div class="question-card">
                                    <div class="question-number">Questão ${index + 1}</div>
                                    <h4 class="q-text">${q.question}</h4>
                                    ${optionsHtml}
                                </div>
                            `;
                            questionsList.insertAdjacentHTML('beforeend', html);
                        });
                    }
                } else {
                    questionsList.innerHTML = `<div style="color:#e94560; text-align:center; padding:20px;">
                        <h3>❌ Ocorreu um erro</h3>
                        <p>${json.message || 'Erro desconhecido'}</p>
                    </div>`;
                }

            } catch (error) {
                loadingState.style.display = 'none';
                resultsContent.style.display = 'block';
                questionsList.innerHTML = `<div style="color:#e94560; text-align:center;">❌ Erro na requisição: ${error.message}</div>`;
                jsonBox.textContent = error.stack;
            } finally {
                btn.disabled = false;
                btn.textContent = 'Gerar Quiz ✨';
            }
        });
    </script>
</body>
</html>
