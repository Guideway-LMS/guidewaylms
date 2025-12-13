<?php
/**
 * Index da Pasta de Testes
 * 
 * Este arquivo exibe o README.md em formato HTML para facilitar
 * o acesso à documentação dos scripts de teste.
 */

// Carrega o Parsedown para converter Markdown para HTML
// Se não tiver instalado, exibe em texto puro
$readmePath = __DIR__ . '/README.md';

if (!file_exists($readmePath)) {
    die('Arquivo README.md não encontrado!');
}

$markdown = file_get_contents($readmePath);

// Função para gerar slugs limpos (IDs)
function slugify($text) {
    // Converter para minúsculas
    $text = mb_strtolower($text, 'UTF-8');
    
    // Remover acentos
    $text = str_replace(
        ['á','à','ã','â','ä','é','è','ê','ë','í','ì','î','ï','ó','ò','õ','ô','ö','ú','ù','û','ü','ç','ñ'],
        ['a','a','a','a','a','e','e','e','e','i','i','i','i','o','o','o','o','o','u','u','u','u','c','n'],
        $text
    );
    
    // Substituir caracteres não alfanuméricos por hífen
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    
    // Remover hífens duplicados e das pontas
    $text = trim(preg_replace('/-+/', '-', $text), '-');
    
    return $text;
}

// Função simples para converter markdown básico para HTML
function convertMarkdownToHTML($text) {
    // Headers with ID generation
    $text = preg_replace_callback('/^# (.*?)$/m', function($matches) {
        $id = slugify($matches[1]);
        return "<h1 id=\"$id\">{$matches[1]}</h1>";
    }, $text);
    
    $text = preg_replace_callback('/^## (.*?)$/m', function($matches) {
        $id = slugify($matches[1]);
        return "<h2 id=\"$id\">{$matches[1]}</h2>";
    }, $text);

    $text = preg_replace_callback('/^### (.*?)$/m', function($matches) {
        $id = slugify($matches[1]);
        return "<h3 id=\"$id\">{$matches[1]}</h3>";
    }, $text);
    
    // Bold
    $text = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $text);
    
    // Italic
    $text = preg_replace('/\*(.*?)\*/', '<em>$1</em>', $text);
    
    // Code blocks
    $text = preg_replace('/```(.*?)```/s', '<pre><code>$1</code></pre>', $text);
    
    // Inline code
    $text = preg_replace('/`(.*?)`/', '<code>$1</code>', $text);
    
    // Links
    $text = preg_replace('/\[(.*?)\]\((.*?)\)/', '<a href="$2">$1</a>', $text);
    
    // Unordered lists
    $text = preg_replace('/^- (.*?)$/m', '<li>$1</li>', $text);
    $text = preg_replace('/(<li>.*<\/li>)\n/s', '<ul>$1</ul>', $text);
    
    // Line breaks
    $text = nl2br($text);
    
    return $text;
}

$html = convertMarkdownToHTML($markdown);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Documentação - Scripts de Teste</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            line-height: 1.6;
            color: #333;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px;
            text-align: center;
        }
        .header h1 {
            font-size: 32px;
            margin-bottom: 10px;
        }
        .header p {
            opacity: 0.9;
        }
        .content {
            padding: 40px;
        }
        .test-columns {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin: 30px 0;
        }
        .test-column h2 {
            font-size: 20px;
            margin-bottom: 20px;
            color: #4a5568;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 10px;
        }
        .quick-links {
            display: grid;
            gap: 15px;
        }
        .quick-link {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 8px;
            text-decoration: none;
            text-align: center;
            transition: transform 0.2s;
        }
        .quick-link:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        .quick-link h3 {
            margin-bottom: 10px;
            font-size: 18px;
        }
        .quick-link p {
            font-size: 14px;
            opacity: 0.9;
        }
        .markdown-content {
            color: #333;
        }
        .markdown-content h1 {
            color: #667eea;
            margin: 30px 0 20px 0;
            padding-bottom: 10px;
            border-bottom: 2px solid #667eea;
        }
        .markdown-content h2 {
            color: #764ba2;
            margin: 25px 0 15px 0;
            font-size: 24px;
        }
        .markdown-content h3 {
            color: #333;
            margin: 20px 0 10px 0;
            font-size: 18px;
        }
        .markdown-content code {
            background: #f4f4f4;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
            font-size: 14px;
        }
        .markdown-content pre {
            background: #2d2d2d;
            color: #f8f8f2;
            padding: 15px;
            border-radius: 6px;
            overflow-x: auto;
            margin: 15px 0;
        }
        .markdown-content pre code {
            background: none;
            padding: 0;
            color: #f8f8f2;
        }
        .markdown-content ul {
            margin: 10px 0;
            padding-left: 30px;
        }
        .markdown-content li {
            margin: 5px 0;
        }
        .markdown-content a {
            color: #667eea;
            text-decoration: none;
        }
        .markdown-content a:hover {
            text-decoration: underline;
        }
        .markdown-content hr {
            border: none;
            border-top: 2px solid #e9ecef;
            margin: 30px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🧪 Scripts de Teste</h1>
            <p>Documentação e Ferramentas de Debug para Guideway LMS</p>
        </div>
        
        <div class="content">
            <div class="test-columns">
                <!-- Coluna A: Banco de Dados -->
                <div class="test-column">
                    <h2>🗄️ Banco de Dados & Model</h2>
                    <div class="quick-links">
                        <a href="test_announcements_check.php" class="quick-link">
                            <h3>🧪 Teste de Avisos</h3>
                            <p>Verifica o Model de Announcements</p>
                        </a>
                        <a href="create_test_data.php" class="quick-link">
                            <h3>🛠️ Criar Dados</h3>
                            <p>Gera matrícula de teste</p>
                        </a>
                        <a href="debug_db_columns.php" class="quick-link">
                            <h3>🔍 Debug Schema</h3>
                            <p>Lista colunas da tabela</p>
                        </a>
                    </div>
                </div>

                <!-- Coluna B: Inteligência Artificial -->
                <div class="test-column">
                    <h2>🤖 Inteligência Artificial</h2>
                    <div class="quick-links">
                        <a href="groq_smoke_test.php" class="quick-link">
                            <h3>🤖 Groq Smoke Test</h3>
                            <p>Teste de conexão com IA</p>
                        </a>
                        <a href="#integracao-groq-api-documentacao-tecnica" class="quick-link">
                            <h3>📚 Doc. Groq API</h3>
                            <p>Ir para documentação</p>
                        </a>
                    </div>
                </div>
            </div>
            
            <hr style="border: none; border-top: 2px solid #e9ecef; margin: 30px 0;">
            
            <div class="markdown-content">
                <?php echo $html; ?>
            </div>
        </div>
    </div>
</body>
</html>
