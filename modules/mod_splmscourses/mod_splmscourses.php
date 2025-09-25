<?php
/**
 * @package com_splms
 * @subpackage  mod_splmscourses
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
$doc->addStylesheet( Uri::root(true) . '/modules/mod_splmscourses/assets/css/mod-splms-courses.css' );
$doc->addStylesheet(Uri::root(true) . '/components/com_splms/assets/css/splms.css');

$items 			 = ModSplmscoursesHelper::getCourses($params);
$moduleclass_sfx = ($params->get('moduleclass_sfx')) ? htmlspecialchars($params->get('moduleclass_sfx')) : '';
$columns 		 = htmlspecialchars($params->get('columns', 3));
$show_discount	 = $params->get('show_discount');

if(!isset($show_discount) ) {
	$show_discount = $params->get('show_discount', true);
} else {
	$show_discount = $params->get('show_discount', false);
}

//Legacy compatibility
foreach ($items as $item) {
	$item->splms_coursescategory_id = $item->coursecategory_id;
	$item->splms_course_id 			= $item->id;
	$item->price					= SplmsHelper::formatPrice($item->price);
	$item->sale_price				= SplmsHelper::formatPrice($item->sale_price);
}

require ModuleHelper::getLayoutPath('mod_splmscourses', $params->get('layout', 'default'));