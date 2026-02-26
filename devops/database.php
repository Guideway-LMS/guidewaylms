<?php
define('_JEXEC', 1);
define('JPATH_BASE', dirname(__DIR__));

// Lista de Dumps
$dumpDir = JPATH_BASE . '/_dumps';
$files = [];
if (is_dir($dumpDir)) {
    $scanned_files = scandir($dumpDir);
    foreach ($scanned_files as $file) {
        if (pathinfo($file, PATHINFO_EXTENSION) === 'sql') {
            $files[] = [
                'name' => $file,
                'date' => date("d/m/Y H:i:s", filemtime($dumpDir . '/' . $file)),
                'size' => round(filesize($dumpDir . '/' . $file) / 1024 / 1024, 2) . ' MB'
            ];
        }
    }
    usort($files, function($a, $b) use ($dumpDir) {
        return filemtime($dumpDir . '/' . $b['name']) - filemtime($dumpDir . '/' . $a['name']);
    });
}

// Lista de Updates
$updatesDir = JPATH_BASE . '/database/updates';
$updateFiles = [];
if (is_dir($updatesDir)) {
    $uScanned_files = scandir($updatesDir);
    foreach ($uScanned_files as $file) {
        if (pathinfo($file, PATHINFO_EXTENSION) === 'sql') {
            $updateFiles[] = [
                'name' => $file,
                'date' => date("d/m/Y H:i:s", filemtime($updatesDir . '/' . $file)),
                'content' => file_get_contents($updatesDir . '/' . $file)
            ];
        }
    }
    usort($updateFiles, function($a, $b) use ($updatesDir) {
        return filemtime($updatesDir . '/' . $b['name']) - filemtime($updatesDir . '/' . $a['name']);
    });
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Sync & Dumps - Guideway LMS</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, system-ui, sans-serif; background: #1a1a2e; color: #eee; min-height: 100vh; padding: 20px; }
        .container { max-width: 1200px; margin: 0 auto; }
        
        .header { background: linear-gradient(135deg, #16213e 0%, #0f3460 100%); padding: 30px; border-radius: 12px; margin-bottom: 20px; border: 1px solid #1f4068; display: flex; justify-content: space-between; align-items: center; }
        .header-titles h1 { font-size: 26px; color: #e94560; margin-bottom: 5px; }
        .header-titles p { color: #a0aec0; font-size: 14px; }
        .back-btn { background: rgba(255,255,255,0.1); color: #fff; text-decoration: none; padding: 8px 16px; border-radius: 6px; font-size: 14px; }
        .back-btn:hover { background: rgba(255,255,255,0.2); }

        .dashboard-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 20px; }
        .full-width { grid-column: 1 / -1; }
        .panel { background: #16213e; border-radius: 12px; padding: 25px; border: 1px solid #1f4068; }
        .panel-header { display: flex; align-items: center; margin-bottom: 20px; gap: 10px; border-bottom: 1px solid #1f4068; padding-bottom: 15px; }
        .panel-header h2 { font-size: 18px; color: #fff; }
        
        .info-box { background: rgba(66, 153, 225, 0.1); border-left: 4px solid #4299e1; padding: 15px 20px; border-radius: 0 8px 8px 0; margin-bottom: 20px; }
        .info-box p { color: #a0aec0; font-size: 14px; margin-top: 5px; }
        
        .action-area { background: #0f3460; border-radius: 8px; padding: 20px; margin-bottom: 20px; display: flex; align-items: center; gap: 20px; flex-wrap: wrap; }
        .input-group label { color: #a0aec0; font-weight: 600; font-size: 14px; }
        .input-group input { background: #1a1a2e; border: 1px solid #1f4068; color: #fff; padding: 10px; border-radius: 6px; }

        .btn { border: none; padding: 12px 24px; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 14px; color: #fff; display: inline-flex; align-items: center; gap: 8px; }
        .btn-green { background: linear-gradient(135deg, #48bb78 0%, #38a169 100%); }
        .btn-blue { background: linear-gradient(135deg, #4299e1 0%, #3182ce 100%); }
        .btn-orange { background: linear-gradient(135deg, #ed8936 0%, #dd6b20 100%); }
        .btn-red { background: linear-gradient(135deg, #e53e3e 0%, #c53030 100%); }
        .btn-ghost { background: transparent; border: 1px solid #4a5568; color: #a0aec0; }
        .btn-sm { padding: 6px 12px; font-size: 12px; }
        .btn:disabled { opacity: 0.5; cursor: not-allowed; }

        .file-item { display: flex; justify-content: space-between; align-items: center; padding: 15px; border-bottom: 1px solid #1f4068; }
        .file-item:last-child { border-bottom: none; }
        .file-name { font-weight: 500; font-family: monospace; color: #fff; font-size: 14px; }
        .file-meta { font-size: 12px; color: #a0aec0; }
        
        .empty-state { text-align: center; padding: 30px; border: 2px dashed #1f4068; border-radius: 8px; color: #a0aec0; }
        #status-msg { margin-top: 15px; padding: 12px 16px; border-radius: 6px; display: none; font-size: 14px; }

        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 8px; color: #a0aec0; font-size: 14px; font-weight: 600; }
        .form-group input, .form-group textarea { width: 100%; background: #0f3460; border: 1px solid #1f4068; color: #fff; padding: 12px; border-radius: 6px; font-family: monospace; }
        
        .diff-item { background: rgba(0,0,0,0.2); padding: 15px; border-radius: 8px; margin-bottom: 10px; display: flex; justify-content: space-between; align-items: center; border-left: 4px solid #ed8936; }
        .diff-item code { color: #f6ad55; background: #2d3748; padding: 2px 6px; border-radius: 4px; }
        
        .spinner { border: 3px solid rgba(255,255,255,0.3); border-radius: 50%; border-top: 3px solid white; width: 16px; height: 16px; animation: spin 1s linear infinite; display: none; }
        @keyframes spin { 100% { transform: rotate(360deg); } }

        /* Modal */
        .modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.6); backdrop-filter: blur(4px); z-index: 1000; display: flex; align-items: center; justify-content: center; opacity: 0; pointer-events: none; transition: opacity 0.2s; }
        .modal-overlay.show { opacity: 1; pointer-events: all; }
        .modal-box { background: #1a1a2e; border: 1px solid #1f4068; border-radius: 16px; padding: 30px; max-width: 440px; width: 90%; transform: scale(0.95); transition: transform 0.25s; box-shadow: 0 20px 60px rgba(0,0,0,0.5); }
        .modal-overlay.show .modal-box { transform: scale(1); }
        .modal-icon { font-size: 40px; text-align: center; margin-bottom: 16px; }
        .modal-title { font-size: 18px; font-weight: 700; text-align: center; margin-bottom: 8px; }
        .modal-msg { font-size: 14px; color: #a0aec0; text-align: center; line-height: 1.6; margin-bottom: 24px; word-break: break-word; }
        .modal-actions { display: flex; gap: 10px; justify-content: center; }
        .modal-btn { padding: 10px 28px; border-radius: 8px; border: none; font-weight: 600; font-size: 14px; cursor: pointer; transition: all 0.2s; }
        .modal-btn-cancel { background: #2d3748; color: #a0aec0; }
        .modal-btn-cancel:hover { background: #4a5568; }
        .modal-btn-confirm { background: linear-gradient(135deg, #48bb78, #38a169); color: #fff; }
        .modal-btn-confirm:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(72,187,120,0.4); }
        .modal-btn-ok { background: linear-gradient(135deg, #4299e1, #3182ce); color: #fff; min-width: 120px; }
        .modal-btn-ok:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(66,153,225,0.4); }
        .modal-btn-error { background: linear-gradient(135deg, #e53e3e, #c53030); color: #fff; min-width: 120px; }

        /* Rules Accordion */
        .rules-accordion { background: #16213e; border: 1px solid #1f4068; border-radius: 12px; margin-bottom: 20px; overflow: hidden; }
        .rules-header { padding: 15px 25px; cursor: pointer; display: flex; justify-content: space-between; align-items: center; color: #fff; font-weight: 600; background: rgba(0,0,0,0.1); }
        .rules-header:hover { background: rgba(255,255,255,0.05); }
        .rules-content { padding: 0 25px; max-height: 0; overflow: hidden; transition: max-height 0.3s ease-out, padding 0.3s ease; background: #0f3460; }
        .rules-content.open { padding: 20px 25px; max-height: 500px; }
        .rules-list { list-style: none; color: #a0aec0; font-size: 14px; line-height: 1.6; }
        .rules-list li { margin-bottom: 12px; position: relative; padding-left: 24px; }
        .rules-list li::before { content: "👉"; position: absolute; left: 0; top: 0; }
        .rules-list strong { color: #fff; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="header-titles">
                <h1>🗄️ Database Sync & Tools</h1>
                <p>Gerenciamento inteligente de schema, Dumps e versionamento de SQL</p>
            </div>
            <a href="index.php" class="back-btn">← Voltar p/ Dashboard</a>
        </div>

        <div class="rules-accordion">
            <div class="rules-header" onclick="document.getElementById('rules-content').classList.toggle('open')">
                <span>📘 Regras de Uso e Fluxo de Trabalho do Banco</span>
                <span>▼</span>
            </div>
            <div class="rules-content" id="rules-content">
                <p style="color: #cbd5e0; font-size: 14px; margin-bottom: 15px; line-height: 1.5;">
                    <strong>Como esse sistema funciona:</strong> Ele automatiza a sincronização do banco de dados entre a equipe. Ao invés de ficar importando arquivos pesados de backup o tempo todo, esse painel lê o arquivo oficial de Dump (`.sql`) do repositório, cruza com o seu Banco de Dados Local ao vivo, e descobre sozinho exatamente quais tabelas ou colunas estão faltando pra você.
                </p>
                <ul class="rules-list">
                    <li><strong>Sincronize antes de codar:</strong> Sempre que der `git pull`, abra essa tela. Se o Checker apontar colunas/tabelas faltando, clique em "Aplicar Automático".</li>
                    <li><strong>Não altere o banco no DBeaver/HeidiSQL:</strong> Se precisar criar uma coluna ou tabela, use a área "Inserir Script Manual" abaixo. Isso garante que a equipe toda receba o update via Git.</li>
                    <li><strong>Coisas Novas Locais = Dump Novo:</strong> Se o painel alertar que seu banco tem colunas/tabelas a mais que o Dump oficial, isso significa que você criou algo novo. Gere um novo Dump com o seu Nome para que os outros baixem sua estrutura!</li>
                </ul>
            </div>
        </div>

        <div class="dashboard-grid">
            
            <!-- Schema Checker -->
            <div class="panel full-width">
                <div class="panel-header">
                    <div class="panel-icon">🔍</div>
                    <h2>Verificador de Integridade (Schema Diff)</h2>
                    <button class="btn btn-sm btn-ghost" onclick="runChecker()" id="btn-refresh" style="margin-left: auto;">🔄 Refresh</button>
                </div>
                
                <div id="checker-loading" style="text-align: center; padding: 40px; color: #a0aec0;">
                    <div class="spinner" style="display:inline-block; border-color: #4a5568; border-top-color: #fff; width:30px; height:30px;"></div>
                    <p style="margin-top: 15px;">Analisando Banco de Dados vs Último Dump...</p>
                </div>

                <div id="checker-result" style="display: none;"></div>
            </div>

            <!-- Scripts Manuais -->
            <div class="panel">
                <div class="panel-header">
                    <div class="panel-icon">➕</div>
                    <h2>Inserir Script Manual</h2>
                </div>
                <div class="info-box" style="margin-bottom: 15px; padding: 12px;">
                    <p style="margin-top:0; font-size: 13px;">Gera um arquivo <code>.sql</code> na pasta <code>database/updates/</code> com timestamp.</p>
                </div>
                
                <div class="form-group">
                    <label>Título (ex: add_tags)</label>
                    <input type="text" id="manual_title" placeholder="add_forum_tags">
                </div>
                <div class="form-group">
                    <label>Comando SQL (USE #__ PARA PREFIXO)</label>
                    <textarea id="manual_sql" rows="4" placeholder="ALTER TABLE `#__tabela` ADD COLUMN ..."></textarea>
                </div>
                
                <div style="display: flex; gap: 10px; margin-top: 15px;">
                    <button class="btn btn-blue" onclick="runManualScript(true)" id="btn-ms-save" title="Salva na pasta database/updates/ mas NÃO aplica no DB.">💾 Só Salvar</button>
                    <button class="btn btn-orange" onclick="runManualScript(false)" id="btn-ms-exec" title="Salva na pasta e aplica a query no seu Banco.">🚀 Salvar e Executar</button>
                </div>
                <div id="ms-status" style="margin-top: 10px; font-size: 13px;"></div>
            </div>

            <!-- Dumps -->
            <div class="panel">
                <div class="panel-header">
                    <div class="panel-icon">📦</div>
                    <h2>Dumps Oficiais do Repositório</h2>
                </div>
                
                <div class="action-area">
                    <div class="input-group">
                        <label>Nome do Dev:</label>
                        <input type="text" id="username" placeholder="joaosilva" style="width: 120px;">
                    </div>
                    <button class="btn btn-green" id="btn-generate" onclick="generateDump()">
                        <div class="spinner" id="spinner-dump"></div> <span id="btn-text">Gerar Dump</span>
                    </button>
                </div>
                <div id="status-msg"></div>

                <div style="margin-top: 20px;">
                    <h3 style="font-size: 14px; color: #a0aec0; margin-bottom: 10px;">📁 _dumps/</h3>
                    <?php if (empty($files)): ?>
                        <div class="empty-state">Nenhum dump.</div>
                    <?php else: ?>
                        <div style="max-height: 200px; overflow-y: auto;">
                            <?php foreach ($files as $file): ?>
                                <div class="file-item">
                                    <div class="file-info">
                                        <span class="file-name"><?php echo htmlspecialchars($file['name']); ?></span>
                                        <span class="file-meta">📅 <?php echo $file['date']; ?> • 💾 <?php echo $file['size']; ?></span>
                                    </div>
                                    <div class="file-actions">
                                        <a href="../_dumps/<?php echo urlencode($file['name']); ?>" download>⬇</a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Updates Pendentes -->
            <div class="panel full-width">
                <div class="panel-header">
                    <div class="panel-icon">🔄</div>
                    <h2>Scripts de Update Pendentes</h2>
                    <span style="margin-left:auto; font-size:12px; color:#a0aec0;">database/updates/*.sql</span>
                </div>
                <?php if (empty($updateFiles)): ?>
                    <div class="empty-state" style="padding: 20px;">Nenhum script de update manual encontrado.</div>
                <?php else: ?>
                    <div style="display: grid; gap: 10px;">
                        <?php foreach ($updateFiles as $idx => $file): ?>
                            <div class="diff-item" style="border-left-color: #3182ce;">
                                <div>
                                    <div class="file-name"><?php echo htmlspecialchars($file['name']); ?></div>
                                    <div class="file-meta">Criado em: <?php echo $file['date']; ?></div>
                                    <div style="margin-top:8px;">
                                        <code style="font-size:11px;"><?php echo htmlspecialchars(substr($file['content'], 0, 80)); ?>...</code>
                                    </div>
                                    <textarea id="up_<?php echo $idx; ?>" style="display:none;"><?php echo htmlspecialchars($file['content']); ?></textarea>
                                </div>
                                <button class="btn btn-sm btn-green" onclick="runAutoFix(document.getElementById('up_<?php echo $idx; ?>').value, false)">▶ Rodar no Banco</button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </div>

    <!-- Modal Custom -->
    <div class="modal-overlay" id="modal">
        <div class="modal-box">
            <div class="modal-icon" id="modal-icon"></div>
            <div class="modal-title" id="modal-title"></div>
            <div class="modal-msg" id="modal-msg"></div>
            <div class="modal-actions" id="modal-actions"></div>
        </div>
    </div>

    <script>
        // Array global para armazenar SQLs do checker (evita problemas com aspas no onclick)
        let pendingSqls = [];

        // Init Checker
        document.addEventListener("DOMContentLoaded", runChecker);

        function runChecker() {
            document.getElementById('checker-loading').style.display = 'block';
            document.getElementById('checker-result').style.display = 'none';
            pendingSqls = [];

            fetch('schema_checker.php')
                .then(res => res.json())
                .then(data => {
                    document.getElementById('checker-loading').style.display = 'none';
                    const resDiv = document.getElementById('checker-result');
                    resDiv.style.display = 'block';
                    resDiv.innerHTML = '';

                    if (!data.success) {
                        resDiv.innerHTML = `<div class="info-box error"><p>Erro: ${data.message}</p></div>`;
                        return;
                    }

                    let html = `<div style="margin-bottom:15px; font-size:14px; color:#a0aec0;">Comparando Live DB contra o dump: <strong>${data.dump_file}</strong></div>`;

                    if (data.status === 'ok') {
                        html += `<div class="info-box success" style="border-left-color: #48bb78; background: rgba(72,187,120,0.15);">
                            <h3 style="color:#48bb78; margin-bottom:5px;">Tudo Sincronizado! ✅</h3>
                            <p>O seu banco de dados local está idêntico à estrutura do último Dump do Git.</p>
                        </div>`;
                    } else {
                        // Missing Tables
                        data.diff.missing_tables.forEach(t => {
                            const idx = pendingSqls.length;
                            pendingSqls.push(t.sql);
                            html += `
                            <div class="diff-item">
                                <div>
                                    <span style="color:#fc8181; font-weight:bold;">Tabela Faltando Local:</span> <code>${t.table}</code>
                                    <div class="file-meta" style="margin-top:4px;">Ela está no Git mas não no seu BD.</div>
                                </div>
                                <button class="btn btn-sm btn-orange" onclick="applyPendingSql(${idx})">Aplicar Automático</button>
                            </div>`;
                        });
                        
                        // Missing Columns
                        data.diff.missing_columns.forEach(c => {
                            const idx = pendingSqls.length;
                            pendingSqls.push(c.sql);
                            html += `
                            <div class="diff-item">
                                <div>
                                    <span style="color:#fc8181; font-weight:bold;">Coluna Faltando Local:</span> <code>${c.column}</code> na tabela <code>${c.table}</code>
                                    <div class="file-meta" style="margin-top:4px;">Ela está no Git mas não no seu BD.</div>
                                </div>
                                <button class="btn btn-sm btn-orange" onclick="applyPendingSql(${idx})">Aplicar Automático</button>
                            </div>`;
                        });

                        // Extra warnings
                        if (data.diff.extra_tables.length > 0 || data.diff.extra_columns.length > 0) {
                             let extrasList = '';
                             data.diff.extra_tables.forEach(t => {
                                 extrasList += `<li>Tabela nova: <strong>${t}</strong></li>`;
                             });
                             data.diff.extra_columns.forEach(c => {
                                 extrasList += `<li>Coluna nova: <strong>${c.column}</strong> na tabela <strong>${c.table}</strong></li>`;
                             });

                             html += `<div class="info-box warning" style="margin-top: 20px; border-left-color: #ed8936; background: rgba(237,137,54,0.1);">
                                <strong style="color: #ed8936;">⚠️ Aviso: Seu Banco Local possui itens extras não mapeados no Dump!</strong><br>
                                <p style="margin-bottom: 8px;">Caso você tenha criado essas estruturas recentemente, gere um novo Dump abaixo para enviá-las ao repositório.</p>
                                <ul style="margin-left: 20px; font-size: 13px; color: #cbd5e0; line-height: 1.6;">
                                    ${extrasList}
                                </ul>
                             </div>`;
                        }
                    }

                    resDiv.innerHTML = html;
                }).catch(err => {
                    document.getElementById('checker-loading').style.display = 'none';
                    document.getElementById('checker-result').innerHTML = `<div class="info-box error"><p>Erro de conexão com o painel.</p></div>`;
                });
        }

        function applyPendingSql(index) {
            runAutoFix(pendingSqls[index], false);
        }

        // === Modal Functions ===
        function showModal(icon, title, msg, buttons) {
            document.getElementById('modal-icon').textContent = icon;
            document.getElementById('modal-title').textContent = title;
            document.getElementById('modal-msg').textContent = msg;
            const actionsDiv = document.getElementById('modal-actions');
            actionsDiv.innerHTML = '';
            buttons.forEach(b => {
                const btn = document.createElement('button');
                btn.className = 'modal-btn ' + (b.cls || '');
                btn.textContent = b.label;
                btn.onclick = () => { closeModal(); if (b.action) b.action(); };
                actionsDiv.appendChild(btn);
            });
            document.getElementById('modal').classList.add('show');
        }
        function closeModal() { document.getElementById('modal').classList.remove('show'); }

        function showSuccess(msg, onOk) {
            showModal('✅', 'Sucesso!', msg, [{ label: 'OK', cls: 'modal-btn-ok', action: onOk }]);
        }
        function showError(msg) {
            showModal('❌', 'Erro', msg, [{ label: 'Fechar', cls: 'modal-btn-error' }]);
        }
        function showConfirm(msg, onConfirm) {
            showModal('⚠️', 'Confirmação', msg, [
                { label: 'Cancelar', cls: 'modal-btn-cancel' },
                { label: 'Confirmar', cls: 'modal-btn-confirm', action: onConfirm }
            ]);
        }

        // === Auto Fix ===
        function runAutoFix(sqlData, isBase64) {
            let sql = sqlData;
            if (isBase64) {
                try {
                    sql = decodeURIComponent(escape(atob(sqlData)));
                } catch(e) {
                    showError('Erro ao decodificar SQL.'); return;
                }
            }
            
            showConfirm('Deseja aplicar esta alteração no seu Banco de Dados?', () => {
                const fd = new FormData();
                fd.append('action', 'auto');
                fd.append('sql', sql);

                fetch('schema_runner.php', { method: 'POST', body: fd })
                    .then(r => r.json())
                    .then(data => {
                        if(data.success) {
                            showSuccess(data.message, () => window.location.reload());
                        } else {
                            showError(data.message);
                        }
                    });
            });
        }

        // === Manual Script ===
        function runManualScript(saveOnly) {
            const title = document.getElementById('manual_title').value;
            const sql = document.getElementById('manual_sql').value;
            const statusDiv = document.getElementById('ms-status');

            if(!title || !sql) {
                showError('Preencha o título e o comando SQL.'); return;
            }

            const fd = new FormData();
            fd.append('action', 'manual');
            fd.append('title', title);
            fd.append('sql', sql);
            if (saveOnly) fd.append('save_only', '1');

            const btnSave = document.getElementById('btn-ms-save');
            const btnExec = document.getElementById('btn-ms-exec');
            btnSave.disabled = true; btnExec.disabled = true;
            statusDiv.style.color = "#a0aec0";
            statusDiv.textContent = "Processando...";
            
            fetch('schema_runner.php', { method: 'POST', body: fd })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        statusDiv.style.color = "#48bb78";
                        statusDiv.textContent = "✅ " + data.message;
                        setTimeout(() => window.location.reload(), 1500);
                    } else {
                        statusDiv.style.color = "#fc8181";
                        statusDiv.textContent = "❌ " + data.message;
                    }
                }).finally(() => {
                    btnSave.disabled = false; btnExec.disabled = false;
                });
        }

        function generateDump() {
            const btn = document.getElementById('btn-generate');
            const spinner = document.getElementById('spinner-dump');
            const btnText = document.getElementById('btn-text');
            const statusMsg = document.getElementById('status-msg');
            const username = document.getElementById('username').value.trim();

            if (!username) {
                showError("Por favor, preencha o seu Nome de Dev antes de gerar o Dump.");
                return;
            }

            btn.disabled = true;
            spinner.style.display = 'block';
            btnText.textContent = 'Gerando...';

            const formData = new FormData();
            formData.append('username', username);

            fetch('generate_dump.php', { method: 'POST', body: formData })
            .then(r => r.json())
            .then(data => {
                statusMsg.style.display = 'block';
                if (data.success) {
                    statusMsg.style.color = "#48bb78";
                    statusMsg.textContent = '✅ ' + data.message;
                    setTimeout(() => location.reload(), 1500);
                } else {
                    statusMsg.style.color = "#fc8181";
                    statusMsg.textContent = '❌ ' + data.message;
                }
            })
            .finally(() => {
                btn.disabled = false;
                spinner.style.display = 'none';
                btnText.textContent = 'Gerar Dump';
            });
        }
    </script>
</body>
</html>
