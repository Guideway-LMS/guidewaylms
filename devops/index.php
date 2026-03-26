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

        /* === CONTENT === */
        .main { max-width: 800px; margin: 0 auto; padding: 40px 50px; }


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



    <div class="main">

        <!-- ═══════════ DASHBOARD CONTENT ═══════════ -->
        <div>

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
                <a href="test_callai_endpoint.php" class="card">
                    <div class="card-top">
                        <div class="card-emoji">🔐</div>
                        <span class="card-tag tag-ai">AI</span>
                    </div>
                    <h3>Endpoint callAI</h3>
                    <p>Verificar segurança CSRF do controller</p>
                    <div class="card-stripe stripe-orange"></div>
                </a>
            </div>
        </div>



    </div>


</body>
</html>
