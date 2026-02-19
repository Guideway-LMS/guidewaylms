<?php
/**
 * Dashboard de Testes e Documentação - Guideway LMS
 * 
 * Interface unificada com abas para separar Testes e Documentação
 */

// Bootstrap do Joomla (necessário para Factory::getDbo na aba Quiz)
define('_JEXEC', 1);
define('JPATH_BASE', dirname(__DIR__));
require_once JPATH_BASE . '/includes/defines.php';
require_once JPATH_BASE . '/includes/framework.php';

use Joomla\CMS\Factory;

$readmePath = __DIR__ . '/README.md';
$markdown = file_exists($readmePath) ? file_get_contents($readmePath) : '';

$statusPath = __DIR__ . '/PROJECT_STATUS.md';
$statusMarkdown = file_exists($statusPath) ? file_get_contents($statusPath) : '';;

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
$statusHtml = convertMarkdownToHTML($statusMarkdown);

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
        .card.quiz { border-left: 3px solid #f59e0b; }
        
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
        <div class="tab" onclick="showTab('status')">📊 Status</div>
        <div class="tab" onclick="showTab('quiz')">🎓 Quiz</div>
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

        <!-- TAB: StatusReport -->
        <div id="tab-status" class="tab-content">
            <div class="doc-content">
                <?php echo $statusHtml; ?>
            </div>
        </div>

        <!-- TAB: Quiz Integration -->
        <div id="tab-quiz" class="tab-content">
            <?php
            // Lógica de migração
            $migrationColumns = [
                'lesson_format' => ['type' => "VARCHAR(30) NOT NULL DEFAULT 'content'", 'desc' => 'Tipo de lição: content, quiz, assignment'],
                'quiz_id'       => ['type' => 'INT(11) NOT NULL DEFAULT 0', 'desc' => 'ID do quiz vinculado à lição'],
                'passing_score' => ['type' => 'INT(11) NOT NULL DEFAULT 0', 'desc' => 'Nota de corte personalizada (%)'],
                'is_optional'   => ['type' => 'TINYINT(1) NOT NULL DEFAULT 0', 'desc' => 'Se a lição é opcional no progresso'],
            ];

            $db = Factory::getDbo();
            $existingColumns = $db->getTableColumns('#__splms_lessons');
            $migrationResults = [];

            // Processar migração se solicitado
            if (isset($_POST['run_quiz_migration'])) {
                foreach ($migrationColumns as $colName => $colInfo) {
                    if (array_key_exists($colName, $existingColumns)) {
                        $migrationResults[$colName] = ['status' => 'skip', 'msg' => 'Já existe'];
                    } else {
                        try {
                            $sql = 'ALTER TABLE ' . $db->quoteName('#__splms_lessons') . ' ADD COLUMN ' . $db->quoteName($colName) . ' ' . $colInfo['type'];
                            $db->setQuery($sql);
                            $db->execute();
                            $migrationResults[$colName] = ['status' => 'ok', 'msg' => 'Criada com sucesso'];
                        } catch (Exception $e) {
                            $migrationResults[$colName] = ['status' => 'error', 'msg' => $e->getMessage()];
                        }
                    }
                }
                // Recarregar colunas
                $existingColumns = $db->getTableColumns('#__splms_lessons');
            }
            ?>

            <!-- Seção A: Migração -->
            <div class="info-box">
                <h4>🗄️ Migração de Banco de Dados</h4>
                <p>Colunas necessárias na tabela <code>#__splms_lessons</code> para as funcionalidades de Quiz.</p>
            </div>

            <div class="doc-content" style="margin-bottom: 30px;">
                <h2 style="color: #f59e0b; margin-top: 0;">Status das Colunas</h2>
                <table style="width: 100%; border-collapse: collapse; margin: 15px 0;">
                    <tr style="border-bottom: 2px solid #0f3460;">
                        <th style="padding: 12px; text-align: left; color: #f59e0b;">Coluna</th>
                        <th style="padding: 12px; text-align: left; color: #f59e0b;">Tipo</th>
                        <th style="padding: 12px; text-align: left; color: #f59e0b;">Descrição</th>
                        <th style="padding: 12px; text-align: center; color: #f59e0b;">Status</th>
                    </tr>
                    <?php foreach ($migrationColumns as $colName => $colInfo) : 
                        $exists = array_key_exists($colName, $existingColumns);
                        $icon = $exists ? '✅' : '❌';
                        $rowBg = $exists ? 'rgba(34,197,94,0.1)' : 'rgba(239,68,68,0.1)';
                    ?>
                    <tr style="border-bottom: 1px solid #0f3460; background: <?php echo $rowBg; ?>;">
                        <td style="padding: 10px; font-weight: 700;"><code><?php echo $colName; ?></code></td>
                        <td style="padding: 10px; font-size: 13px; color: #a0aec0;"><code><?php echo $colInfo['type']; ?></code></td>
                        <td style="padding: 10px; font-size: 13px; color: #a0aec0;"><?php echo $colInfo['desc']; ?></td>
                        <td style="padding: 10px; text-align: center; font-size: 20px;"><?php echo $icon; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </table>

                <?php if (!empty($migrationResults)) : ?>
                <div style="background: #0a0a15; border-radius: 8px; padding: 15px; margin: 15px 0;">
                    <h4 style="color: #48bb78; margin-bottom: 10px;">📋 Resultado da Migração</h4>
                    <?php foreach ($migrationResults as $col => $res) : 
                        $color = $res['status'] === 'ok' ? '#22c55e' : ($res['status'] === 'skip' ? '#f59e0b' : '#ef4444');
                        $icon = $res['status'] === 'ok' ? '✅' : ($res['status'] === 'skip' ? '⏭️' : '❌');
                    ?>
                    <p style="color: <?php echo $color; ?>; margin: 5px 0;">
                        <?php echo $icon; ?> <code><?php echo $col; ?></code> — <?php echo htmlspecialchars($res['msg']); ?>
                    </p>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <?php 
                $allExist = true;
                foreach ($migrationColumns as $colName => $colInfo) {
                    if (!array_key_exists($colName, $existingColumns)) { $allExist = false; break; }
                }
                ?>

                <?php if ($allExist) : ?>
                <div style="background: rgba(34,197,94,0.15); border: 1px solid #22c55e; border-radius: 8px; padding: 15px; text-align: center;">
                    <p style="color: #22c55e; font-weight: 700; font-size: 16px;">✅ Todas as colunas já existem. Nenhuma migração necessária.</p>
                </div>
                <?php else : ?>
                <form method="POST" style="text-align: center; margin-top: 20px;">
                    <input type="hidden" name="run_quiz_migration" value="1">
                    <button type="submit" style="background: linear-gradient(135deg, #f59e0b, #d97706); color: #fff; border: none; padding: 14px 40px; border-radius: 10px; font-size: 16px; font-weight: 700; cursor: pointer; transition: all 0.2s; box-shadow: 0 4px 15px rgba(245,158,11,0.3);">
                        🚀 Aplicar Migração
                    </button>
                    <p style="color: #a0aec0; font-size: 12px; margin-top: 10px;">Apenas as colunas faltantes serão adicionadas.</p>
                </form>
                <?php endif; ?>
            </div>

            <!-- Seção B: Documentação -->
            <div class="info-box">
                <h4>📖 Documentação da Implementação</h4>
                <p>Resumo técnico dos arquivos modificados e funções chave da integração Quiz → Progresso.</p>
            </div>

            <div class="doc-content">
                <h2 style="color: #f59e0b; margin-top: 0;">Arquivos Modificados</h2>
                <table style="width: 100%; border-collapse: collapse; margin: 15px 0;">
                    <tr style="border-bottom: 2px solid #0f3460;">
                        <th style="padding: 10px; text-align: left; color: #4299e1;">Arquivo</th>
                        <th style="padding: 10px; text-align: left; color: #4299e1;">Função</th>
                    </tr>
                    <tr style="border-bottom: 1px solid #0f3460;">
                        <td style="padding: 10px;"><code>views/quizquestion/view.html.php</code></td>
                        <td style="padding: 10px; color: #a0aec0;">View do quiz: resultado colorido (verde/vermelho), CSS, lógica de nota de corte</td>
                    </tr>
                    <tr style="border-bottom: 1px solid #0f3460;">
                        <td style="padding: 10px;"><code>layouts/course/content.php</code></td>
                        <td style="padding: 10px; color: #a0aec0;">Badges "★ Obrigatório" / "● Opcional" na lista de aulas</td>
                    </tr>
                    <tr style="border-bottom: 1px solid #0f3460;">
                        <td style="padding: 10px;"><code>templates/maestro/.../lesson/default.php</code></td>
                        <td style="padding: 10px; color: #a0aec0;">Seção Quiz na página de lição, botão "Iniciar Quiz", status de resultado</td>
                    </tr>
                    <tr style="border-bottom: 1px solid #0f3460;">
                        <td style="padding: 10px;"><code>controllers/quizquestions.php</code></td>
                        <td style="padding: 10px; color: #a0aec0;">Salva resultado do quiz e marca lição como concluída automaticamente</td>
                    </tr>
                    <tr style="border-bottom: 1px solid #0f3460;">
                        <td style="padding: 10px;"><code>models/course.php</code></td>
                        <td style="padding: 10px; color: #a0aec0;">Filtra lições opcionais do cálculo de progresso do curso</td>
                    </tr>
                    <tr style="border-bottom: 1px solid #0f3460;">
                        <td style="padding: 10px;"><code>models/forms/lesson.xml</code></td>
                        <td style="padding: 10px; color: #a0aec0;">Campos admin: lesson_format, quiz_id, passing_score, is_optional</td>
                    </tr>
                    <tr style="border-bottom: 1px solid #0f3460;">
                        <td style="padding: 10px;"><code>admin/models/quizquestion.php</code></td>
                        <td style="padding: 10px; color: #a0aec0;">Criação automática de lição ao salvar quiz no admin</td>
                    </tr>
                </table>

                <h2 style="color: #f59e0b;">Funções Chave</h2>
                <table style="width: 100%; border-collapse: collapse; margin: 15px 0;">
                    <tr style="border-bottom: 2px solid #0f3460;">
                        <th style="padding: 10px; text-align: left; color: #48bb78;">Função</th>
                        <th style="padding: 10px; text-align: left; color: #48bb78;">Arquivo</th>
                        <th style="padding: 10px; text-align: left; color: #48bb78;">Propósito</th>
                    </tr>
                    <tr style="border-bottom: 1px solid #0f3460;">
                        <td style="padding: 10px;"><code>displayScore()</code></td>
                        <td style="padding: 10px; color: #a0aec0;">view.html.php (JS)</td>
                        <td style="padding: 10px; color: #a0aec0;">Exibe resultado com cores verde/vermelha baseado na nota de corte</td>
                    </tr>
                    <tr style="border-bottom: 1px solid #0f3460;">
                        <td style="padding: 10px;"><code>insertScore()</code></td>
                        <td style="padding: 10px; color: #a0aec0;">view.html.php (JS)</td>
                        <td style="padding: 10px; color: #a0aec0;">Envia resultado via AJAX para o backend</td>
                    </tr>
                    <tr style="border-bottom: 1px solid #0f3460;">
                        <td style="padding: 10px;"><code>submit_result()</code></td>
                        <td style="padding: 10px; color: #a0aec0;">controllers/quizquestions.php</td>
                        <td style="padding: 10px; color: #a0aec0;">Salva no BD e marca lição como concluída se aprovado</td>
                    </tr>
                    <tr style="border-bottom: 1px solid #0f3460;">
                        <td style="padding: 10px;"><code>getCourseLessons()</code></td>
                        <td style="padding: 10px; color: #a0aec0;">models/course.php</td>
                        <td style="padding: 10px; color: #a0aec0;">Filtra lições opcionais do cálculo de progresso</td>
                    </tr>
                </table>

                <h2 style="color: #f59e0b;">Fluxo de Funcionamento</h2>
                <div style="background: #0a0a15; border-radius: 8px; padding: 20px; margin: 15px 0; font-family: monospace; font-size: 13px; color: #a0aec0; line-height: 1.8;">
                    <span style="color: #4299e1;">1.</span> Admin cria Quiz → Lição criada automaticamente com <code>lesson_format='quiz'</code><br>
                    <span style="color: #4299e1;">2.</span> Aluno acessa Lição → Vê botão "Iniciar Quiz" + nota de corte<br>
                    <span style="color: #4299e1;">3.</span> Aluno responde questões → JS calcula acertos<br>
                    <span style="color: #4299e1;">4.</span> <code>insertScore()</code> envia resultado via AJAX → <code>submit_result()</code><br>
                    <span style="color: #4299e1;">5.</span> Backend salva em <code>#__splms_quizresults</code><br>
                    <span style="color: #4299e1;">6.</span> Se aprovado → marca lição como concluída em <code>#__splms_lesson_completed</code><br>
                    <span style="color: #4299e1;">7.</span> <code>displayScore()</code> mostra resultado: <span style="color: #22c55e;">verde</span> (aprovado) ou <span style="color: #ef4444;">vermelho</span> (reprovado)<br>
                    <span style="color: #4299e1;">8.</span> Progresso do curso recalculado filtrando lições opcionais
                </div>
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
            const tabIndex = {'tests': 0, 'docs': 1, 'status': 2, 'quiz': 3, 'project': 4, 'readme': 1, 'composer': 1}[tabName];
            if (document.querySelectorAll('.tab')[tabIndex]) {
                document.querySelectorAll('.tab')[tabIndex].classList.add('active');
            }
        }
    </script>
</body>
</html>
