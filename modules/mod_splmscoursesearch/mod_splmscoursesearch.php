<?php
/**
 * @package com_splms
 * @subpackage  mod_splmscoursesearch
 *
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2024 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
 */

// No Direct Access
defined ('_JEXEC') or die('Resticted Aceess');
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Helper\ModuleHelper;

// Load the method jquery script.
HTMLHelper::_('jquery.framework');

$doc = Factory::getDocument();
$doc->addStylesheet( Uri::base(true) . '/components/com_splms/assets/css/font-splms.css' );
$doc->addStylesheet( Uri::base(true) . '/modules/mod_splmscoursesearch/assets/css/course-search.css' );
$doc->addScript( Uri::base(true) . '/modules/mod_splmscoursesearch/assets/js/course-search.js' );

$moduleclass_sfx = ($params->get('moduleclass_sfx')) ? htmlspecialchars($params->get('moduleclass_sfx')) : '';

// Include the helper.
require_once __DIR__ . '/helper.php';

require ModuleHelper::getLayoutPath('mod_splmscoursesearch', $params->get('layout', 'default'));