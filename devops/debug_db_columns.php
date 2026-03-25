<?php
require_once __DIR__ . '/auth.php';

/**
 * Script para Debugar Colunas de Tabelas
 * 
 * Este script lista todas as colunas de uma tabela específica
 * para ajudar a identificar problemas de schema.
 */

// Define constantes necessárias
// define('_JEXEC', 1);
// define('JPATH_BASE', dirname(__DIR__));

// Inclui o framework do Joomla
require_once JPATH_BASE . '/includes/defines.php';
require_once JPATH_BASE . '/includes/framework.php';

use Joomla\CMS\Factory;

// Carrega aliases se necessário
if (!class_exists('JFactory')) {
    class_alias('Joomla\\CMS\\Factory', 'JFactory');
}

// ---------------------------------------------------------
// DEFINA AQUI A TABELA QUE VOCÊ QUER INVESTIGAR
// ---------------------------------------------------------
$tableName = '#__splms_course_announcements'; 
// ---------------------------------------------------------
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Debug: Colunas da Tabela</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        h1 {
            color: #667eea;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e9ecef;
        }
        th {
            background: #667eea;
            color: white;
            font-weight: 600;
        }
        tr:hover {
            background: #f8f9fa;
        }
        .highlight {
            background: #fff3cd;
        }
        .error {
            background: #f8d7da;
            border-left: 4px solid #dc3545;
            padding: 15px;
            border-radius: 4px;
            margin: 15px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Estrutura da Tabela: <?php echo htmlspecialchars($tableName); ?></h1>

<?php
try {
    $db = Factory::getDbo();
    $columns = $db->getTableColumns($tableName);

    if (empty($columns)) {
        echo '<div class="error">';
        echo '<strong>❌ Erro:</strong> A tabela ' . htmlspecialchars($tableName) . ' não foi encontrada ou está vazia.';
        echo '</div>';
    } else {
        echo '<p><strong>Total de colunas:</strong> ' . count($columns) . '</p>';
        echo '<table>';
        echo '<tr><th>Nome da Coluna</th><th>Tipo</th></tr>';
        
        foreach ($columns as $columnName => $columnType) {
            // Destaca colunas importantes
            $rowClass = '';
            if (in_array($columnName, ['order_user_id', 'user_id', 'created_at', 'created_on', 'message', 'description'])) {
                $rowClass = 'class="highlight"';
            }
            
            echo '<tr ' . $rowClass . '>';
            echo '<td><strong>' . htmlspecialchars($columnName) . '</strong></td>';
            echo '<td>' . htmlspecialchars($columnType) . '</td>';
            echo '</tr>';
        }
        
        echo '</table>';
    }
} catch (Exception $e) {
    echo '<div class="error">';
    echo '<strong>❌ Erro:</strong><br>';
    echo htmlspecialchars($e->getMessage());
    echo '</div>';
}
?>
        
        <div style="margin-top: 30px; padding: 15px; background: #e7f3ff; border-radius: 6px;">
            <strong>💡 Dica:</strong> Para verificar outra tabela, edite a variável <code>$tableName</code> no início do arquivo.
        </div>
    </div>
</body>
</html>
