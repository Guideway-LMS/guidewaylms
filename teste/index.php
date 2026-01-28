<?php
/**
 * Dashboard de Testes e Documentação - Guideway LMS
 * 
 * Interface unificada com abas para separar Testes e Documentação
 */

$readmePath = __DIR__ . '/README.md';
$markdown = file_exists($readmePath) ? file_get_contents($readmePath) : '';

function slugify($text) {
    $text = mb_strtolower($text, 'UTF-8');
    $text = str_replace(
        ['á','à','ã','â','ä','é','è','ê','ë','í','ì','î','ï','ó','ò','õ','ô','ö','ú','ù','û','ü','ç','ñ'],
        ['a','a','a','a','a','e','e','e','e','i','i','i','i','o','o','o','o','o','u','u','u','u','c','n'],
        $text
    );
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    return trim(preg_replace('/-+/', '-', $text), '-');
}

function convertMarkdownToHTML($text) {
    $text = preg_replace_callback('/^# (.*?)$/m', fn($m) => "<h1 id=\"".slugify($m[1])."\">{$m[1]}</h1>", $text);
    $text = preg_replace_callback('/^## (.*?)$/m', fn($m) => "<h2 id=\"".slugify($m[1])."\">{$m[1]}</h2>", $text);
    $text = preg_replace_callback('/^### (.*?)$/m', fn($m) => "<h3 id=\"".slugify($m[1])."\">{$m[1]}</h3>", $text);
    $text = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $text);
    $text = preg_replace('/\*(.*?)\*/', '<em>$1</em>', $text);
    $text = preg_replace('/```(.*?)```/s', '<pre><code>$1</code></pre>', $text);
    $text = preg_replace('/`(.*?)`/', '<code>$1</code>', $text);
    $text = preg_replace('/\[(.*?)\]\((.*?)\)/', '<a href="$2">$1</a>', $text);
    $text = preg_replace('/^- (.*?)$/m', '<li>$1</li>', $text);
    $text = nl2br($text);
    return $text;
}

$html = convertMarkdownToHTML($markdown);

