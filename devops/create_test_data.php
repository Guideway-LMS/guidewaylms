<?php
/**
 * Script para Criar Dados de Teste
 * 
 * Este script cria uma matrícula (ordem) de teste para permitir
 * que o teste de avisos funcione corretamente.
 */

// Define constantes necessárias
define('_JEXEC', 1);
define('JPATH_BASE', dirname(__DIR__));

// Inclui o framework do Joomla
require_once JPATH_BASE . '/includes/defines.php';
require_once JPATH_BASE . '/includes/framework.php';

use Joomla\CMS\Factory;

// Carrega o DI Container
$container = Factory::getContainer();
$container->alias(\Joomla\Session\SessionInterface::class, 'session.web.site')
    ->alias(\Joomla\Session\Session::class, 'session.web.site')
    ->alias('session', 'session.web.site');

$app = $container->get(\Joomla\CMS\Application\SiteApplication::class);

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar Dados de Teste</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            max-width: 800px;
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
        .success {
            background: #d4edda;
            border-left: 4px solid #28a745;
            padding: 15px;
            border-radius: 4px;
            margin: 15px 0;
        }
        .error {
            background: #f8d7da;
            border-left: 4px solid #dc3545;
            padding: 15px;
            border-radius: 4px;
            margin: 15px 0;
        }
        .info {
            background: #d1ecf1;
            border-left: 4px solid #0c5460;
            padding: 15px;
            border-radius: 4px;
            margin: 15px 0;
        }
        pre {
            background: #2d2d2d;
            color: #f8f8f2;
            padding: 15px;
            border-radius: 6px;
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🛠️ Criar Dados de Teste</h1>
        
<?php
try {
    $db = Factory::getDbo();
    
    // Busca um usuário ativo
    $query = $db->getQuery(true)
        ->select('id, name, username')
        ->from($db->quoteName('#__users'))
        ->where($db->quoteName('block') . ' = 0')
        ->setLimit(1);
    
    $db->setQuery($query);
    $user = $db->loadObject();
    
    if (!$user) {
        throw new Exception('Nenhum usuário ativo encontrado!');
    }
    
    echo '<div class="info">';
    echo '<strong>👤 Usuário Selecionado:</strong><br>';
    echo 'Nome: ' . htmlspecialchars($user->name) . '<br>';
    echo 'Username: ' . htmlspecialchars($user->username) . '<br>';
    echo 'ID: ' . $user->id;
    echo '</div>';
    
    // Busca um curso publicado
    $query = $db->getQuery(true)
        ->select('id, title')
        ->from($db->quoteName('#__splms_courses'))
        ->where($db->quoteName('published') . ' = 1')
        ->setLimit(1);
    
    $db->setQuery($query);
    $course = $db->loadObject();
    
    if (!$course) {
        throw new Exception('Nenhum curso publicado encontrado!');
    }
    
    echo '<div class="info">';
    echo '<strong>📚 Curso Selecionado:</strong><br>';
    echo 'Título: ' . htmlspecialchars($course->title) . '<br>';
    echo 'ID: ' . $course->id;
    echo '</div>';
    
    // Verifica se já existe uma ordem para este usuário/curso
    $query = $db->getQuery(true)
        ->select('id')
        ->from($db->quoteName('#__splms_orders'))
        ->where($db->quoteName('order_user_id') . ' = ' . (int) $user->id)
        ->where($db->quoteName('course_id') . ' = ' . (int) $course->id);
    
    $db->setQuery($query);
    $existingOrder = $db->loadResult();
    
    if ($existingOrder) {
        echo '<div class="success">';
        echo '<strong>✅ Ordem já existe!</strong><br>';
        echo 'ID da Ordem: ' . $existingOrder . '<br>';
        echo 'O usuário já está "matriculado" neste curso.';
        echo '</div>';
    } else {
        // Cria uma nova ordem
        $orderData = new stdClass();
        $orderData->order_user_id = $user->id;
        $orderData->course_id = $course->id;
        $orderData->published = 1;
        $orderData->created_at = date('Y-m-d H:i:s');
        $orderData->price = 0.00;
        $orderData->status = 'completed';
        
        $db->insertObject('#__splms_orders', $orderData);
        $newOrderId = $db->insertid();
        
        echo '<div class="success">';
        echo '<strong>✅ Ordem Criada com Sucesso!</strong><br>';
        echo 'ID da Nova Ordem: ' . $newOrderId . '<br>';
        echo 'O usuário agora está "matriculado" no curso.';
        echo '</div>';
    }
    
    echo '<div class="info">';
    echo '<strong>📝 Próximos Passos:</strong><br>';
    echo '1. Execute o teste: <a href="test_announcements_check.php">test_announcements_check.php</a><br>';
    echo '2. Crie avisos no painel administrativo para o curso ID ' . $course->id;
    echo '</div>';
    
} catch (Exception $e) {
    echo '<div class="error">';
    echo '<strong>❌ Erro:</strong><br>';
    echo htmlspecialchars($e->getMessage());
    echo '</div>';
}
?>
    </div>
</body>
</html>
