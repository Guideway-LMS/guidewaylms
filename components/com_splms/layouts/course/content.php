<?php

/**
 * @package     SP Movie Databse
 *
 * @copyright   Copyright (C) 2010 - 2021 JoomShaper. All rights reserved.
 * @license     GNU General Public License version 2 or later.
 */

defined('_JEXEC') or die('Restricted Access');

use Joomla\CMS\Language\Text;

$contents     = $displayData['contents'];
list($content, $price, $isAuthorised, $active) = $contents;
?>

<?php if ( $content->lesson_type == 0 || $price == 0 || $isAuthorised != '' ) { ?>
    <li class="<?php echo (!empty($active) && $active == $content->id) ? 'active' : ''; ?>">
        <?php if (!empty($content->video_url)) :?>
            <span>
                <a href="<?php echo $content->lesson_url; ?>">
                    <?php echo '['.Text::_('COM_SPLMS_LESSON') .'] ' . $content->title; ?>
                </a>
            </span>
            <?php if (!empty($content->video_duration)) :?>
            <span class="pull-right">
                <?php echo Text::_('COM_SPLMS_COMMON_DURATION') . ': '; ?>
                <small><?php echo $content->video_duration; ?></small>
            </span>
            <?php endif;?>

        <?php else :?>
            <a href="<?php echo $content->lesson_url; ?>">
                <i class="splms-icon-book"></i>
                <?php echo '['.Text::_('COM_SPLMS_LESSON') .'] ' . $content->title; ?>
            </a>
            
             <?php 
            // GUIDEWAY CUSTOM: Badges
            $badgeText = '';
            $badgeClass = '';
            
            if (isset($content->is_optional) && $content->is_optional == 1) {
                $badgeText = 'Opcional';
                $badgeClass = 'badge badge-secondary'; // Cinza/Neutro
            } elseif (isset($content->lesson_format) && ($content->lesson_format == 'quiz' || $content->lesson_format == 'assignment')) {
                 $badgeText = 'Obrigatório';
                 $badgeClass = 'badge badge-warning'; // Amarelo/Laranja
            }
            
            if ($badgeText) {
                echo '<span class="' . $badgeClass . ' pull-right" style="margin-left: 10px; font-size: 0.8em;">' . $badgeText . '</span>';
            }
            ?>
        <?php endif;?>
    </li>
<?php } else { ?>
    <li>
        <div style="display: inline;">
            <?php if (!empty($content->video_url)) :?>
            <i class="splms-icon-play"></i>
            <?php else :?>
            <i class="splms-icon-book"></i>
            <?php endif;?>
            <i class="splms-icon-lock"></i>
            <?php echo '['.Text::_('COM_SPLMS_LESSON') .'] ' . $content->title; ?>
        </div>
        <?php if (!empty($content->video_duration)) :?>
        <span class="pull-right">
            <?php echo Text::_('COM_SPLMS_COMMON_DURATION') . ': '; ?>
            <small><?php echo $content->video_duration; ?></small>
        </span>
        <?php endif;?>
    </li>
<?php } ?>

