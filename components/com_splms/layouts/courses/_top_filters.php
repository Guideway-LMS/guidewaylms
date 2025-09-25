<?php
/**
 * @package     SP SPLMS
 *
 * @copyright   Copyright (C) 2010 - 2021 JoomShaper. All rights reserved.
 * @license     GNU General Public License version 2 or later.
 */

defined('_JEXEC') or die('Restricted Access');

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\Utilities\ArrayHelper;

$categories  = (isset( $displayData['categories']) && $displayData['categories'] ) ? $displayData['categories'] : array();
$levels      = (isset( $displayData['levels']) && $displayData['levels'] ) ? $displayData['levels'] : array();
$types       = (isset( $displayData['types']) && $displayData['types'] ) ? $displayData['types'] : array();
$hide_search = (isset( $displayData['hide_search']) && $displayData['hide_search'] ) ? $displayData['hide_search'] : false;

$input          = Factory::getApplication()->input;
$view           = $input->get('view', '', 'STRING');
$item_id        = $input->get('Itemid', '', 'INT');

// Filter by category
$cate_filters  = $input->get('category', NULL, 'RAW');
if (is_array($cate_filters)) {
    $cate_filters = ArrayHelper::toInteger($cate_filters);
} else {
    $cate_filters = explode(',', $cate_filters);
    $cate_filters = ArrayHelper::toInteger($cate_filters);
}

// Filter by levels
$level_filters  = $input->get('level', NULL, 'STRING');
$level_filters = explode(',', $level_filters);

// Filter by course type
$course_type    = $input->get('course_type', '', 'STRING');

// Terms
$terms    = htmlspecialchars($input->get('terms', '', 'STRING'));
?>

<div class="splms-courses-filters-top">

    <form action="<?php echo Route::_('index.php?option=com_splms&view=courses', false); ?>" id="splms-courses-filters-form" class="splms-courses-filters">
        <?php if(!$hide_search) { ?>
            <div class="splms-course-filter-terms">
                <input type="text" name="terms" value="<?php echo $terms; ?>" placeholder="<?php echo Text::_('COM_SPLMS_COURSE_SEARCH'); ?>" />
            </div>
        <?php } ?>

        <?php if(count((array)$categories) ) { ?>
            <div class="splms-course-filter-categories">
                <div class="filter-toggler"><?php echo Text::_('COM_SPLMS_FILTERS_CATEGORY'); ?> <i class="fa fa-angle-down"></i></div>
                <ul id="splms-filter-categories" class="splms-filter-items">
                    <?php foreach ($categories as $key => $category) { ?>
                        <li>
                            <label class="custom-checkbox">
                                <input type="checkbox" value="<?php echo $category->id; ?>" name="category[]" <?php echo ( in_array($category->id, $cate_filters) ) ? 'checked="checked"': ''; ?> >
                                <span class="checkmark">
                                    <?php echo $category->title; ?>
                                    <span class="category-course-count">(<?php echo $category->count; ?>)</span>
                                </span>
                            </label>
                        </li>
                    <?php } ?>
                </ul>
            </div> <!-- //.splms-category-search -->
        <?php } ?>
        <?php if(count((array)$levels) ) { ?>
            <div class="splms-course-filter-levels">
                <div class="filter-toggler"><?php echo Text::_('COM_SPLMS_FILTERS_LEVEL'); ?> <i class="fa fa-angle-down"></i></div>
                <ul id="splms-filter-levels" class="splms-filter-items">
                    <?php foreach ($levels as $key => $level) { ?>
                        <li>
                            <label class="custom-checkbox">
                                <input type="checkbox" name="level[]" value="<?php echo strtolower($level); ?>" <?php echo ( in_array(strtolower($level), $level_filters) ) ? 'checked="checked"': ''; ?>  >
                                <span class="checkmark"><?php echo $level; ?></span>
                            </label>
                        </li>
                    <?php } ?>
                </ul>
            </div> <!-- //.splms-category-search -->
        <?php } ?>

        <?php if(count((array)$types) ) { ?>
            <div class="splms-course-filter-types">
                <div class="filter-toggler"><?php echo Text::_('COM_SPLMS_FILTERS_TYPE'); ?>  <i class="fa fa-angle-down"></i></div>
                <ul id="splms-filter-types" class="splms-filter-items">
                    <?php foreach ($types as $key => $type) { ?>
                        <li>
                            <label class="custom-checkbox">
                                <input type="radio" name="course_type" value="<?php echo strtolower($type); ?>" <?php echo ( $course_type == strtolower($type) ) ? 'checked="checked"': ''; ?>  >
                                <span class="checkmark"><?php echo $type; ?></span>
                            </label>
                        </li>
                    <?php } ?>
                </ul>
            </div> <!-- //.splms-category-search -->
        <?php } ?>

        <div class="splms-buttons-group">
            <button type="submit" class="splms-btn btn btn-primary splms-submit-button"><?php echo Text::_('COM_SPLMS_BTN_SUBMIT'); ?></button>
            <button type="reset" class="splms-btn btn btn-link splms-action-reset"><?php echo Text::_('COM_SPLMS_BTN_RESET'); ?></button>
        </div>
    </form>
</div> <!-- //.splms-courses-filters-top -->