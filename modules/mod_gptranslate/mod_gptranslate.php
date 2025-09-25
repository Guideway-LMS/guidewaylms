<?php
/**
* @version   $Id$
* @package   GTranslate
* @copyright Copyright (C) 2008-2023 GTranslate Inc. All rights reserved.
* @license   GNU/GPL v3 http://www.gnu.org/licenses/gpl.html
*/

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Component\ComponentHelper;
use JExtStore\Module\Gptranslate\Site\Helper\GptranslateHelper;

// Retrieve params from main component configuration
$params = ComponentHelper::getParams('com_gptranslate');

// Require module templates, both for standard Joomla login form and facebook button addon
require ModuleHelper::getLayoutPath ( 'mod_gptranslate' );