// Extract Composer Guide separately
$composerGuideMarkdown = '';
if (preg_match('/## 📦 Guia de Instalação de Dependências \(Composer\)(.*?)(?:^## |\Z)/sm', $markdown, $matches)) {
    $composerGuideMarkdown = "## 📦 Guia de Instalação de Dependências (Composer)\n" . trim($matches[1]);
}
$composerGuideHtml = convertMarkdownToHTML($composerGuideMarkdown);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guideway LMS - DevTools</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, system-ui, sans-serif; background: #1a1a2e; color: #eee; min-height: 100vh; }
        
        /* Header */
        .header { background: linear-gradient(135deg, #16213e 0%, #1a1a2e 100%); padding: 30px 40px; border-bottom: 1px solid #0f3460; }
        .header h1 { font-size: 28px; color: #e94560; margin-bottom: 5px; }
        .header p { color: #a0aec0; font-size: 14px; }
        
        /* Tabs */
        .tabs { display: flex; background: #16213e; border-bottom: 2px solid #0f3460; padding: 0 40px; }
        .tab { padding: 15px 25px; cursor: pointer; color: #a0aec0; font-weight: 600; border-bottom: 3px solid transparent; transition: all 0.2s; }
        .tab:hover { color: #e94560; }
        .tab.active { color: #e94560; border-bottom-color: #e94560; background: rgba(233, 69, 96, 0.1); }
        
        /* Content */
        .content { padding: 40px; max-width: 1200px; margin: 0 auto; }
        .tab-content { display: none; }
        .tab-content.active { display: block; }
        
        /* Cards Grid */
        .cards-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .section-title { font-size: 18px; color: #e94560; margin-bottom: 15px; padding-bottom: 10px; border-bottom: 1px solid #0f3460; }
        
        /* Card */
        .card { background: #16213e; border-radius: 12px; padding: 20px; border: 1px solid #0f3460; transition: all 0.2s; text-decoration: none; color: inherit; display: block; position: relative; overflow: hidden; }
        .card:hover { transform: translateY(-3px); border-color: #e94560; box-shadow: 0 10px 30px rgba(233, 69, 96, 0.2); }
        .card h3 { font-size: 16px; margin-bottom: 8px; color: #fff; }
        .card p { font-size: 13px; color: #a0aec0; }
        .card-badge { position: absolute; top: 0; right: 0; background: #e94560; color: white; font-size: 10px; padding: 4px 10px; border-bottom-left-radius: 8px; }
        .card-icon { font-size: 24px; margin-bottom: 10px; }
        
        /* Card variations */
        .card.devops { border-left: 3px solid #48bb78; }
        .card.ai { border-left: 3px solid #ed8936; }
        .card.db { border-left: 3px solid #4299e1; }
        .card.doc { border-left: 3px solid #9f7aea; }
        
        /* Documentation content */
        .doc-content { background: #16213e; border-radius: 12px; padding: 30px; border: 1px solid #0f3460; }
        .doc-content h1 { color: #e94560; font-size: 24px; margin: 25px 0 15px; padding-bottom: 10px; border-bottom: 1px solid #0f3460; }
        .doc-content h2 { color: #ed8936; font-size: 20px; margin: 20px 0 10px; }
        .doc-content h3 { color: #4299e1; font-size: 16px; margin: 15px 0 8px; }
        .doc-content code { background: #0f3460; padding: 2px 8px; border-radius: 4px; font-size: 13px; color: #48bb78; }
        .doc-content pre { background: #0a0a15; padding: 15px; border-radius: 8px; overflow-x: auto; margin: 15px 0; }
        .doc-content pre code { background: none; color: #a0aec0; }
        .doc-content a { color: #e94560; }
        .doc-content ul, .doc-content ol { margin: 10px 0 10px 25px; }
        .doc-content li { margin: 5px 0; color: #a0aec0; }
        
        /* Info box */
        .info-box { background: rgba(66, 153, 225, 0.1); border-left: 4px solid #4299e1; padding: 15px 20px; border-radius: 0 8px 8px 0; margin-bottom: 25px; }
        .info-box h4 { color: #4299e1; margin-bottom: 5px; }
        .info-box p { color: #a0aec0; font-size: 14px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>🚀 Guideway LMS DevTools</h1>
        <p>Ferramentas de Desenvolvimento, Testes e Documentação</p>
    </div>

    <div class="tabs">
        <div class="tab active" onclick="showTab('tests')">🧪 Testes</div>
        <div class="tab" onclick="showTab('docs')">📚 Documentação</div>
        <div class="tab" onclick="showTab('project')">📖 Sobre o Projeto</div>
    </div>

    <div class="content">
        <!-- TAB: Testes -->
        <div id="tab-tests" class="tab-content active">
            <div class="info-box">
                <h4>ℹ️ Ambiente de Testes</h4>
                <p>Scripts de verificação e debug para desenvolvimento. Não usar em produção.</p>
            </div>

            <h2 class="section-title">🗄️ Banco de Dados & Model</h2>
            <div class="cards-grid">
                <a href="test_announcements_check.php" class="card db">
                    <div class="card-icon">🧪</div>
                    <h3>Teste de Avisos</h3>
                    <p>Verifica o Model de Announcements</p>
                </a>
                <a href="create_test_data.php" class="card db">
                    <div class="card-icon">🛠️</div>
                    <h3>Criar Dados de Teste</h3>
                    <p>Gera matrícula de teste</p>
                </a>
                <a href="debug_db_columns.php" class="card db">
                    <div class="card-icon">🔍</div>
                    <h3>Debug Schema</h3>
                    <p>Lista colunas da tabela</p>
                </a>
            </div>

            <h2 class="section-title">🤖 Inteligência Artificial</h2>
            <div class="cards-grid">
                <a href="groq_smoke_test.php" class="card ai">
                    <div class="card-icon">🔌</div>
                    <h3>Groq Smoke Test</h3>
                    <p>Teste de conexão com a API</p>
                </a>
                <a href="groq_prompt_test.php" class="card ai">
                    <div class="card-badge">Consome Tokens 🪙</div>
                    <div class="card-icon">📝</div>
                    <h3>Teste de Prompts</h3>
                    <p>Testar ações: Revisar, Resumir, Reescrever</p>
                </a>
                <a href="test_callai_endpoint.php" class="card ai">
                    <div class="card-icon">🔐</div>
                    <h3>Teste Endpoint callAI</h3>
                    <p>Verificar segurança CSRF do controller</p>
                </a>
                <a href="pdf_upload_test.php" class="card ai">
                    <div class="card-icon">📄</div>
                    <h3>Teste Upload PDF</h3>
                    <p>Validar upload e parsing de PDF</p>
                </a>
                <a href="quiz_gen_test.php" class="card ai">
                    <div class="card-icon">🎓</div>
                    <h3>Gerador de Quiz</h3>
                    <p>IA gera questões a partir de PDF</p>
                </a>
            </div>

            <h2 class="section-title">⚙️ DevOps & Infraestrutura</h2>
            <div class="cards-grid">
                <a href="dump_manager.php" class="card devops">
                    <div class="card-icon">📦</div>
                    <h3>Gerenciador de Dumps</h3>
                    <p>Gerar e baixar backups do banco</p>
                </a>
            </div>
        </div>

        <!-- TAB: Documentação -->
        <div id="tab-docs" class="tab-content">
            <div class="info-box">
                <h4>📚 Documentação Técnica</h4>
                <p>Documentação dos scripts de teste e integrações.</p>
            </div>

            <h2 class="section-title">📄 Documentações Disponíveis</h2>
            <div class="cards-grid">
                <a href="#" onclick="showTab('readme'); return false;" class="card doc">
                    <div class="card-icon">📋</div>
                    <h3>README - Scripts de Teste</h3>
                    <p>Documentação completa dos scripts</p>
                </a>
                <a href="#integracao-groq-api-documentacao-tecnica" onclick="showTab('readme'); return false;" class="card doc">
                    <div class="card-icon">🤖</div>
                    <h3>Integração Groq API</h3>
                    <p>Configuração e uso da API de IA</p>
                </a>
                <a href="#guia-de-instalacao-de-dependencias-composer" onclick="showTab('composer'); return false;" class="card doc">
                    <div class="card-icon">📦</div>
                    <h3>Instalação PDF Parser</h3>
                    <p>Guia manual do Composer</p>
                </a>
            </div>
        </div>

        <!-- TAB: Composer Guide (Isolated) -->
        <div id="tab-composer" class="tab-content">
            <div class="doc-content">
                <a href="#" onclick="showTab('docs'); return false;" style="display:inline-block; margin-bottom:20px;">← Voltar para Documentação</a>
                <?php echo $composerGuideHtml; ?>
            </div>
        </div>

        <!-- TAB: Sobre o Projeto -->
        <div id="tab-project" class="tab-content">
            <div class="doc-content">
                <h1>📖 Guideway LMS</h1>
                <p>Sistema de Gerenciamento de Aprendizado desenvolvido sobre Joomla + SP LMS.</p>
                
                <h2>🏗️ Arquitetura</h2>
                <ul>
                    <li><strong>Framework:</strong> Joomla 4.x</li>
                    <li><strong>Componente Base:</strong> SP LMS (JoomShaper)</li>
                    <li><strong>Integração IA:</strong> Groq API (LLaMA 3.3 70B)</li>
                    <li><strong>Banco de Dados:</strong> MariaDB</li>
                </ul>

                <h2>📁 Estrutura do Componente</h2>
                <pre><code>components/com_splms/
├── controllers/     # Controllers MVC
│   └── lesson.php   # Endpoint callAI()
├── helpers/
│   └── GuidewayAIHelper.php  # Lógica de IA
├── models/          # Models de dados
├── views/           # Views e templates
└── assets/          # CSS, JS, imagens</code></pre>

                <h2>🔌 Integrações Implementadas</h2>
                <h3>Sprint 7 - GuidewayAIHelper</h3>
                <ul>
                    <li>Método <code>processarTexto($texto, $acao)</code></li>
                    <li>Ações: revisar, resumir, reescrever</li>
                    <li>Conexão segura com Groq API via cURL</li>
                </ul>

                <h3>Sprint 8 - Endpoint AJAX</h3>
                <ul>
                    <li>Endpoint: <code>/index.php?option=com_splms&task=lesson.callAI</code></li>
                    <li>Verificação CSRF + autenticação</li>
                    <li>Retorno JSON padronizado</li>
                </ul>

                <h3>Integração Frontend (Michel)</h3>
                <ul>
                    <li>Arquivo: <code>administrator/.../assets/js/guideway_ai.js</code></li>
                    <li>Toolbar: <code>toolbar_ai.php</code> com botões Revisar/Resumir/Reescrever</li>
                    <li>Integração TinyMCE: Lê e escreve no editor</li>
                    <li>CSRF Token: Via <code>Joomla.getOptions('csrf.token')</code></li>
                </ul>

                <h2>👥 Equipe</h2>
                <p>Desenvolvido pela equipe Guideway LMS.</p>
            </div>
        </div>

        <!-- TAB: README (hidden, shown via docs) -->
        <div id="tab-readme" class="tab-content">
            <div class="doc-content">
                <?php echo $html; ?>
            </div>
        </div>
    </div>

    <script>
        function showTab(tabName) {
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
            document.querySelectorAll('.tab').forEach(el => el.classList.remove('active'));
            
            // Show selected tab
            document.getElementById('tab-' + tabName).classList.add('active');
            
            // Highlight tab button
            const tabIndex = {'tests': 0, 'docs': 1, 'project': 2, 'readme': 1, 'composer': 1}[tabName];
            document.querySelectorAll('.tab')[tabIndex].classList.add('active');
        }
    </script>
</body>
</html>
