<?php

/**
 * @package com_splms
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2024 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
 */

// No Direct Access
defined('_JEXEC') or die('Resticted Aceess');

use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;

$show_filter = $this->params->get('show_course_filter', 1);
$filter_position = $this->params->get('course_filter_position', 'left');
$hide_search = $this->params->get('hide_course_filter_search', false);
$columns = $this->params->get('columns', 2);
?>

<div id="splms" class="splms view-splms-courses">

	<?php if ($show_filter && ($filter_position == 'left' || $filter_position == 'right')) { ?>
		<div class="splms-row">
			<?php echo LayoutHelper::render('courses.sidebar_filters', array('categories' => $this->course_categories, 'levels' => $this->course_lavels, 'types' => $this->filter_course_types, 'position' => $filter_position, 'hide_search' => $hide_search)); ?>
			<div class="splms-col-md-9<?php echo $filter_position == 'right' ? ' splms-col-md-pull-3' : ''; ?>">
			<?php } elseif ($show_filter && $filter_position == 'top') { ?>
				<?php echo LayoutHelper::render('courses.top_filters', array('categories' => $this->course_categories, 'levels' => $this->course_lavels, 'types' => $this->filter_course_types, 'hide_search' => $hide_search)); ?>
			<?php } ?>

			<?php if (count($this->items)) { ?>
				<div class="splms-courses-list">
					<div class="splms-row splms-shuffle">
						<?php foreach ($this->items as $course) { ?>
							<div class="<?php echo ($columns > 3) ? 'splms-col-sm-6 splms-col-md-' . round(12 / $columns) : 'splms-col-md-' . round(12 / $columns); ?>" data-groups='["all","<?php echo $course->coursecategory_alias[0]; ?>"]'>
								<?php echo LayoutHelper::render('courses.course', array('course' => $course)); ?>
							</div>
						<?php } ?>
					</div>
				</div>
				<div class="pagination">
					<?php echo $this->pagination->getPagesLinks(); ?>
				</div>

			<?php } else { ?>
				<div class="splms-message no-item">
					<div class="alert alert-warning" role="alert"><?php echo Text::_('COM_SPLMS_EVENTS_NO_ITEM_FOUND'); ?></div>
				</div>
			<?php } ?>

			<?php if ($show_filter && ($filter_position == 'left' || $filter_position == 'right')) { ?>
			</div>
		</div>
	<?php } ?>

</div>