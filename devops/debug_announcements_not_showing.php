<?php
require_once __DIR__ . '/auth.php';

/**
 * Debug: Verifica por que os avisos não aparecem
 */

// define('_JEXEC', 1);
// define('JPATH_BASE', dirname(__DIR__));

require_once JPATH_BASE . '/includes/defines.php';
require_once JPATH_BASE . '/includes/framework.php';

use Joomla\CMS\Factory;

$db = Factory::getDbo();

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Debug Avisos</title>";
echo "<style>body{font-family:sans-serif;padding:20px;background:#f5f5f5;}";
echo ".section{background:white;padding:20px;margin:20px 0;border-radius:8px;box-shadow:0 2px 4px rgba(0,0,0,0.1);}";
echo "h2{color:#667eea;border-bottom:2px solid #667eea;padding-bottom:10px;}";
echo "table{width:100%;border-collapse:collapse;margin-top:10px;}";
echo "th,td{padding:10px;text-align:left;border-bottom:1px solid #ddd;}";
echo "th{background:#667eea;color:white;}";
echo "pre{background:#2d2d2d;color:#f8f8f2;padding:15px;border-radius:6px;overflow-x:auto;}";
echo ".success{color:#28a745;font-weight:bold;}";
echo ".error{color:#dc3545;font-weight:bold;}";
echo "</style></head><body>";

echo "<h1>🔍 Debug: Por que os avisos não aparecem?</h1>";

// 1. Verifica avisos no banco
echo "<div class='section'>";
echo "<h2>1. Avisos na Tabela</h2>";
$query = $db->getQuery(true)
    ->select('*')
    ->from($db->quoteName('#__splms_course_announcements'))
    ->order('id DESC');
$db->setQuery($query);
$allAnnouncements = $db->loadObjectList();

if (empty($allAnnouncements)) {
    echo "<p class='error'>❌ Nenhum aviso encontrado na tabela!</p>";
} else {
    echo "<p class='success'>✅ Encontrados " . count($allAnnouncements) . " aviso(s) na tabela</p>";
    echo "<table><tr><th>ID</th><th>Título</th><th>Course ID</th><th>Created By</th><th>Created At</th></tr>";
    foreach ($allAnnouncements as $row) {
        echo "<tr>";
        echo "<td>" . $row->id . "</td>";
        echo "<td>" . htmlspecialchars($row->title) . "</td>";
        echo "<td>" . $row->course_id . "</td>";
        echo "<td>" . $row->created_by . "</td>";
        echo "<td>" . $row->created_at . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}
echo "</div>";

// 2. Verifica matrículas
echo "<div class='section'>";
echo "<h2>2. Matrículas (Orders)</h2>";
$query = $db->getQuery(true)
    ->select('*')
    ->from($db->quoteName('#__splms_orders'))
    ->where($db->quoteName('published') . ' = 1')
    ->order('id DESC');
$db->setQuery($query);
$orders = $db->loadObjectList();

if (empty($orders)) {
    echo "<p class='error'>❌ Nenhuma matrícula encontrada!</p>";
    echo "<p>Execute <a href='create_test_data.php'>create_test_data.php</a> para criar uma matrícula.</p>";
} else {
    echo "<p class='success'>✅ Encontradas " . count($orders) . " matrícula(s)</p>";
    echo "<table><tr><th>ID</th><th>User ID</th><th>Course ID</th><th>Published</th><th>Created At</th></tr>";
    foreach ($orders as $row) {
        echo "<tr>";
        echo "<td>" . $row->id . "</td>";
        echo "<td>" . $row->order_user_id . "</td>";
        echo "<td>" . $row->course_id . "</td>";
        echo "<td>" . $row->published . "</td>";
        echo "<td>" . ($row->created_at ?? 'N/A') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}
echo "</div>";

// 3. Simula a query do Model
if (!empty($allAnnouncements) && !empty($orders)) {
    echo "<div class='section'>";
    echo "<h2>3. Simulação da Query do Model</h2>";
    
    $testUserId = $orders[0]->order_user_id;
    $testCourseId = $allAnnouncements[0]->course_id;
    
    echo "<p><strong>Testando com:</strong> User ID = $testUserId, Course ID = $testCourseId</p>";
    
    $modelQuery = $db->getQuery(true)
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
        ->where($db->quoteName('a.course_id') . ' = ' . (int) $testCourseId);
    
    // Subquery de matrícula
    $subQuery = $db->getQuery(true)
        ->select('1')
        ->from($db->quoteName('#__splms_orders', 'o'))
        ->where($db->quoteName('o.course_id') . ' = ' . (int) $testCourseId)
        ->where($db->quoteName('o.order_user_id') . ' = ' . (int) $testUserId)
        ->where($db->quoteName('o.published') . ' = 1');
    
    $modelQuery->where('EXISTS (' . $subQuery . ')');
    $modelQuery->order($db->quoteName('a.created_at') . ' DESC');
    
    echo "<p><strong>SQL Gerado:</strong></p>";
    echo "<pre>" . htmlspecialchars((string) $modelQuery) . "</pre>";
    
    $db->setQuery($modelQuery);
    try {
        $modelResults = $db->loadObjectList();
        
        if (empty($modelResults)) {
            echo "<p class='error'>❌ Query retornou VAZIO!</p>";
            echo "<p><strong>Possíveis causas:</strong></p>";
            echo "<ul>";
            echo "<li>User ID $testUserId não está matriculado no Course ID $testCourseId</li>";
            echo "<li>Avisos criados para um curso diferente</li>";
            echo "<li>Subquery de matrícula não encontrou correspondência</li>";
            echo "</ul>";
        } else {
            echo "<p class='success'>✅ Query retornou " . count($modelResults) . " aviso(s)!</p>";
            echo "<table><tr><th>ID</th><th>Título</th><th>Autor</th><th>Data</th></tr>";
            foreach ($modelResults as $row) {
                echo "<tr>";
                echo "<td>" . $row->id . "</td>";
                echo "<td>" . htmlspecialchars($row->title) . "</td>";
                echo "<td>" . htmlspecialchars($row->author_name) . "</td>";
                echo "<td>" . $row->created_on . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
    } catch (Exception $e) {
        echo "<p class='error'>❌ Erro ao executar query:</p>";
        echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
    }
    echo "</div>";
}

// 4. Recomendações
echo "<div class='section'>";
echo "<h2>4. Recomendações</h2>";
echo "<ul>";
echo "<li>Verifique se o <strong>ID do curso na URL</strong> corresponde ao course_id dos avisos</li>";
echo "<li>Verifique se o <strong>usuário logado</strong> está matriculado no curso</li>";
echo "<li>Use o mesmo User ID e Course ID que aparecem nas tabelas acima</li>";
echo "</ul>";
echo "</div>";

echo "</body></html>";
?>
