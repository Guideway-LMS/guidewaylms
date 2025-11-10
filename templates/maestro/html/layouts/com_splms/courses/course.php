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
    </div>

    <div class="splms-course-info">
        <div class="splms-courses-title-wrap">
            <h3 class="splms-courses-title">
                <a href="<?php echo $course->url; ?>">
                    <?php echo $course->title; ?>
                </a>
            </h3>
            <span><?php echo SplmsHelper::getPrice($course->price, $course->sale_price); ?></span>
        </div>
        <div class="splms-course-time"><?php echo $course->course_time; ?></div>

        <div class="splms-course-meta">
            <ul>
                <li><i class="fas fa-book"></i><?php echo $course->lessonsCount; ?> <?php echo Text::_('COM_SPLMS_COMMON_LESSONS'); ?></li>
                <li><i class="far fa-calendar-alt"></i><?php echo $course->duration; ?></li>
                <li><i class="fas fa-edit"></i><?php echo $course->level; ?></li>
                <li>
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
                </li>
            </ul>
        </div>
    </div>
</div>