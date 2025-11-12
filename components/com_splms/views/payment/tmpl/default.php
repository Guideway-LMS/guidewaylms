<?php

/**
 * @package com_splms
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2024 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
 */

// No Direct Access
defined ('_JEXEC') or die('Resticted Aceess');

use Joomla\CMS\Uri\Uri;

$notify_url 	= Uri::base() . 'index.php?option=com_splms&view=payment&task=notify';
$return_success = Uri::base() . 'index.php?option=com_splms&view=payment&task=success';
$return_cencel  = Uri::base() . 'index.php?option=com_splms&view=payment&task=cancel';
?>

