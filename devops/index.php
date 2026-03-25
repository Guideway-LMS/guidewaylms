<?php
require_once __DIR__ . '/auth.php';

/**
 * DevOps Dashboard - Guideway LMS
 * Centro de Comando: Ferramentas de Dev, Testes, DB e Documentação
 */
// define('_JEXEC', 1);
// define('JPATH_BASE', dirname(__DIR__));
require_once JPATH_BASE . '/includes/defines.php';
require_once JPATH_BASE . '/includes/framework.php';

use Joomla\CMS\Factory;

// Quiz migration logic
$migrationColumns = [
    'lesson_format' => ['type' => "VARCHAR(30) NOT NULL DEFAULT 'content'", 'desc' => 'Tipo de lição: content, quiz, assignment'],
    'quiz_id'       => ['type' => 'INT(11) NOT NULL DEFAULT 0', 'desc' => 'ID do quiz vinculado à lição'],
    'passing_score' => ['type' => 'INT(11) NOT NULL DEFAULT 0', 'desc' => 'Nota de corte personalizada (%)'],
    'is_optional'   => ['type' => 'TINYINT(1) NOT NULL DEFAULT 0', 'desc' => 'Se a lição é opcional no progresso'],
];

$db = Factory::getDbo();
$existingColumns = $db->getTableColumns('#__splms_lessons');
$migrationResults = [];

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
    $existingColumns = $db->getTableColumns('#__splms_lessons');
}

