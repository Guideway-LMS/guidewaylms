<?php
require_once __DIR__ . '/auth.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doc: Fórum de Dúvidas - Guideway LMS</title>
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
        .header::before { content: ''; position: absolute; top: -50%; right: -10%; width: 400px; height: 400px; background: radial-gradient(circle, rgba(59,130,246,0.08) 0%, transparent 70%); border-radius: 50%; }
        .header-inner { max-width: 1000px; width: 100%; margin: 0 auto; display: flex; justify-content: space-between; align-items: center; position: relative; z-index: 1; }
        .header h1 { font-size: 30px; font-weight: 800; letter-spacing: -0.5px; }
        .header h1 span { background: linear-gradient(135deg, var(--accent-blue), var(--accent-cyan)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .header p { color: var(--text-secondary); font-size: 14px; margin-top: 6px; }

        .btn-back { background: var(--bg-card); border: 1px solid var(--border); color: var(--text-primary); padding: 10px 20px; border-radius: 8px; text-decoration: none; font-size: 14px; transition: 0.2s; font-weight: 500;}
        .btn-back:hover { background: var(--bg-hover); }

        /* === CONTENT === */
        .main { max-width: 1000px; margin: 0 auto; padding: 40px 50px; }
        
        .doc-section { background: var(--bg-card); border: 1px solid var(--border); border-radius: 12px; padding: 32px; box-shadow: 0 4px 20px rgba(0,0,0,0.15); margin-bottom: 30px; position: relative; }
        .doc-section .badge { position: absolute; top: 32px; right: 32px; font-size: 11px; font-weight: 700; background: rgba(255,255,255,0.05); padding: 4px 10px; border-radius: 20px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; }
        
        .doc-section h2 { color: var(--text-primary); font-size: 20px; margin: 0 0 20px; display: flex; align-items: center; gap: 12px; border-bottom: 1px solid var(--border); padding-bottom: 16px;}
        .doc-section h2 .icon { width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 18px; background: rgba(59,130,246,0.15); color: var(--accent-blue); }
        .doc-section p { color: var(--text-secondary); margin-bottom: 15px; }
        .doc-section strong { color: var(--text-primary); }
        .doc-section ul { padding-left: 20px; color: var(--text-secondary); margin-bottom: 15px; }
        .doc-section li { margin-bottom: 6px; }
        .doc-section code { background: rgba(0,0,0,0.3); border: 1px solid var(--border); padding: 2px 6px; border-radius: 4px; color: var(--accent-blue); font-size: 13px; font-family: monospace; }
        
        .db-table { width: 100%; border-collapse: collapse; margin-top: 15px; background: var(--bg-primary); border-radius: 8px; overflow: hidden; border: 1px solid var(--border); margin-bottom: 25px;}
        .db-table th { text-align: left; padding: 12px 16px; font-size: 12px; font-weight: 600; color: var(--text-muted); text-transform: uppercase; border-bottom: 2px solid var(--border); }
        .db-table td { padding: 12px 16px; font-size: 14px; color: var(--text-secondary); border-bottom: 1px solid var(--border); }
        .db-table tr:last-child td { border-bottom: none; }
        .db-table td code { background: transparent; border: none; padding: 0; }
        .db-table .pk { color: var(--accent-yellow); font-weight: 700; display: inline-flex; align-items: center; gap: 4px; }
        .db-table .fk { color: var(--accent-purple); font-weight: 700; display: inline-flex; align-items: center; gap: 4px; }

        .code-block { background: #0f172a; padding: 16px; border-radius: 8px; border: 1px solid var(--border); font-family: monospace; font-size: 13px; color: var(--text-primary); overflow-x: auto; margin: 15px 0; white-space: pre-wrap; }
        .code-block .comment { color: #64748b; }
        .code-block .keyword { color: var(--accent-orange); }
        .code-block .string { color: var(--accent-green); }
        .code-block .variable { color: var(--accent-blue); }

        .feature-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 15px; }
        .feature-card { background: rgba(255,255,255,0.02); border: 1px solid var(--border); border-radius: 8px; padding: 20px; transition: 0.2s; }
        .feature-card:hover { border-color: var(--accent-blue); background: rgba(59,130,246,0.02); }
        .feature-card h3 { font-size: 16px; color: var(--accent-blue); margin-bottom: 10px; display: flex; align-items: center; gap: 8px; }
    </style>
</head>
<body>

    <div class="header">
        <div class="header-inner">
            <div>
                <h1>📚 Documentação <span>Fórum de Dúvidas</span></h1>
                <p>Referência Técnica de Arquitetura e Estrutura do SPLMS (Forums)</p>
            </div>
            <a href="index.php" class="btn-back">⬅ Voltar ao Dashboard</a>
        </div>
    </div>

    <div class="main">

        <!-- 1. Visão Geral -->
        <div class="doc-section">
            <span class="badge">Architecture</span>
            <h2><div class="icon">💬</div> Visão Geral do Módulo</h2>
            <p>A funcionalidade "Fórum de Dúvidas" (<em>Forums</em>) serve como o canal de comunicação bidirecional entre os alunos matriculados e os professores/administradores do curso.</p>
            <ul>
                <li><strong>Frontend:</strong> Os alunos enviam suas dúvidas através da página do curso. Podem visualizar as respostas e também interagir com outras dúvidas através de votos (upvotes/downvotes).</li>
                <li><strong>Backend:</strong> Admins e professores usam a View <code>forums</code> (lista de dúvidas) e <code>forum</code> (edição/visualização individual) para gerenciar, editar o título e corpo da dúvida, vincular ao curso e marcar se o tópico foi <strong>Resolvido</strong> (<code>solved</code>).</li>
            </ul>
        </div>

        <!-- 2. Database Schema -->
        <div class="doc-section">
            <span class="badge">Database</span>
            <h2><div class="icon">🗄️</div> Estrutura de Banco de Dados</h2>
            <p>O módulo é estruturado em três tabelas separadas que garantem a escalabilidade de perguntas, respostas e sistema de votos. As lógicas de tabela ficam em Models/Tables no J5.</p>
            
            <h3 style="color: var(--text-primary); font-size: 15px; margin-bottom: 10px; font-family: monospace;">#__splms_forum_questions</h3>
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
                        <td>Identificador Único da Dúvida/Tópico (Primary Key).</td>
                    </tr>
                    <tr>
                        <td><span class="fk">🔗 course_id</span></td>
                        <td>INT(11)</td>
                        <td>Chave Estrangeira. Associa esta dúvida ao curso correspondente em <code>#__splms_courses</code>.</td>
                    </tr>
                    <tr>
                        <td><code>user_id</code></td>
                        <td>INT(11)</td>
                        <td>ID do Usuário (aluno/professor) que abriu o tópico no fórum.</td>
                    </tr>
                    <tr>
                        <td><code>title</code></td>
                        <td>VARCHAR(255)</td>
                        <td>Título principal do tópico do fórum.</td>
                    </tr>
                    <tr>
                        <td><code>body</code></td>
                        <td>TEXT</td>
                        <td>Corpo principal da dúvida descrita pelo usuário em HTML.</td>
                    </tr>
                    <tr>
                        <td><code>votes</code></td>
                        <td>INT(11) DEFAULT 0</td>
                        <td>Contagem total em cache do saldo de votos deste tópico.</td>
                    </tr>
                    <tr>
                        <td><code>views</code></td>
                        <td>INT(11) DEFAULT 0</td>
                        <td>Contador de visualizações da dúvida.</td>
                    </tr>
                    <tr>
                        <td><code>solved</code></td>
                        <td>TINYINT(1) DEFAULT 0</td>
                        <td>Booleano indicando se o problema/dúvida foi resolvido (1) ou não (0).</td>
                    </tr>
                    <tr>
                        <td><code>tags</code></td>
                        <td>TEXT</td>
                        <td>Tags em texto para categorização livre do próprio tópico.</td>
                    </tr>
                    <tr>
                        <td><code>created_on</code></td>
                        <td>DATETIME</td>
                        <td>Data e hora que a dúvida foi postada (<code>JFactory::getDate()</code>).</td>
                    </tr>
                </tbody>
            </table>

            <h3 style="color: var(--text-primary); font-size: 15px; margin-bottom: 10px; font-family: monospace;">#__splms_forum_answers</h3>
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
                        <td>Identificador Único da Resposta (Primary Key).</td>
                    </tr>
                    <tr>
                        <td><span class="fk">🔗 question_id</span></td>
                        <td>INT(11)</td>
                        <td>Chave Estrangeira apontando para a pergunta em <code>#__splms_forum_questions</code>.</td>
                    </tr>
                    <tr>
                        <td><code>user_id</code></td>
                        <td>INT(11)</td>
                        <td>ID do Usuário que respondeu a dúvida.</td>
                    </tr>
                    <tr>
                        <td><code>body</code></td>
                        <td>TEXT</td>
                        <td>Conteúdo da resposta submetida.</td>
                    </tr>
                    <tr>
                        <td><code>votes</code></td>
                        <td>INT(11) DEFAULT 0</td>
                        <td>Contagem de curtidas (upvotes/downvotes) apenas desta resposta específica.</td>
                    </tr>
                    <tr>
                        <td><code>is_accepted</code></td>
                        <td>TINYINT(1) DEFAULT 0</td>
                        <td>Define se foi selecionada como a resposta correta/definitiva (1).</td>
                    </tr>
                    <tr>
                        <td><code>created_on</code></td>
                        <td>DATETIME</td>
                        <td>Data e hora de criação.</td>
                    </tr>
                </tbody>
            </table>

            <h3 style="color: var(--text-primary); font-size: 15px; margin-bottom: 10px; font-family: monospace;">#__splms_forum_votes</h3>
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
                        <td>Identificador Único do Voto (Primary Key).</td>
                    </tr>
                    <tr>
                        <td><code>user_id</code></td>
                        <td>INT(11)</td>
                        <td>ID de quem realizou o voto. A constraint <code>UNIQUE KEY</code> baseada neste campo e nos listados abaixo evita votos duplicados.</td>
                    </tr>
                    <tr>
                        <td><span class="fk">🔗 item_id</span></td>
                        <td>INT(11)</td>
                        <td>ID do objeto sofrendo voto. Pode ser da pergunta ou da resposta.</td>
                    </tr>
                    <tr>
                        <td><code>item_type</code></td>
                        <td>VARCHAR(20)</td>
                        <td>Diz se está votando na 'question' (pergunta) ou na 'answer' (resposta) - define para onde o <code>item_id</code> está apontando.</td>
                    </tr>
                    <tr>
                        <td><code>vote</code></td>
                        <td>TINYINT(2)</td>
                        <td>Valor do voto (+1 ou -1) para upvote / downvote.</td>
                    </tr>
                </tbody>
            </table>
        </div>



    </div>

</body>
</html>
