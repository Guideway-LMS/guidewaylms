<?php
define('_JEXEC', 1);
if (file_exists(__DIR__ . '/defines.php')) {
    include_once __DIR__ . '/defines.php';
}
if (!defined('_JDEFINES')) {
    define('JPATH_BASE', __DIR__);
    require_once JPATH_BASE . '/includes/defines.php';
}
require_once JPATH_BASE . '/includes/framework.php';

$app = JFactory::getApplication('site');
$cache = JFactory::getCache('');
$cache->clean();
echo "Cache cleaned!";
