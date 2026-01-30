<?php
define('_JEXEC', 1);
define('JPATH_BASE', __DIR__);

require_once JPATH_BASE . '/includes/defines.php';
require_once JPATH_BASE . '/includes/framework.php';

// Bootstrap
$container = \Joomla\CMS\Factory::getContainer();
$container->alias('session.web', 'session.web.site')
    ->alias('session', 'session.web.site')
    ->alias('JSession', 'session.web.site')
    ->alias(\Joomla\CMS\Session\Session::class, 'session.web.site')
    ->alias(\Joomla\Session\Session::class, 'session.web.site')
    ->alias(\Joomla\Session\SessionInterface::class, 'session.web.site');

// Mock application input
$app = $container->get(\Joomla\CMS\Application\SiteApplication::class);
// In newer Joomla, we don't need setApplication via Factory, the container handles the instance.
// But we might need to spoof the container "application" key if Factory::getApplication is called statically without arguments.
// However, Factory::getApplication() usually gets 'japp' or similar from container.
// Let's try ignoring setApplication and just ensuring the app is initialized.

require_once JPATH_SITE . '/components/com_splms/helpers/GuidewayAIHelper.php';

// Set override key to bypass Joomla params loading which fails in this CLI script
\GuidewayAIHelper::setOverrideKey('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');

echo "\n--- STARTING SMOKE TEST ---\n";
try {
    $result = \GuidewayAIHelper::smokeTest();
    print_r($result);
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Trace:\n" . $e->getTraceAsString();
}
echo "\n--- END SMOKE TEST ---\n";
