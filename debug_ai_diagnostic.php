<?php
define('_JEXEC', 1);
define('JPATH_BASE', __DIR__);

require_once JPATH_BASE . '/includes/defines.php';
require_once JPATH_BASE . '/includes/framework.php';

// Bootstrap the application
$container = \Joomla\CMS\Factory::getContainer();
$container->alias('session.web', 'session.web.site')
    ->alias('session', 'session.web.site')
    ->alias('JSession', 'session.web.site')
    ->alias(\Joomla\CMS\Session\Session::class, 'session.web.site')
    ->alias(\Joomla\Session\Session::class, 'session.web.site')
    ->alias(\Joomla\Session\SessionInterface::class, 'session.web.site');

$app = $container->get(\Joomla\CMS\Application\SiteApplication::class);
\Joomla\CMS\Factory::setApplication($app);

echo "\n--- SPLMS Params Diagnostic ---\n";
$params = \Joomla\CMS\Component\ComponentHelper::getParams('com_splms');
$key = $params->get('groq_api_key');
echo "Params 'groq_api_key': [" . ($key ? $key : "EMPTY") . "]\n";

echo "\n--- Environment Diagnostic ---\n";
echo "getenv('GROQ_API_KEY'): [" . (getenv('GROQ_API_KEY') ?: "EMPTY") . "]\n";

echo "\n--- GuidewayAIHelper Diagnostic ---\n";
$helperPath = JPATH_SITE . '/components/com_splms/helpers/GuidewayAIHelper.php';
if (file_exists($helperPath)) {
    require_once $helperPath;
    if (class_exists('GuidewayAIHelper')) {
        if (method_exists('GuidewayAIHelper', 'getGroqApiKey')) {
            $helperKey = \GuidewayAIHelper::getGroqApiKey();
            echo "GuidewayAIHelper::getGroqApiKey(): [" . ($helperKey ? $helperKey : "EMPTY/NULL") . "]\n";
        } else {
            echo "GuidewayAIHelper::getGroqApiKey does not exist.\n";
        }
    } else {
        echo "GuidewayAIHelper class not found.\n";
    }
} else {
    echo "GuidewayAIHelper file not found at $helperPath\n";
}

echo "\n--- All Params Dump ---\n";
print_r($params->toArray());
