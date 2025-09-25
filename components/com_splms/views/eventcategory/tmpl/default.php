<?php
/**
 * @package com_splms
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2024 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
 */

// No Direct Access
defined ('_JEXEC') or die('Resticted Aceess');

use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

?>

<div class="event-category">
	<div id="splms" class="splms splms-view-events splms-event-category-items">
		<?php if(count($this->category_items)) { ?>
			<?php foreach ($this->category_items as $category_item) { ?>
				<div class="splms-event">
					<div class="splms-row">
						<div class="splms-col-sm-4">
							<a href="<?php echo $category_item->url; ?>">
								<img src="<?php echo $category_item->thumb; ?>" class="splms-event-img splms-img-responsive" alt="<?php echo $category_item->title; ?>">
							</a>
						</div>
						<div class="splms-col-sm-8">
							<div class="splms-event-details">
								<h3 class="splms-event-title">
									<a href="<?php echo $category_item->url; ?>">
										<?php echo $category_item->title; ?>
									</a>
								</h3>
								<ul class="splms-event-info-list">
									<?php if($category_item->event_start_date) { ?>
										<li>
											<strong><?php echo Text::_('COM_SPLMS_EVENT_DATE_STARTS'). ': '; ?></strong> <?php echo HTMLHelper::date($category_item->event_start_date, 'DATE_FORMAT_LC1'); ?>
											<?php if($category_item->event_time) { ?>
												<?php echo HTMLHelper::_('date', $category_item->event_time, 'h:i A'); ?>
											<?php } ?>
										</li>
									<?php } ?>

									<?php if($category_item->event_end_date) { ?>
										<li>
											<strong><?php echo Text::_('COM_SPLMS_EVENT_DATE_ENDS'). ': '; ?></strong> <?php echo HTMLHelper::date($category_item->event_end_date, 'DATE_FORMAT_LC1'); ?>
											<?php if(isset($category_item->event_end_time) && $category_item->event_end_time) { ?>
												<?php echo HTMLHelper::_('date', $category_item->event_end_time, 'h:i A'); ?>
											<?php } ?>
										</li>
									<?php } ?>

									<li>
										<strong>
											<?php echo Text::_('COM_SPLMS_EVENT_LOCATION') . ': '; ?>
										</strong>
										<?php echo $category_item->event_address; ?>
									</li>
								</ul>
								
								<div class="splms-event-short-description">
									<p>
										<?php echo HTMLHelper::_('string.truncate', strip_tags($category_item->description), $this->params->get('intro_limit', 300)); ?>
									</p>
								</div>

								<?php if ($this->params->get('show_readmore')) { ?>
									<a class="btn btn-primary" href="<?php echo $category_item->url; ?>"><?php echo $this->params->get('readmore_text', Text::_('COM_SPLMS_DETAILS')); ?>
									</a>
								<?php } ?>

							</div>
						</div>
					</div><!--/row-->
				</div>
			<?php }?>
		<?php } ?>
	</div>
</div>


