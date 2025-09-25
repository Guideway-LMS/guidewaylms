<?php

/**
 * @package com_splms
 * @subpackage  mod_splmscoursescategegory
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
$doc->addStylesheet( Uri::base(true) . '/modules/mod_splmscoursescategegory/assets/css/mod-splms-course-categories.css' );

$items = ModSplmscoursescategegoryHelper::getCoursescategories($params);
$moduleclass_sfx = ($params->get('moduleclass_sfx')) ? htmlspecialchars($params->get('moduleclass_sfx')) : '';

require ModuleHelper::getLayoutPath('mod_splmscoursescategegory', $params->get('layout', 'default'));