<?php
/**
 * @package     SP SPLMS
 *
 * @copyright   Copyright (C) 2010 - 2021 JoomShaper. All rights reserved.
 * @license     GNU General Public License version 2 or later.
 */

defined('_JEXEC') or die('Restricted Access');


use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

$app = Factory::getApplication('com_splms');
$params = $app->getParams();

$course = $displayData['course'];

$show_discount = $params->get('show_discount', 1);
$discount_percentage = '';
if( $show_discount && ($course->price > $course->sale_price) && $course->sale_price && ($course->sale_price != '0.00' && $course->price != '0.00') ) {
    $discount_percentage = (($course->price - $course->sale_price)*100) /$course->price;
}
?>

<div class="splms-course splms-match-height">
    <div class="splms-common-overlay-wrapper">
        <img src="<?php echo $course->thumbnail; ?>" class="splms-course-img splms-img-responsive" alt="<?php echo $course->title; ?>">
        <?php if ($course->price == 0) {
            echo '<span class="splms-badge-free">' . Text::_('COM_SPLMS_FREE') . '</span>';
        } ?>
        <?php if( $show_discount && $course->sale_price && isset($discount_percentage) && $discount_percentage) { ?>
            <span class="splms-course-discount-price"><?php echo round($discount_percentage) . Text::_('COM_SPLMS_COURSES_PERCENT_OFF'); ?></span>
        <?php }?>
        <div class="splms-common-overlay">
            <div class="splms-vertical-middle">
                <div>
                    <a href="<?php echo $course->url; ?>" class="splms-readmore btn btn-default">
                        <?php echo $params->get('readmore_text', Text::_('COM_SPLMS_DETAILS')); ?>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="splms-course-info">
        <h3 class="splms-courses-title">
            <a href="<?php echo $course->url; ?>">
                <?php echo $course->title; ?>
                <small><?php echo $course->category_name; ?></small>
            </a>
        </h3>
        <div class="splms-course-time"><?php echo $course->course_time; ?></div>

        <?php if (!empty($course->teachers)) { ?>
            <div class="splms-course-teacher">
                <?php if(count($course->teachers) == 1) { ?>
                    <i class="splms-icon-teacher"></i>
                    <?php foreach ($course->teachers as $teacher) { ?>
                        <a href="<?php echo $teacher->url; ?>">
                            <?php echo $teacher->title;?>
                        </a>
                    <?php } ?>
                <?php } elseif (count($course->teachers)  > 1 ) { ?>
                    <i class="splms-icon-users"></i>
                    <a href="javascipt:void(0);" id="splms-multiteacher-toogle" multiple-teachers-toggler><?php echo Text::_('COM_SPLMS_COMMON_MULTIPLE_TEACHERS');?></a>
                    <ul class="splms-course-multi-teachers">
                        <?php foreach ($course->teachers as $teacher) { ?>
                            <li><a href="<?php echo $teacher->url; ?>"><?php echo $teacher->title; ?></a></li>
                        <?php } ?>	
                    </ul>
                <?php } ?>
            </div>
        <?php } ?>

        <?php if(isset($course->short_description) && !empty($course->short_description)) { ?>
            <p class="splms-course-short-info"><?php echo $course->short_description; ?></p>
        <?php } ?>

        <div class="splms-course-meta">
            <ul>
                <li><?php echo SplmsHelper::getPrice($course->price, $course->sale_price); ?></li>
                <li><?php echo $course->lessonsCount; ?> <?php echo Text::_('COM_SPLMS_COMMON_LESSONS'); ?></li>
            </ul>
        </div>
    </div>
</div>