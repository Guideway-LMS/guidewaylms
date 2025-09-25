<?php
/**
* @package com_splms
* @subpackage  mod_splmscourses
*
* @author JoomShaper http://www.joomshaper.com
* @copyright Copyright (c) 2010 - 2024 JoomShaper
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

// No Direct Access
defined ('_JEXEC') or die('Resticted Aceess');

HTMLHelper::_('jquery.framework');

$doc = Factory::getDocument();
$doc->addStylesheet( Uri::root(true) . '/modules/mod_splmscart/assets/css/style.css' );
$doc->addStylesheet( Uri::root(true) . '/administrator/components/com_splms/assets/css/font-awesome.min.css' );
$doc->addScript( Uri::base(true) . '/modules/mod_splmscart/assets/js/main.js' );

JLoader::register('SplmsHelper', JPATH_SITE . '/components/com_splms/helpers/helper.php');
// Load course model
jimport('joomla.application.component.model');
BaseDatabaseModel::addIncludePath(JPATH_SITE.'/components/com_splms/models');

$model = BaseDatabaseModel::getInstance( 'Cart', 'SplmsModel' );
$items = $model->getItems();

$moduleclass_sfx = ($params->get('moduleclass_sfx')) ? htmlspecialchars($params->get('moduleclass_sfx')) : '';
require ModuleHelper::getLayoutPath('mod_splmscart', $params->get('layout', 'default'));