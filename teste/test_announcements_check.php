<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teste: Model de Avisos</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            font-size: 28px;
            margin-bottom: 10px;
        }
        .header p {
            opacity: 0.9;
            font-size: 14px;
        }
        .content {
            padding: 30px;
        }
        .section {
            margin-bottom: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
            border-left: 4px solid #667eea;
        }
        .section h2 {
            font-size: 18px;
            margin-bottom: 15px;
            color: #333;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #e9ecef;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .info-label {
            font-weight: 600;
            color: #666;
        }
        .info-value {
            color: #333;
        }
        .success {
            background: #d4edda;
            border-left-color: #28a745;
        }
        .error {
            background: #f8d7da;
            border-left-color: #dc3545;
        }
        .warning {
            background: #fff3cd;
            border-left-color: #ffc107;
        }
        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .badge-success {
            background: #28a745;
            color: white;
        }
        .announcement-card {
            background: white;
            padding: 20px;
            margin-bottom: 15px;
            border-radius: 8px;
            border: 1px solid #e9ecef;
        }
        .announcement-card h3 {
            color: #667eea;
            margin-bottom: 10px;
            font-size: 20px;
        }
        .announcement-meta {
            color: #666;
            font-size: 13px;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e9ecef;
        }
        .announcement-body {
            color: #333;
            line-height: 1.6;
        }
        pre {
            background: #2d2d2d;
            color: #f8f8f2;
            padding: 15px;
            border-radius: 6px;
            overflow-x: auto;
            font-size: 13px;
            line-height: 1.5;
        }
        code {
            background: #f4f4f4;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🧪 Teste: Model de Avisos (Announcements)</h1>
            <p>Verificação de Segurança e Funcionalidade - Teste SQL Direto</p>
        </div>
        <div class="content">
<?php
/**
 * Script de Teste para SplmsModelAnnouncements
 * Versão Simplificada - SQL Direto (sem dependências da aplicação Joomla)
 */

// Define constantes necessárias
define('_JEXEC', 1);
define('JPATH_BASE', dirname(__DIR__));

// Inclui apenas o framework mínimo
require_once JPATH_BASE . '/includes/defines.php';
require_once JPATH_BASE . '/includes/framework.php';

use Joomla\CMS\Factory;

try {
    // Obtém conexão com o banco
    $db = Factory::getDbo();
    
    // Parâmetros do teste
    $userId = 1;   // ID do usuário de teste
    $courseId = 1; // ID do curso de teste
    
    echo '<div class="section">';
    echo '<h2>📋 Parâmetros do Teste</h2>';
    echo '<div class="info-row">';
    echo '<span class="info-label">Usuário ID:</span>';
    echo '<span class="info-value">' . $userId . '</span>';
    echo '</div>';
    echo '<div class="info-row">';
    echo '<span class="info-label">Curso ID:</span>';
    echo '<span class="info-value">' . $courseId . '</span>';
    echo '</div>';
    echo '</div>';
    
    // Primeiro: Verifica se o usuário está matriculado
    echo '<div class="section">';
    echo '<h2>🔐 Verificando Matrícula...</h2>';
    
    $queryEnrollment = $db->getQuery(true)
        ->select('COUNT(*)')
        ->from($db->quoteName('#__splms_orders'))
        ->where($db->quoteName('order_user_id') . ' = ' . (int) $userId)
        ->where($db->quoteName('course_id') . ' = ' . (int) $courseId)
        ->where($db->quoteName('published') . ' = 1');
    
    $db->setQuery($queryEnrollment);
    $isEnrolled = (int) $db->loadResult();
    
    if ($isEnrolled > 0) {
        echo '<p style="color: #28a745; font-weight: bold;">✅ Usuário está matriculado!</p>';
        echo '<p>Encontradas <strong>' . $isEnrolled . '</strong> matrícula(s) ativa(s).</p>';
    } else {
        echo '<p style="color: #dc3545; font-weight: bold;">❌ Usuário NÃO está matriculado!</p>';
        echo '<p>Nenhuma matrícula ativa encontrada na tabela <code>#__splms_orders</code>.</p>';
        echo '<p style="margin-top: 10px;">Execute <a href="create_test_data.php">create_test_data.php</a> para criar uma matrícula de teste.</p>';
    }
    echo '</div>';
    
    // Se estiver matriculado, busca os avisos
    if ($isEnrolled > 0) {
        echo '<div class="section">';
        echo '<h2>📢 Buscando Avisos do Curso...</h2>';
        
        // Query exatamente como está no Model
        $queryAnnouncements = $db->getQuery(true)
            ->select([
                $db->quoteName('a.id'),
                $db->quoteName('a.title'),
                $db->quoteName('a.message', 'description'),
                $db->quoteName('a.created_at', 'created_on'),
                $db->quoteName('u.name', 'author_name')
            ])
            ->from($db->quoteName('#__splms_course_announcements', 'a'))
            ->leftJoin(
                $db->quoteName('#__users', 'u') . ' ON ' . 
                $db->quoteName('u.id') . ' = ' . $db->quoteName('a.created_by')
            )
            ->where($db->quoteName('a.course_id') . ' = ' . (int) $courseId)
            ->order($db->quoteName('a.created_at') . ' DESC');
        
        $db->setQuery($queryAnnouncements);
        $items = $db->loadObjectList();
        
        echo '<p>Query SQL executada:</p>';
        echo '<pre>' . htmlspecialchars((string) $queryAnnouncements) . '</pre>';
        
        echo '</div>';
        
        if (empty($items)) {
            echo '<div class="section warning">';
            echo '<h2>⚠️ Nenhum Aviso Encontrado</h2>';
            echo '<p>A consulta foi executada com sucesso, mas não há avisos cadastrados para este curso.</p>';
            echo '<p style="margin-top: 10px;">Para criar avisos, acesse o painel administrativo do Joomla.</p>';
            echo '</div>';
        } else {
            echo '<div class="section success">';
            echo '<h2>✅ SUCESSO! <span class="badge badge-success">' . count($items) . ' aviso(s) encontrado(s)</span></h2>';
            echo '</div>';
            
            echo '<div class="section">';
            echo '<h2>📢 Avisos do Curso</h2>';
            
            foreach ($items as $item) {
                echo '<div class="announcement-card">';
                echo '<h3>' . htmlspecialchars($item->title) . '</h3>';
                echo '<div class="announcement-meta">';
                echo '👤 <strong>Autor:</strong> ' . htmlspecialchars($item->author_name) . ' | ';
                echo '📅 <strong>Data:</strong> ' . htmlspecialchars($item->created_on);
                echo '</div>';
                echo '<div class="announcement-body">';
                echo nl2br(htmlspecialchars($item->description));
                echo '</div>';
                echo '</div>';
            }
            
            echo '</div>';
        }
    }
    
    // Informações técnicas
    echo '<div class="section">';
    echo '<h2>ℹ️ Informações Técnicas</h2>';
    echo '<div class="info-row">';
    echo '<span class="info-label">Método:</span>';
    echo '<span class="info-value">Consulta SQL Direta (sem Model)</span>';
    echo '</div>';
    echo '<div class="info-row">';
    echo '<span class="info-label">Tabela de Avisos:</span>';
    echo '<span class="info-value">#__splms_course_announcements</span>';
    echo '</div>';
    echo '<div class="info-row">';
    echo '<span class="info-label">Tabela de Matrícula:</span>';
    echo '<span class="info-value">#__splms_orders</span>';
    echo '</div>';
    echo '<div class="info-row">';
    echo '<span class="info-label">Filtro de Publicação:</span>';
    echo '<span class="info-value">Removido (coluna não existe)</span>';
    echo '</div>';
    echo '</div>';
    
} catch (Exception $e) {
    echo '<div class="section error">';
    echo '<h2>❌ Erro na Execução</h2>';
    echo '<p><strong>Mensagem:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '<p><strong>Arquivo:</strong> ' . htmlspecialchars($e->getFile()) . '</p>';
    echo '<p><strong>Linha:</strong> ' . $e->getLine() . '</p>';
    echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
    echo '</div>';
}
?>
        </div>
    </div>
</body>
</html>
