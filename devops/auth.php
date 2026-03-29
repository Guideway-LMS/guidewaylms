<?php
/**
 * DevOps Security Check
 * Garante que apenas usuários administradores logados no Joomla
 * possam acessar as ferramentas desta pasta.
 */

define('_JEXEC', 1);

if (!defined('JPATH_BASE')) {
    define('JPATH_BASE', dirname(__DIR__));
}

require_once JPATH_BASE . '/includes/defines.php';
require_once JPATH_BASE . '/includes/framework.php';

// Inicializa o contêiner DI do Joomla 5
$container = \Joomla\CMS\Factory::getContainer();

$container->alias('session.web', 'session.web.administrator')
    ->alias('session', 'session.web.administrator')
    ->alias('JSession', 'session.web.administrator')
    ->alias(\Joomla\CMS\Session\Session::class, 'session.web.administrator')
    ->alias(\Joomla\Session\Session::class, 'session.web.administrator')
    ->alias(\Joomla\Session\SessionInterface::class, 'session.web.administrator');

// Instancia a aplicação de Administrador
$app = $container->get(\Joomla\CMS\Application\AdministratorApplication::class);

// Define a aplicação no Factory
\Joomla\CMS\Factory::$application = $app;

// Obtém o usuário atual
$user = \Joomla\CMS\Factory::getUser();

function devops_access_denied($title, $message) {
    http_response_code(403);
    ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title); ?> - DevOps Area</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-primary: #0f172a;
            --bg-card: #1e293b;
            --border: #334155;
            --text-primary: #f1f5f9;
            --text-secondary: #94a3b8;
            --accent-red: #f43f5e;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Inter', -apple-system, sans-serif; 
            background: linear-gradient(135deg, var(--bg-primary) 0%, #1e1b4b 100%); 
            color: var(--text-primary); 
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 40px;
            max-width: 480px;
            width: 100%;
            text-align: center;
            box-shadow: 0 20px 40px rgba(0,0,0,0.6);
            position: relative;
            overflow: hidden;
            animation: fadeIn 0.4s cubic-bezier(0.16, 1, 0.3, 1);
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; height: 4px;
            background: linear-gradient(90deg, var(--accent-red), #f97316);
        }
        .icon-wrap {
            margin-bottom: 24px;
            display: flex;
            justify-content: center;
        }
        .icon {
            font-size: 50px;
            background: rgba(244,63,94,0.1);
            width: 90px;
            height: 90px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            color: var(--accent-red);
            border: 1px solid rgba(244,63,94,0.2);
        }
        h1 { font-size: 24px; font-weight: 800; margin-bottom: 12px; letter-spacing: -0.5px; }
        p { color: var(--text-secondary); line-height: 1.6; margin-bottom: 30px; font-size: 15px; }
        .btn {
            background: linear-gradient(135deg, var(--accent-red), #e11d48);
            color: white;
            text-decoration: none;
            padding: 14px 30px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 15px;
            display: inline-block;
            transition: all 0.2s;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(225, 29, 72, 0.4);
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon-wrap">
            <div class="icon">🛡️</div>
        </div>
        <h1><?php echo htmlspecialchars($title); ?></h1>
        <p><?php echo htmlspecialchars($message); ?></p>
        <a href="/guidewaylms/administrator/" class="btn">Ir para o Painel Admin</a>
    </div>
</body>
</html>
    <?php
    exit;
}

if ($user->guest) {
    devops_access_denied('Acesso Negado', 'Você precisa estar logado no painel de administração do Joomla para acessar o Centro de Comando DevOps.');
}

if (!$user->authorise('core.admin')) {
    devops_access_denied('Privilégios Insuficientes', 'Sua conta não possui permissões de Administrador para acessar esta área restrita.');
}
