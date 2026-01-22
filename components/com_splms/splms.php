<?php
/**
* @package com_splms
* @author JoomShaper http://www.joomshaper.com
* @copyright Copyright (c) 2010 - 2024 JoomShaper
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\MVC\Controller\BaseController;

require_once JPATH_COMPONENT . '/helpers/helper.php';
HTMLHelper::_('jquery.framework');
$doc = Factory::getDocument();
$app = Factory::getApplication();
$params = $app->getParams('com_splms');

// Include JS
$doc->addScriptdeclaration('var splms_url="' . Uri::base() . '";');
$doc->addScript(Uri::root(true) . '/components/com_splms/assets/js/shuffle-5.1.1.js');
$doc->addScript( Uri::root(true) . '/components/com_splms/assets/js/splms.js' );

// Include CSS files
$doc->addStylesheet( Uri::root(true) . '/components/com_splms/assets/css/font-splms.css' );
$doc->addStylesheet( Uri::root(true) . '/components/com_splms/assets/css/splms.css' );
if(!$params->get('disable_styling', 0)) {
    $doc->addStylesheet( Uri::root(true) . '/components/com_splms/assets/css/styles.css' );
}

$languageFilePath = JPATH_ROOT . '/components/com_splms/helpers/language.php';

if (file_exists($languageFilePath))
{
    require_once $languageFilePath;
}

$controller = BaseController::getInstance('Splms');
$input = Factory::getApplication()->input;
$controller->execute($input->getCmd('task'));
$controller->redirect();
