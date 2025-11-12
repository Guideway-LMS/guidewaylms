<?php

/**
 * @package com_splms
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2024 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
 */

// No Direct Access
defined('_JEXEC') or die('Resticted Aceess');

use Joomla\CMS\Factory;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Ramsey\Uuid\Converter\Time\PhpTimeConverter;

$input = Factory::getApplication()->input;
$Itemid = $input->get('Itemid', 0, 'INT');
$monthindex = $input->get('monthindex', NULL, 'INT');
$event_type = $input->get('etype', 'all', 'STRING');

$etype_include = '';
if ($event_type) {
	$etype_include = '&etype=' . $event_type;
}

if ($monthindex) {
	$month_url = 'index.php?option=com_splms&view=events&monthindex=' . $monthindex . $etype_include . '&Itemid=' . $Itemid;
} else {
	$month_url = 'index.php?option=com_splms&view=events&Itemid=' . $Itemid;
}

$month_include = '';
if ($monthindex) {
	$month_include = '&monthindex=' . $monthindex;
}

//event type url
$all_event_url 		= 'index.php?option=com_splms&view=events&etype=all&Itemid=' . $Itemid;
$latest_event_url 	= 'index.php?option=com_splms&view=events' . $month_include . '&etype=latest&Itemid=' . $Itemid;
$upcoming_event_url = 'index.php?option=com_splms&view=events' . $month_include . '&etype=upcoming&Itemid=' . $Itemid;

?>

<div id="splms" class="splms splms-view-events">
	<?php if ($this->events_filter) { ?>
		<div class="splms-events-filters">
			<div class="splms-event-sorting-etype">
				<ul class="list-inline list-style-none">
					<li class="<?php echo ($event_type == 'all') ? 'active' : ''; ?>">
						<a href="<?php echo Route::_($all_event_url); ?>"><?php echo Text::_('COM_SPLMS_EVENTS_ALL'); ?></a>
					</li>
					<li class="<?php echo ($event_type == NULL || $event_type == 'upcoming') ? 'active' : ''; ?>">
						<a href="<?php echo Route::_($upcoming_event_url); ?>"><?php echo Text::_('COM_SPLMS_EVENTS_UPCOMING'); ?></a>
					</li>
					<li class="<?php echo ($event_type == 'latest') ? 'active' : ''; ?>">
						<a href="<?php echo Route::_($latest_event_url); ?>"><?php echo Text::_('COM_SPLMS_EVENTS_LATEST'); ?></a>
					</li>
				</ul>
			</div>

			<div class="splms-event-sorting-month">
				<ul class="list-inline list-style-none">
					<?php foreach ($this->months as $month) { ?>
						<li class="<?php echo ($monthindex == $month) ? 'active' : ''; ?>">
							<a href="<?php echo Route::_($month_url . '&monthindex=' . $month); ?>">
								<?php echo date("F", mktime(0, 0, 0, $month, 10)); ?>
							</a>
						</li>
					<?php } ?>
				</ul>
			</div> <!-- /.splms-event-sorting-month -->
		</div> <!-- /.plms-events-filters -->
	<?php } ?>

	<?php if (count($this->items)) { ?>
		<?php foreach ($this->items as $event) { ?>
			<div class="splms-event">
				<div class="splms-row">
					<div class="splms-col-sm-4">
						<a href="<?php echo $event->url; ?>">
							<img src="<?php echo $event->thumb; ?>" class="splms-event-img splms-img-responsive" alt="<?php echo $event->title; ?>">
						</a>
					</div>
					<div class="splms-col-sm-8">
						<div class="splms-event-details">
							<h3 class="splms-event-title">
								<a href="<?php echo $event->url; ?>">
									<?php echo $event->title; ?>
								</a>
							</h3>
							<ul class="splms-event-info-list">
								<?php if ($event->event_start_date) { ?>
									<li>
										<strong><?php echo Text::_('COM_SPLMS_EVENT_DATE_STARTS') . ': '; ?></strong> <?php echo HTMLHelper::date($event->event_start_date, 'DATE_FORMAT_LC1'); ?>
										<?php if ($event->event_time) { 											
											$event->event_time = strtotime($event->event_time);
											echo date('h:i A', $event->event_time);?>
										<?php } ?>
									</li>
								<?php } ?>

								<?php if ($event->event_end_date) { ?>													
									<li>
										<strong><?php echo Text::_('COM_SPLMS_EVENT_DATE_ENDS') . ': '; ?></strong> <?php echo HTMLHelper::date($event->event_end_date, 'DATE_FORMAT_LC1'); ?>
										<?php if ($event->event_end_time) {
											$event->event_end_time = strtotime($event->event_end_time);
											echo date('h:i A', $event->event_end_time); ?>
										<?php } ?>
									</li>
								<?php } ?>
								<li>
									<strong><?php echo Text::_('COM_SPLMS_EVENT_LOCATION') . ': '; ?></strong>
									<?php echo $event->event_address; ?>
								</li>
							</ul>

							<div class="splms-event-short-description">
								<p><?php echo HTMLHelper::_('string.truncate', strip_tags($event->description), $this->params->get('intro_limit', 300)); ?></p>
							</div>

							<?php if ($this->params->get('show_readmore')) { ?>
								<a class="btn btn-primary" href="<?php echo $event->url; ?>"><?php echo $this->params->get('readmore_text', Text::_('COM_SPLMS_DETAILS')); ?></a>
							<?php } ?>
						</div>
					</div>
				</div>
				<!--/row-->
			</div>

		<?php } ?>

		<?php if ($this->params->get('hide_pagination') == 0) { ?>
			<?php if ($this->pagination->pagesTotal > 1) { ?>
				<div class="pagination">
					<?php echo $this->pagination->getPagesLinks(); ?>
				</div>
			<?php } ?>
		<?php } ?>

	<?php } else { ?>
		<div class="splms-event no-item">
			<div class="alert alert-danger" role="alert"><?php echo Text::_('COM_SPLMS_EVENTS_NO_ITEM_FOUND'); ?></div>
		</div>
	<?php } ?>
</div>