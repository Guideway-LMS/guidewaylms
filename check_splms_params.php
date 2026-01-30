<?php
define('_JEXEC', 1);
define('JPATH_BASE', __DIR__);

require_once JPATH_BASE . '/includes/defines.php';
require_once JPATH_BASE . '/includes/framework.php';

$container = \Joomla\CMS\Factory::getContainer();
$container->alias('session.web', 'session.web.site')
    ->alias('session', 'session.web.site')
    ->alias('JSession', 'session.web.site')
    ->alias(\Joomla\CMS\Session\Session::class, 'session.web.site')
    ->alias(\Joomla\Session\Session::class, 'session.web.site')
    ->alias(\Joomla\Session\SessionInterface::class, 'session.web.site');

$app = $container->get(\Joomla\CMS\Application\SiteApplication::class);
\Joomla\CMS\Factory::setApplication($app);

$params = \Joomla\CMS\Component\ComponentHelper::getParams('com_splms');

echo "<h1>SPLMS Params Check</h1>";
echo "<pre>";
print_r($params);
echo "</pre>";

echo "<h2>Raw DB Check</h2>";
$db = \Joomla\CMS\Factory::getDbo();
$query = $db->getQuery(true)
    ->select($db->quoteName('params'))
    ->from($db->quoteName('#__extensions'))
    ->where($db->quoteName('element') . ' = ' . $db->quote('com_splms'));
$db->setQuery($query);
$raw = $db->loadResult();
echo "<pre>";
print_r(json_decode($raw));
echo "</pre>";
