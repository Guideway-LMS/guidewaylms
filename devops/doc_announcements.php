<?php
require_once __DIR__ . '/auth.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doc: Mural de Avisos - Guideway LMS</title>
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
            --accent-purple: #a855f7;
            --accent-blue: #3b82f6;
            --accent-green: #22c55e;
            --accent-orange: #f97316;
            --accent-yellow: #eab308;
            --accent-red: #f43f5e;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', -apple-system, sans-serif; background: var(--bg-primary); color: var(--text-primary); min-height: 100vh; line-height: 1.6; }

        /* === HEADER === */
        .header { background: linear-gradient(135deg, #1e293b 0%, #0f172a 50%, #1e1b4b 100%); padding: 40px 50px; border-bottom: 1px solid var(--border); position: relative; overflow: hidden; display: flex; justify-content: space-between; align-items: center; }
        .header::before { content: ''; position: absolute; top: -50%; right: -10%; width: 400px; height: 400px; background: radial-gradient(circle, rgba(168,85,247,0.08) 0%, transparent 70%); border-radius: 50%; }
        .header-inner { max-width: 1000px; width: 100%; margin: 0 auto; display: flex; justify-content: space-between; align-items: center; position: relative; z-index: 1; }
        .header h1 { font-size: 30px; font-weight: 800; letter-spacing: -0.5px; }
        .header h1 span { background: linear-gradient(135deg, var(--accent-purple), var(--accent-blue)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .header p { color: var(--text-secondary); font-size: 14px; margin-top: 6px; }

        .btn-back { background: var(--bg-card); border: 1px solid var(--border); color: var(--text-primary); padding: 10px 20px; border-radius: 8px; text-decoration: none; font-size: 14px; transition: 0.2s; font-weight: 500;}
        .btn-back:hover { background: var(--bg-hover); }

        /* === CONTENT === */
        .main { max-width: 1000px; margin: 0 auto; padding: 40px 50px; }
        
        .doc-section { background: var(--bg-card); border: 1px solid var(--border); border-radius: 12px; padding: 32px; box-shadow: 0 4px 20px rgba(0,0,0,0.15); margin-bottom: 30px; position: relative; }
        .doc-section .badge { position: absolute; top: 32px; right: 32px; font-size: 11px; font-weight: 700; background: rgba(255,255,255,0.05); padding: 4px 10px; border-radius: 20px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; }
        
        .doc-section h2 { color: var(--text-primary); font-size: 20px; margin: 0 0 20px; display: flex; align-items: center; gap: 12px; border-bottom: 1px solid var(--border); padding-bottom: 16px;}
        .doc-section h2 .icon { width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 18px; background: rgba(168,85,247,0.15); color: var(--accent-purple); }
        .doc-section p { color: var(--text-secondary); margin-bottom: 15px; }
        .doc-section strong { color: var(--text-primary); }
        .doc-section ul { padding-left: 20px; color: var(--text-secondary); margin-bottom: 15px; }
        .doc-section li { margin-bottom: 6px; }
        .doc-section code { background: rgba(0,0,0,0.3); border: 1px solid var(--border); padding: 2px 6px; border-radius: 4px; color: var(--accent-purple); font-size: 13px; font-family: monospace; }
        
        .db-table { width: 100%; border-collapse: collapse; margin-top: 15px; background: var(--bg-primary); border-radius: 8px; overflow: hidden; border: 1px solid var(--border); }
        .db-table th { text-align: left; padding: 12px 16px; font-size: 12px; font-weight: 600; color: var(--text-muted); text-transform: uppercase; border-bottom: 2px solid var(--border); }
        .db-table td { padding: 12px 16px; font-size: 14px; color: var(--text-secondary); border-bottom: 1px solid var(--border); }
        .db-table tr:last-child td { border-bottom: none; }
        .db-table td code { background: transparent; border: none; padding: 0; }
        .db-table .pk { color: var(--accent-yellow); font-weight: 700; display: inline-flex; align-items: center; gap: 4px; }
        .db-table .fk { color: var(--accent-blue); font-weight: 700; display: inline-flex; align-items: center; gap: 4px; }

        .code-block { background: #0f172a; padding: 16px; border-radius: 8px; border: 1px solid var(--border); font-family: monospace; font-size: 13px; color: var(--text-primary); overflow-x: auto; margin: 15px 0; white-space: pre-wrap; }
        .code-block .comment { color: #64748b; }
        .code-block .keyword { color: var(--accent-orange); }
        .code-block .string { color: var(--accent-green); }
        .code-block .variable { color: var(--accent-blue); }

        .feature-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 15px; }
        .feature-card { background: rgba(255,255,255,0.02); border: 1px solid var(--border); border-radius: 8px; padding: 20px; transition: 0.2s; }
        .feature-card:hover { border-color: var(--accent-purple); background: rgba(168,85,247,0.02); }
        .feature-card h3 { font-size: 16px; color: var(--accent-purple); margin-bottom: 10px; display: flex; align-items: center; gap: 8px; }
    </style>
</head>
<body>

    <div class="header">
        <div class="header-inner">
            <div>
                <h1>📚 Documentação <span>Mural de Avisos</span></h1>
                <p>Referência Técnica de Arquitetura e Integrações do SPLMS (Announcements)</p>
            </div>
            <a href="index.php" class="btn-back">⬅ Voltar ao Dashboard</a>
        </div>
    </div>

    <div class="main">

        <!-- 1. Visão Geral -->
        <div class="doc-section">
            <span class="badge">Architecture</span>
            <h2><div class="icon">📢</div> Visão Geral do Módulo</h2>
            <p>A funcionalidade "Mural de Avisos" (<em>Announcements</em>) serve como o painel de comunicação unidirecional oficial entre professores/administradores e os alunos matriculados em um curso específico.</p>
            <ul>
                <li><strong>Backend:</strong> Admins usam a View <code>edit.php</code> para escrever o comunicado HTML (através do editor `message`). Eles selecionam o ID do curso a qual pertence.</li>
                <li><strong>Frontend:</strong> Os alunos veem os avisos através da infraestrutura de models e views do frontend, acessível na página do respectivo curso. Tudo é puxado via queries que listam mensagens publicadas vinculadas ao <code>course_id</code>.</li>
            </ul>
        </div>

        <!-- 2. Database Schema -->
        <div class="doc-section">
            <span class="badge">Database</span>
            <h2><div class="icon">🗄️</div> Estrutura de Banco de Dados</h2>
            <p>O módulo é governado pela tabela principal <code>#__splms_course_announcements</code>, gerenciada via código pelo Table Model <code>SplmsTableAnnouncement</code>. O Joomla preenche automaticamente o horário e autoria.</p>
            
            <table class="db-table">
                <thead>
                    <tr>
                        <th>Coluna</th>
                        <th>Tipo SQL (MySQL)</th>
                        <th>Papel do Campo na Aplicação</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><span class="pk">🔑 id</span></td>
                        <td>INT(11) AUTO_INCREMENT</td>
                        <td>Identificador Único do Aviso (Primary Key).</td>
                    </tr>
                    <tr>
                        <td><code>title</code></td>
                        <td>VARCHAR(255)</td>
                        <td>Título do aviso lido pelos alunos. É validado como <em>required</em> nos formulários.</td>
                    </tr>
                    <tr>
                        <td><code>message</code></td>
                        <td>TEXT</td>
                        <td>Corpo principal do aviso gerado pelo professor (Salva conteúdo HTML Seguro).</td>
                    </tr>
                    <tr>
                        <td><span class="fk">🔗 course_id</span></td>
                        <td>INT(11)</td>
                        <td>Chave Extrangeira (Foreign Key). Associa este aviso à tabela <code>#__splms_courses</code>.</td>
                    </tr>
                    <tr>
                        <td><code>created_by</code></td>
                        <td>INT(11)</td>
                        <td>ID do Usuário logado do JFactory que disparou o aviso.</td>
                    </tr>
                    <tr>
                        <td><code>created_at</code></td>
                        <td>DATETIME</td>
                        <td>Data e hora que o script de Tabela inseriu ao salvar (<code>JFactory::getDate()</code>).</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- 3. Integração com Inteligência Artificial -->
        <div class="doc-section">
            <span class="badge">Artificial Intelligence</span>
            <h2><div class="icon">🤖</div> Padrões de Injeção da IA (Edição)</h2>
            <p>A diferença deste mural para extensões genéricas é a capacidade de produzir textos institucionais com a <strong>Groq API</strong> inserida nativamente no editor.</p>
            
            <p>Dentro do arquivo <code>administrator/components/com_splms/views/announcement/tmpl/edit.php</code>, fizemos o seguinte gancho ao renderizar o campo _Message_:</p>

            <div class="code-block">
<span class="comment">// === Toolbar AI Hibrida: Injeção Específica para Avisos ===</span>
<span class="keyword">list</span>(<span class="variable">$hideGenerateQuestions</span>) = [<span class="keyword">true</span>]; <span class="comment">// Força ocultar as opções de PDF Quiz!</span>

<span class="comment">// 1. Toolbar de Geração (Botão "Conectar c/ Inteligência Artificial")</span>
<span class="keyword">if</span> (Factory::getUser()->authorise(<span class="string">'ai.generate'</span>, <span class="string">'com_splms'</span>)) {
    <span class="keyword">include</span> JPATH_COMPONENT_ADMINISTRATOR . <span class="string">'/views/announcement/tmpl/toolbar_ai_chat.php'</span>;
}

<span class="comment">// 2. Toolbar de Refinamento (Gerar Resumo, Corrigir Gramática, Expandir)</span>
<span class="keyword">if</span> (Factory::getUser()->authorise(<span class="string">'ai.refine'</span>, <span class="string">'com_splms'</span>)) {
    <span class="keyword">include</span> JPATH_COMPONENT_ADMINISTRATOR . <span class="string">'/views/announcement/tmpl/toolbar_ai.php'</span>;
}
            </div>

            <div class="feature-grid">
                <div class="feature-card">
                    <h3>💬 Prompt Creator (toolbar_ai_chat.php)</h3>
                    <p>Interface Pop-Up. O professor escreve "Me ajude a fazer um aviso de Feliz Ano Novo" e a requisição vai para o <code>GuidewayAIHelper::processPrompt()</code> gerar texto livre em HTML de volta ao TinyMCE.</p>
                </div>
                <div class="feature-card">
                    <h3>✨ Text Refiner (toolbar_ai.php)</h3>
                    <p>Barra fixa na UI do campo. Extrai os textos do banco, envia para a Groq pedindo "Resumir" ou "Revisar Gramática Ortográfica" do aviso atual, sobrescrevendo a área formatada.</p>
                </div>
            </div>
            
            <p style="margin-top: 15px; font-size: 13px;">
                💡 <strong>Nota de Arquitetura:</strong> Nós evitamos hardcode de HTML desnecessário injetado no PHP e reaproveitamos de forma inteligente os toolbars (criados na Lições) parametrizando variáveis booleanas curtas como <code>$hideGenerateQuestions</code> para reciclar o painel.
            </p>
        </div>

    </div>

</body>
</html>