$allQuizColsExist = true;
foreach ($migrationColumns as $colName => $colInfo) {
    if (!array_key_exists($colName, $existingColumns)) { $allQuizColsExist = false; break; }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DevOps Dashboard - Guideway LMS</title>
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
        }
        .header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(244,63,94,0.08) 0%, transparent 70%);
            border-radius: 50%;
        }
        .header-inner { max-width: 1300px; margin: 0 auto; display: flex; justify-content: space-between; align-items: center; position: relative; z-index: 1; }
        .header h1 { font-size: 30px; font-weight: 800; letter-spacing: -0.5px; }
        .header h1 span { background: linear-gradient(135deg, var(--accent-red), var(--accent-orange)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .header p { color: var(--text-secondary); font-size: 14px; margin-top: 6px; }
        .header-badge { background: var(--bg-primary); border: 1px solid var(--border); padding: 8px 16px; border-radius: 20px; font-size: 12px; color: var(--text-secondary); display: flex; align-items: center; gap: 6px; }
        .header-badge .dot { width: 8px; height: 8px; border-radius: 50%; background: var(--accent-green); animation: pulse 2s infinite; }
        @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.4; } }

        /* === NAV === */
        .nav { background: var(--bg-secondary); border-bottom: 1px solid var(--border); padding: 0 50px; position: sticky; top: 0; z-index: 100; }
        .nav-inner { max-width: 1300px; margin: 0 auto; display: flex; gap: 0; overflow-x: auto; }
        .nav-item {
            padding: 16px 24px;
            cursor: pointer;
            color: var(--text-muted);
            font-weight: 600;
            font-size: 13px;
            border-bottom: 2px solid transparent;
            transition: all 0.2s;
            white-space: nowrap;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .nav-item:hover { color: var(--text-primary); background: rgba(255,255,255,0.03); }
        .nav-item.active { color: var(--accent-red); border-bottom-color: var(--accent-red); }

        /* === CONTENT === */
        .main { max-width: 1300px; margin: 0 auto; padding: 40px 50px; }
        .tab-content { display: none; }
        .tab-content.active { display: block; animation: fadeIn 0.3s ease; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }

        /* === SECTION === */
        .section-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 24px;
            padding-bottom: 16px;
            border-bottom: 1px solid var(--border);
        }
        .section-header .icon {
            width: 36px; height: 36px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
        }
        .section-header h2 { font-size: 18px; font-weight: 700; }
        .section-header p { font-size: 13px; color: var(--text-muted); margin-left: auto; }

        /* === CARDS === */
        .cards { display: grid; grid-template-columns: repeat(auto-fill, minmax(290px, 1fr)); gap: 16px; margin-bottom: 40px; }
        .card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 24px;
            text-decoration: none;
            color: inherit;
            transition: all 0.25s;
            position: relative;
            overflow: hidden;
        }
        .card:hover { border-color: var(--accent-blue); transform: translateY(-2px); box-shadow: 0 8px 24px rgba(0,0,0,0.3); }
        .card-top { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 14px; }
        .card-emoji { font-size: 28px; }
        .card-tag { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; padding: 4px 10px; border-radius: 6px; }
        .tag-db { background: rgba(59,130,246,0.15); color: var(--accent-blue); }
        .tag-ai { background: rgba(249,115,22,0.15); color: var(--accent-orange); }
        .tag-devops { background: rgba(34,197,94,0.15); color: var(--accent-green); }
        .tag-doc { background: rgba(168,85,247,0.15); color: var(--accent-purple); }
        .tag-quiz { background: rgba(234,179,8,0.15); color: var(--accent-yellow); }
        .tag-warn { background: rgba(244,63,94,0.15); color: var(--accent-red); }
        .card h3 { font-size: 15px; font-weight: 700; margin-bottom: 6px; }
        .card p { font-size: 13px; color: var(--text-secondary); line-height: 1.5; }
        .card-stripe { position: absolute; bottom: 0; left: 0; right: 0; height: 3px; }
        .stripe-blue { background: linear-gradient(90deg, var(--accent-blue), var(--accent-cyan)); }
        .stripe-orange { background: linear-gradient(90deg, var(--accent-orange), var(--accent-yellow)); }
        .stripe-green { background: linear-gradient(90deg, var(--accent-green), var(--accent-cyan)); }
        .stripe-purple { background: linear-gradient(90deg, var(--accent-purple), var(--accent-red)); }
        .stripe-yellow { background: linear-gradient(90deg, var(--accent-yellow), var(--accent-orange)); }

        /* === DOC PANELS === */
        .doc-panel { background: var(--bg-card); border: 1px solid var(--border); border-radius: 12px; padding: 32px; }
        .doc-panel h2 { color: var(--accent-orange); font-size: 18px; margin: 20px 0 10px; }
        .doc-panel code { background: var(--bg-primary); padding: 2px 8px; border-radius: 4px; font-size: 13px; color: var(--accent-green); }

        /* === ALERT === */
        .alert { padding: 16px 20px; border-radius: 10px; margin-bottom: 24px; display: flex; align-items: flex-start; gap: 12px; font-size: 14px; }
        .alert-success { background: rgba(34,197,94,0.1); border: 1px solid rgba(34,197,94,0.2); }
        .alert-warn { background: rgba(249,115,22,0.1); border: 1px solid rgba(249,115,22,0.2); }
        .alert-title { font-weight: 700; margin-bottom: 4px; }
        .alert-text { color: var(--text-secondary); font-size: 13px; }

        /* === TABLE === */
        .data-table { width: 100%; border-collapse: collapse; margin: 16px 0; }
        .data-table th { padding: 12px 16px; text-align: left; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-muted); border-bottom: 2px solid var(--border); }
        .data-table td { padding: 12px 16px; border-bottom: 1px solid var(--border); font-size: 14px; }
        .data-table tr:hover { background: rgba(255,255,255,0.02); }

        /* === BUTTON === */
        .btn-action {
            background: linear-gradient(135deg, var(--accent-orange), #ea580c);
            border: none;
            color: white;
            padding: 14px 36px;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-action:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(249,115,22,0.4); }
        .flow-block { background: var(--bg-primary); border: 1px solid var(--border); border-radius: 10px; padding: 20px; font-family: monospace; font-size: 13px; line-height: 2; color: var(--text-secondary); margin: 16px 0; }
        .flow-block .step { color: var(--accent-blue); font-weight: 600; }
    </style>
</head>
<body>

    <div class="header">
        <div class="header-inner">
            <div>
                <h1>🚀 <span>DevOps</span> Dashboard</h1>
                <p>Centro de Comando — Guideway LMS</p>
            </div>
        </div>
    </div>

    <div class="nav">
        <div class="nav-inner">
            <div class="nav-item active" onclick="showTab('home')">🏠 Início</div>
            <div class="nav-item" onclick="showTab('quiz')">🎓 Quiz</div>
        </div>
    </div>

    <div class="main">

        <!-- ═══════════ TAB: HOME ═══════════ -->
        <div id="tab-home" class="tab-content active">

            <!-- DEVOPS -->
            <div class="section-header">
                <div class="icon" style="background: rgba(34,197,94,0.15);">⚙️</div>
                <h2>DevOps & Infraestrutura</h2>
            </div>
            <div class="cards">
                <a href="database.php" class="card">
                    <div class="card-top">
                        <div class="card-emoji">🗄️</div>
                        <span class="card-tag tag-devops">DevOps</span>
                    </div>
                    <h3>Database Sync & Dumps</h3>
                    <p>Schema Checker, correções automáticas, scripts manuais e dumps do banco.</p>
                    <div class="card-stripe stripe-green"></div>
                </a>
                <a href="sync_admin_menus.php" class="card">
                    <div class="card-top">
                        <div class="card-emoji">📋</div>
                        <span class="card-tag tag-devops">DevOps</span>
                    </div>
                    <h3>Sincronizar Menus (Admin)</h3>
                    <p>Alinha e injeta submenus do SP LMS faltantes na lateral do Joomla 5.</p>
                    <div class="card-stripe stripe-green"></div>
                </a>
            </div>

            <!-- BANCO DE DADOS -->
            <div class="section-header">
                <div class="icon" style="background: rgba(59,130,246,0.15);">🗃️</div>
                <h2>Banco de Dados & Model</h2>
            </div>
            <div class="cards">
                <a href="test_announcements_check.php" class="card">
                    <div class="card-top">
                        <div class="card-emoji">🧪</div>
                        <span class="card-tag tag-db">DB</span>
                    </div>
                    <h3>Teste de Avisos</h3>
                    <p>Verifica o Model de Announcements</p>
                    <div class="card-stripe stripe-blue"></div>
                </a>
                <a href="create_test_data.php" class="card">
                    <div class="card-top">
                        <div class="card-emoji">🛠️</div>
                        <span class="card-tag tag-db">DB</span>
                    </div>
                    <h3>Criar Dados de Teste</h3>
                    <p>Gera matrícula e dados fake para testes</p>
                    <div class="card-stripe stripe-blue"></div>
                </a>
                <a href="debug_db_columns.php" class="card">
                    <div class="card-top">
                        <div class="card-emoji">🔍</div>
                        <span class="card-tag tag-db">DB</span>
                    </div>
                    <h3>Debug Schema</h3>
                    <p>Lista colunas e estrutura de tabelas</p>
                    <div class="card-stripe stripe-blue"></div>
                </a>
            </div>

            <!-- IA -->
            <div class="section-header">
                <div class="icon" style="background: rgba(249,115,22,0.15);">🤖</div>
                <h2>Inteligência Artificial</h2>
            </div>
            <div class="cards">
                <a href="groq_smoke_test.php" class="card">
                    <div class="card-top">
                        <div class="card-emoji">🔌</div>
                        <span class="card-tag tag-ai">AI</span>
                    </div>
                    <h3>Groq Smoke Test</h3>
                    <p>Teste de conexão com a API Groq</p>
                    <div class="card-stripe stripe-orange"></div>
                </a>
                <a href="groq_prompt_test.php" class="card">
                    <div class="card-top">
                        <div class="card-emoji">📝</div>
                        <span class="card-tag tag-warn">Tokens 🪙</span>
                    </div>
                    <h3>Teste de Prompts</h3>
                    <p>Testar ações: Revisar, Resumir, Reescrever</p>
                    <div class="card-stripe stripe-orange"></div>
                </a>
                <a href="test_callai_endpoint.php" class="card">
                    <div class="card-top">
                        <div class="card-emoji">🔐</div>
                        <span class="card-tag tag-ai">AI</span>
                    </div>
                    <h3>Endpoint callAI</h3>
                    <p>Verificar segurança CSRF do controller</p>
                    <div class="card-stripe stripe-orange"></div>
                </a>
                <a href="pdf_upload_test.php" class="card">
                    <div class="card-top">
                        <div class="card-emoji">📄</div>
                        <span class="card-tag tag-ai">AI</span>
                    </div>
                    <h3>Upload de PDF</h3>
                    <p>Validar upload e parsing de PDF</p>
                    <div class="card-stripe stripe-orange"></div>
                </a>
                <a href="quiz_gen_test.php" class="card">
                    <div class="card-top">
                        <div class="card-emoji">🎓</div>
                        <span class="card-tag tag-ai">AI</span>
                    </div>
                    <h3>Gerador de Quiz (IA)</h3>
                    <p>Gera questões automaticamente a partir de PDF</p>
                    <div class="card-stripe stripe-orange"></div>
                </a>
            </div>
        </div>

        <!-- ═══════════ TAB: QUIZ ═══════════ -->
        <div id="tab-quiz" class="tab-content">
            <div class="alert alert-warn">
                <div>
                    <div class="alert-title">🗄️ Migração de Banco de Dados</div>
                    <div class="alert-text">Colunas necessárias na tabela <code>#__splms_lessons</code> para as funcionalidades de Quiz.</div>
                </div>
            </div>

            <div class="doc-panel" style="margin-bottom: 30px;">
                <h2 style="color: var(--accent-yellow); margin-top: 0;">Status das Colunas</h2>
                <table class="data-table">
                    <tr>
                        <th>Coluna</th><th>Tipo</th><th>Descrição</th><th style="text-align:center;">Status</th>
                    </tr>
                    <?php foreach ($migrationColumns as $colName => $colInfo) :
                        $exists = array_key_exists($colName, $existingColumns);
                        $icon = $exists ? '✅' : '❌';
                        $rowBg = $exists ? 'rgba(34,197,94,0.06)' : 'rgba(244,63,94,0.06)';
                    ?>
                    <tr style="background: <?php echo $rowBg; ?>;">
                        <td><code><?php echo $colName; ?></code></td>
                        <td style="color: var(--text-secondary);"><code><?php echo $colInfo['type']; ?></code></td>
                        <td style="color: var(--text-secondary);"><?php echo $colInfo['desc']; ?></td>
                        <td style="text-align:center; font-size:18px;"><?php echo $icon; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </table>

                <?php if (!empty($migrationResults)) : ?>
                <div style="background: var(--bg-primary); border-radius: 8px; padding: 16px; margin: 16px 0; border: 1px solid var(--border);">
                    <h4 style="color: var(--accent-green); margin-bottom: 10px;">📋 Resultado da Migração</h4>
                    <?php foreach ($migrationResults as $col => $res) :
                        $color = $res['status'] === 'ok' ? 'var(--accent-green)' : ($res['status'] === 'skip' ? 'var(--accent-yellow)' : 'var(--accent-red)');
                        $icon = $res['status'] === 'ok' ? '✅' : ($res['status'] === 'skip' ? '⏭️' : '❌');
                    ?>
                    <p style="color: <?php echo $color; ?>; margin: 4px 0;">
                        <?php echo $icon; ?> <code><?php echo $col; ?></code> — <?php echo htmlspecialchars($res['msg']); ?>
                    </p>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <?php if ($allQuizColsExist) : ?>
                <div class="alert alert-success" style="margin-top: 16px;">
                    <div class="alert-title" style="color: var(--accent-green);">✅ Todas as colunas já existem. Nenhuma migração necessária.</div>
                </div>
                <?php else : ?>
                <form method="POST" style="text-align: center; margin-top: 20px;">
                    <input type="hidden" name="run_quiz_migration" value="1">
                    <button type="submit" class="btn-action">🚀 Aplicar Migração</button>
                    <p style="color: var(--text-muted); font-size: 12px; margin-top: 10px;">Apenas as colunas faltantes serão adicionadas.</p>
                </form>
                <?php endif; ?>
            </div>

            <div class="doc-panel">
                <h2 style="color: var(--accent-yellow); margin-top: 0;">Arquivos Modificados</h2>
                <table class="data-table">
                    <tr><th>Arquivo</th><th>Função</th></tr>
                    <tr><td><code>views/quizquestion/view.html.php</code></td><td style="color: var(--text-secondary);">View do quiz: resultado colorido, CSS, nota de corte</td></tr>
                    <tr><td><code>layouts/course/content.php</code></td><td style="color: var(--text-secondary);">Badges "★ Obrigatório" / "● Opcional" na lista de aulas</td></tr>
                    <tr><td><code>templates/maestro/.../default.php</code></td><td style="color: var(--text-secondary);">Seção Quiz na página de lição, botão "Iniciar Quiz"</td></tr>
                    <tr><td><code>controllers/quizquestions.php</code></td><td style="color: var(--text-secondary);">Salva resultado do quiz e marca lição como concluída</td></tr>
                    <tr><td><code>models/course.php</code></td><td style="color: var(--text-secondary);">Filtra lições opcionais do cálculo de progresso</td></tr>
                    <tr><td><code>models/forms/lesson.xml</code></td><td style="color: var(--text-secondary);">Campos admin: lesson_format, quiz_id, passing_score</td></tr>
                    <tr><td><code>admin/models/quizquestion.php</code></td><td style="color: var(--text-secondary);">Criação automática de lição ao salvar quiz</td></tr>
                </table>

                <h2 style="color: var(--accent-yellow);">Fluxo de Funcionamento</h2>
                <div class="flow-block">
                    <span class="step">1.</span> Admin cria Quiz → Lição criada automaticamente com <code>lesson_format='quiz'</code><br>
                    <span class="step">2.</span> Aluno acessa Lição → Vê botão "Iniciar Quiz" + nota de corte<br>
                    <span class="step">3.</span> Aluno responde questões → JS calcula acertos<br>
                    <span class="step">4.</span> <code>insertScore()</code> envia resultado via AJAX → <code>submit_result()</code><br>
                    <span class="step">5.</span> Backend salva em <code>#__splms_quizresults</code><br>
                    <span class="step">6.</span> Se aprovado → marca lição como concluída em <code>#__splms_lesson_completed</code><br>
                    <span class="step">7.</span> <code>displayScore()</code> mostra resultado: <span style="color: var(--accent-green);">verde</span> ou <span style="color: var(--accent-red);">vermelho</span><br>
                    <span class="step">8.</span> Progresso do curso recalculado filtrando lições opcionais
                </div>
            </div>
        </div>

    </div>

    <script>
        function showTab(tabName) {
            document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
            document.querySelectorAll('.nav-item').forEach(el => el.classList.remove('active'));

            document.getElementById('tab-' + tabName).classList.add('active');

            const tabMap = {'home': 0, 'quiz': 1};
            const navItems = document.querySelectorAll('.nav-item');
            if (navItems[tabMap[tabName]]) navItems[tabMap[tabName]].classList.add('active');
        }
    </script>
</body>
</html>
