<?php

/**
 * @package com_splms
 * @subpackage  mod_splmseventcalendar
 *
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2024 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
 */

defined ('_JEXEC') or die('Resticted Aceess');

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Helper\ModuleHelper;

HTMLHelper::_('jquery.framework');

$doc = Factory::getDocument();
$doc->addStylesheet( Uri::base(true) . '/components/com_splms/assets/css/font-splms.css' );
$doc->addStylesheet( Uri::base(true) . '/modules/mod_splmseventcalendar/assets/css/eventcalendar.css' );
$doc->addScript( Uri::base(true) . '/modules/mod_splmseventcalendar/assets/js/jquery.eventcalendar.js' );

$moduleclass_sfx = ($params->get('moduleclass_sfx')) ? htmlspecialchars($params->get('moduleclass_sfx')) : '';

// Include the helper.
require_once __DIR__ . '/helper.php';
$events = modSplmsEventCalendarHelper::getEvents($params);
require ModuleHelper::getLayoutPath('mod_splmseventcalendar', $params->get('layout', 'default'));