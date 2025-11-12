<?php
/**
 * @package com_splms
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2024 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
 */

// No Direct Access
defined ('_JEXEC') or die('Resticted Aceess');

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Access\Exception\NotAllowed;

if (!Factory::getUser()->authorise('core.manage', 'com_splms')){
	throw new NotAllowed(Text::_('JERROR_ALERTNOAUTHOR'), 403);
}


HTMLHelper::_('jquery.framework');
$doc = Factory::getDocument();
$doc->addStylesheet( Uri::root(true) . '/administrator/components/com_splms/assets/css/splms.css' );
$doc->addScript( Uri::root(true) . '/administrator/components/com_splms/assets/js/main.js' );



// Require helper file
JLoader::register('SplmsHelper', JPATH_COMPONENT . '/helpers/splms.php');
$controller = BaseController::getInstance('Splms');
$controller->execute(Factory::getApplication()->input->get('task'));
$controller->redirect();
