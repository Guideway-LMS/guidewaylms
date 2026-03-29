<?php
require_once __DIR__ . '/auth.php';

// define('_JEXEC', 1);
// define('JPATH_BASE', dirname(__DIR__));

// Listar arquivos existentes
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
    // Ordenar por data (mais recente primeiro)
    usort($files, function($a, $b) use ($dumpDir) {
        return filemtime($dumpDir . '/' . $b['name']) - filemtime($dumpDir . '/' . $a['name']);
    });
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciador de Dumps - Guideway LMS</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #1a202c;
            padding: 20px;
            min-height: 100vh;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
        }
        .header {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
            padding: 30px;
            border-radius: 12px 12px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header h1 { font-size: 24px; }
        .back-btn {
            color: white;
            text-decoration: none;
            background: rgba(255,255,255,0.2);
            padding: 8px 16px;
            border-radius: 6px;
            transition: background 0.2s;
        }
        .back-btn:hover { background: rgba(255,255,255,0.3); }
        
        .main-card {
            background: white;
            border-radius: 0 0 12px 12px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            padding: 30px;
        }

        .action-area {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 20px;
            flex-wrap: wrap;
        }
        .input-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .input-group label { font-weight: 600; color: #4a5568; }
        .input-group input {
            padding: 10px 14px;
            border: 1px solid #cbd5e0;
            border-radius: 6px;
            width: 80px;
            font-size: 16px;
        }
        .btn-generate {
            background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            transition: transform 0.2s, box-shadow 0.2s;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .btn-generate:hover { 
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(72, 187, 120, 0.4);
        }
        .btn-generate:disabled { 
            background: #cbd5e0; 
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .file-list h2 { 
            font-size: 18px; 
            margin-bottom: 15px; 
            color: #2d3748;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .file-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            border-bottom: 1px solid #edf2f7;
            transition: background 0.1s;
        }
        .file-item:last-child { border-bottom: none; }
        .file-item:hover { background: #f7fafc; }
        .file-info { display: flex; flex-direction: column; gap: 4px; }
        .file-name { font-weight: 500; color: #2d3748; font-family: monospace; }
        .file-meta { font-size: 12px; color: #718096; }
        .file-actions a {
            color: #3182ce;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            background: #ebf8ff;
            padding: 6px 12px;
            border-radius: 4px;
            transition: background 0.2s;
        }
        .file-actions a:hover { 
            background: #bee3f8;
        }

        #status-msg {
            margin-top: 15px;
            padding: 12px 16px;
            border-radius: 6px;
            display: none;
            font-weight: 500;
        }
        .success { background: #c6f6d5; color: #22543d; }
        .error { background: #fed7d7; color: #822727; }
        
        .spinner {
            border: 3px solid rgba(255,255,255,0.3);
            border-radius: 50%;
            border-top: 3px solid white;
            width: 16px;
            height: 16px;
            animation: spin 1s linear infinite;
            display: none;
        }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }

        .empty-state {
            color: #a0aec0; 
            text-align: center; 
            padding: 40px 20px;
            background: #f7fafc;
            border-radius: 8px;
            border: 2px dashed #e2e8f0;
        }
        .empty-state p { margin-bottom: 10px; }

        .info-box {
            background: #ebf8ff;
            border-left: 4px solid #3182ce;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 0 6px 6px 0;
            font-size: 14px;
            color: #2c5282;
        }

        /* Documentação Colapsável */
        .docs-section {
            margin-top: 30px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            overflow: hidden;
        }
        .docs-section summary {
            background: #f7fafc;
            padding: 15px 20px;
            cursor: pointer;
            font-weight: 600;
            color: #4a5568;
            transition: background 0.2s;
            list-style: none;
        }
        .docs-section summary::-webkit-details-marker { display: none; }
        .docs-section summary::before {
            content: '▶ ';
            font-size: 12px;
            margin-right: 8px;
        }
        .docs-section[open] summary::before { content: '▼ '; }
        .docs-section summary:hover { background: #edf2f7; }
        .docs-content {
            padding: 20px;
            background: white;
            font-size: 14px;
            line-height: 1.7;
        }
        .docs-content h3 {
            color: #2d3748;
            margin: 20px 0 10px 0;
            font-size: 16px;
        }
        .docs-content h3:first-child { margin-top: 0; }
        .docs-content p { margin-bottom: 10px; color: #4a5568; }
        .docs-content ul, .docs-content ol {
            margin: 10px 0 15px 20px;
            color: #4a5568;
        }
        .docs-content li { margin: 5px 0; }
        .docs-content code {
            background: #edf2f7;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: monospace;
            font-size: 13px;
        }
        .docs-content pre {
            background: #2d3748;
            color: #e2e8f0;
            padding: 12px 15px;
            border-radius: 6px;
            overflow-x: auto;
            margin: 10px 0;
        }
        .docs-content pre code {
            background: none;
            padding: 0;
            color: #e2e8f0;
        }
        .docs-content table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }
        .docs-content th, .docs-content td {
            border: 1px solid #e2e8f0;
            padding: 8px 12px;
            text-align: left;
        }
        .docs-content th { background: #f7fafc; font-weight: 600; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📦 Gerenciador de Dumps</h1>
            <a href="index.php" class="back-btn">← Voltar</a>
        </div>
        
        <div class="main-card">
            <div class="info-box">
                <strong>ℹ️ Regra:</strong> Sempre que realizar uma migration no banco de dados, gere um novo dump para manter o ambiente de testes sincronizado.
            </div>

            <div class="action-area">
                <div class="input-group">
                    <label for="group-num">Grupo:</label>
                    <input type="number" id="group-num" value="2" min="1">
                </div>
                <button class="btn-generate" id="btn-generate" onclick="generateDump()">
                    <div class="spinner" id="spinner"></div>
                    <span id="btn-text">🚀 Gerar Novo Dump</span>
                </button>
            </div>
            <div id="status-msg"></div>

            <div class="file-list">
                <h2>📁 Arquivos Disponíveis <span style="font-weight: normal; color: #a0aec0;">(_dumps/)</span></h2>
                <?php if (empty($files)): ?>
                    <div class="empty-state">
                        <p>📭 Nenhum dump encontrado.</p>
                        <p style="font-size: 14px;">Clique em "Gerar Novo Dump" para criar o primeiro.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($files as $file): ?>
                        <div class="file-item">
                            <div class="file-info">
                                <span class="file-name"><?php echo htmlspecialchars($file['name']); ?></span>
                                <span class="file-meta">📅 <?php echo $file['date']; ?> • 💾 <?php echo $file['size']; ?></span>
                            </div>
                            <div class="file-actions">
                                <a href="../_dumps/<?php echo urlencode($file['name']); ?>" download>⬇ Baixar</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Mini Documentação -->
            <details class="docs-section">
                <summary>📖 Como Funciona (Documentação)</summary>
                <div class="docs-content">
                    <h3>🎯 O que é um Dump?</h3>
                    <p>Um <strong>dump</strong> é uma cópia completa do banco de dados em formato SQL. Ele contém toda a estrutura (tabelas, índices) e os dados atuais do sistema.</p>

                    <h3>📋 Quando Gerar um Dump?</h3>
                    <p>Sempre que você realizar uma <strong>migration</strong> (alteração na estrutura do banco), é obrigatório gerar um novo dump para que:</p>
                    <ul>
                        <li>O time de QA tenha o banco sincronizado com o código</li>
                        <li>Outros desenvolvedores possam replicar o ambiente</li>
                        <li>Evitar erros de "tabela não encontrada" ou "coluna inexistente"</li>
                    </ul>

                    <h3>⚙️ Como Funciona Tecnicamente</h3>
                    <p>Ao clicar em "Gerar Novo Dump", o sistema:</p>
                    <ol>
                        <li>Conecta ao banco MariaDB via <strong>PHP PDO</strong></li>
                        <li>Lista todas as tabelas do banco <code>guideway_lms_db</code></li>
                        <li>Exporta a estrutura (CREATE TABLE) e os dados (INSERT) de cada tabela</li>
                        <li>Salva o arquivo na pasta <code>_dumps/</code> na raiz do projeto</li>
                    </ol>
                    <p><em>Nota: Esta abordagem usa PHP puro, sem dependência de comandos externos como mysqldump.</em></p>

                    <h3>📝 Padrão de Nomenclatura</h3>
                    <table>
                        <tr><th>Formato</th><td><code>grupo[NUMERO]_[DDMMAA].sql</code></td></tr>
                        <tr><th>Exemplo</th><td><code>grupo2_131225.sql</code> (Grupo 2, 13/12/2025)</td></tr>
                    </table>

                    <h3>✅ Checklist Pós-Migration</h3>
                    <ol>
                        <li>Rodar o comando de dump (via este gerenciador)</li>
                        <li>Verificar se o arquivo foi gerado corretamente</li>
                        <li>Commitar o arquivo <code>.sql</code> junto com o código da migration</li>
                    </ol>
                </div>
            </details>
        </div>
    </div>

    <script>
        function generateDump() {
            const btn = document.getElementById('btn-generate');
            const spinner = document.getElementById('spinner');
            const btnText = document.getElementById('btn-text');
            const statusMsg = document.getElementById('status-msg');
            const group = document.getElementById('group-num').value;

            // UI Loading State
            btn.disabled = true;
            spinner.style.display = 'block';
            btnText.textContent = 'Gerando...';
            statusMsg.style.display = 'none';

            // Request
            const formData = new FormData();
            formData.append('group', group);

            fetch('generate_dump.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                statusMsg.style.display = 'block';
                if (data.success) {
                    statusMsg.className = 'success';
                    statusMsg.textContent = '✅ ' + data.message + ' (' + data.size + ')';
                    setTimeout(() => location.reload(), 2000);
                } else {
                    statusMsg.className = 'error';
                    statusMsg.textContent = '❌ ' + data.message;
                    console.error('Debug info:', data);
                }
            })
            .catch(error => {
                statusMsg.style.display = 'block';
                statusMsg.className = 'error';
                statusMsg.textContent = '❌ Erro de conexão com o servidor.';
                console.error(error);
            })
            .finally(() => {
                btn.disabled = false;
                spinner.style.display = 'none';
                btnText.textContent = '🚀 Gerar Novo Dump';
            });
        }
    </script>
</body>
</html>
