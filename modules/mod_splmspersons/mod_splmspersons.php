<?php

/**
 * @package com_splms
 * @subpackage  mod_splmspersons
 *
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2024 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
 */

// No Direct Access
defined ('_JEXEC') or die('Resticted Aceess');

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Helper\ModuleHelper;

require_once JPATH_BASE . '/components/com_splms/helpers/helper.php';
require_once __DIR__ . '/helper.php';

$doc = Factory::getDocument();
$doc->addStylesheet( Uri::root(true) . '/components/com_splms/assets/css/splms.css' );
$doc->addStylesheet( Uri::root(true) . '/modules/mod_splmspersons/assets/css/style.css' );

$items 		= ModSplmspersonsHelper::getTeachers($params);
$moduleclass_sfx = ($params->get('moduleclass_sfx')) ? htmlspecialchars($params->get('moduleclass_sfx')) : '';
$columns = htmlspecialchars($params->get('columns', 4));

require ModuleHelper::getLayoutPath('mod_splmspersons', $params->get('layout', 'default'));
