<?php

/**
 * @package     SP LMS
 *
 * @copyright   Copyright (C) 2010 - 2021 JoomShaper. All rights reserved.
 * @license     GNU General Public License version 2 or later.
 */

defined('_JEXEC') or die('Restricted Access');

use Joomla\CMS\Language\Text;

$contents = $displayData['contents'];
list($lesson, $price, $isAuthorised, $active) = $contents;
?>

<?php $active_lesson = ($active == $lesson->id) ? ' active' : '';?>
<?php if ($lesson->lesson_type == 0 || $isAuthorised != '' || $price == 0): ?>
<li class="lesson<?php echo $active_lesson; ?>">
    <?php if (!empty($lesson->video_url)): ?>
    <span>
        <a href="<?php echo $lesson->lesson_url; ?>">
            <?php echo $lesson->title; ?>
        </a>
    </span>
    <span class="pull-right">
        <?php echo Text::_('COM_SPLMS_COMMON_DURATION') . Text::_(': '); ?>
        <?php echo $lesson->video_duration; ?>
    </span>
    <?php else: ?>
    <a href="<?php echo $lesson->lesson_url; ?>">
        <i class="splms-icon-book"></i>
        <?php echo $lesson->title; ?>
    </a>
    <?php endif;?>
</li>
<?php else: ?>
<li class="lesson splms-lesson-unauthorised">
    <span>
        <i class="splms-icon-book"></i>
        <i class="splms-icon-lock"></i>
        <?php echo $lesson->title; ?>
    </span>
    <span class="pull-right">
        <?php echo Text::_('COM_SPLMS_COMMON_DURATION') . ': '; ?>
        <?php echo $lesson->video_duration; ?>
    </span>
</li>
<?php endif; // end else ?>