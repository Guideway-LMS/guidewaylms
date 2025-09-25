<?php

/**
 * @package com_splms
 * @subpackage  mod_splmseventcategories
 *
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2024 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
 */

// No Direct Access
defined ('_JEXEC') or die('Resticted Aceess');

use Joomla\CMS\Helper\ModuleHelper;

require_once JPATH_BASE . '/components/com_splms/models/events.php';
require_once __DIR__ . '/helper.php';

$items = ModSplmseventcategegoriesHelper::getEventcategories($params);
$moduleclass_sfx = ($params->get('moduleclass_sfx')) ? htmlspecialchars($params->get('moduleclass_sfx')) : '';

require ModuleHelper::getLayoutPath('mod_splmseventcategories', $params->get('layout', 'default'));