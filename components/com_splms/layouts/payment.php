<?php

use Joomla\CMS\Language\Text;

/**
 * @package com_splms
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2024 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
 */

// No Direct Access
defined('_JEXEC') or die('Resticted Aceess');

$contents                           = $displayData['contents'];
list($type, $message) = $contents;

?>

<div class="alert alert-<?php echo $type; ?> alert-dismissible fade show">
    <?php // This requires JS so we should add it trough JS. Progressive enhancement and stuff. 
    ?>
    <h2 class="alert-heading"><?php echo ucfirst(Text::_($type)); ?></h2>
    <div>
        <div>
            <p><?php echo $message; ?></p>
            <p>
                <?php echo Text::_('COM_SPLMS_PAYMENT_REDIRECT') ?>
                <span id="countdown-number"></span>
            </p>
        </div>
    </div>
</div>