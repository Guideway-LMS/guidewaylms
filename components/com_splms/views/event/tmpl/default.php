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
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Component\ComponentHelper;


$params = ComponentHelper::getParams('com_splms');
$mapbox_api = $params->get('mapbox_api', '');

// Load assets
$doc = Factory::getDocument();
$doc->addStylesheet(Uri::root(true) . '/components/com_splms/assets/css/lightboxgallery-min.css');
$doc->addScript(Uri::root(true) . '/components/com_splms/assets/js/lightboxgallery-min.js');

$disable_gmap = $params->get('disable_gmap', 0);
$select_map = $params->get('select_map', 1);

// Load Leaflet

if (!$disable_gmap && $select_map == 1) {
	$doc->addStyleSheet('//unpkg.com/leaflet@1.7.1/dist/leaflet.css', [], ["integrity" => "sha512-xodZBNTC5n17Xt2atTPuE1HxjVMSvLVW9ocqUKLsCC5CXdbqCmblAshOMAS6/keqq/sMZMZ19scR4PsZChSR7A==", "crossorigin" => ""]);
	$doc->addScript('//unpkg.com/leaflet@1.7.1/dist/leaflet.js', [], ["integrity" => "sha512-XQoYMqMTK8LvdxXYG3nZ448hOEQiglfqkJs1NOQV44cWnUrBc8PkAOcXy20w0vlaXaVUearIOBhiXZ5V3ynxwA==", "crossorigin" => ""]);
	$doc->addScriptOptions('lat', trim($this->map[0]));
	$doc->addScriptOptions('long', trim($this->map[1]));
	$doc->addScriptOptions('mapbox_api', trim($mapbox_api));
	$doc->addScriptOptions('address', $this->item->event_address);
	$doc->addScript(Uri::root(true) . '/components/com_splms/assets/js/omap.js', [], ['defer' => 'defer']);
}
?>

<div id="splms" class="splms splms-view-event">
	<div class="splms-event-image">
		<img src="<?php echo $this->item->image ?>" class="splms-event-img splms-img-responsive" alt="<?php echo $this->item->title; ?>">
	</div>

	<h2 class="splms-event-title">
		<?php echo $this->item->title; ?>
	</h2>

	<ul class="splms-event-detail-list">
		<?php if ($this->item->event_start_date) { ?>
			<li>
				<strong><?php echo Text::_('COM_SPLMS_EVENT_DATE_STARTS') . ': '; ?></strong> <?php echo HTMLHelper::date($this->item->event_start_date, 'DATE_FORMAT_LC1'); ?>
				<?php if ($this->item->event_time) { 
					$this->item->event_time = strtotime($this->item->event_time);
					echo date('h:i A', $this->item->event_time);
				} ?>
			</li>
		<?php } ?>

		<?php if ($this->item->event_end_date) { ?>
			<li>
				<strong><?php echo Text::_('COM_SPLMS_EVENT_DATE_ENDS') . ': '; ?></strong> <?php echo HTMLHelper::date($this->item->event_end_date, 'DATE_FORMAT_LC1'); ?>
				<?php if ($this->item->event_end_time) {
					$this->item->event_end_time = strtotime($this->item->event_end_time);
					echo date('h:i A',$this->item->event_end_time,);
				 } ?>
			</li>
		<?php } ?>

		<?php if ($this->item->event_address) { ?>
			<li>
				<strong><?php echo Text::_('COM_SPLMS_EVENT_LOCATION') . ': '; ?></strong> <?php echo $this->item->event_address; ?>
			</li>
		<?php } // has address 
		?>

		<?php if ($this->item->price) { ?>
			<li>
				<strong><?php echo Text::_('COM_SPLMS_EVENT_PRICE') . ': '; ?></strong>
				<?php echo $this->item->price; ?>
			</li>
		<?php } ?>
	</ul>

	<!-- event gallery -->
	<?php if (isset($this->item->gallery) && is_array($this->item->gallery) && count($this->item->gallery)) { ?>
		<div class="splms-event-gallery">
			<h3 class="splms-event-title"><?php echo Text::_('COM_SPLMS_EVENT_PHOTO_GALLERY'); ?></h3>
			<div class="splms-event-gallery-list">
				<?php foreach ($this->item->gallery as $key => $gallery_item) { ?>
					<a class="lightboxgallery-gallery-item" href="<?php echo Uri::root() . $gallery_item['image']; ?>">
						<img src="<?php echo Uri::root() . $gallery_item['image']; ?>" alt="image">
						<h4 class="title"><?php echo $gallery_item['text']; ?></h4>
					</a>
				<?php } ?>
			</div> <!-- //.gallery-list -->
		</div> <!-- //.splms-event-gallery -->
	<?php } ?>

	<!-- event topics -->
	<?php if (isset($this->topics) && is_array($this->topics) && count($this->topics)) { ?>
		<div class="splms-event-topics">
			<h3 class="splms-event-title"><?php echo Text::_('COM_SPLMS_EVENT_TOPICS'); ?></h3>
			<ul class="splms-event-topics-list">
				<?php foreach ($this->topics as $key => $topics) { ?>
					<li>
						<div class="table-responsive">
							<table class="table">
								<thead>
									<tr>
										<td><?php echo $key; ?></td>
									</tr>
								</thead>
								<tbody>
									<?php foreach ($topics as $topic) { ?>
										<tr>
											<?php if ($topic['title']) { ?>
												<td><?php echo $topic['title']; ?></td>
											<?php }
											if ($topic['time']) { ?>
												<td><i class="fa fa-clock-o"></i><?php echo $topic['time']; ?></td>
											<?php }
											if ($topic['speaker_infos']) { ?>
												<td><span class="with"><?php echo Text::_('COM_SPLMS_COMMON_WITH'); ?></span> : <a href="<?php echo $topic['speaker_infos']->url; ?>"><?php echo $topic['speaker_infos']->title; ?></a></td>
											<?php } ?>
										</tr> <!-- //each row -->
									<?php } ?>
								</tbody>
							</table>
						</div>
					<?php } ?>
			</ul> <!-- //.splms-event-topics-list -->
		</div> <!-- //.splms-event-topics -->
	<?php } ?>

	<!-- Event Speakers -->
	<?php if (isset($this->item->speaker_infos) && $this->item->speaker_infos && count($this->item->speaker_infos)) { ?>
		<div class="splms-event-speakers">
			<h3 class="splms-event-title"><?php echo Text::_('COM_SPLMS_EVENT_TRAINERS'); ?></h3>
			<div class="row">
				<?php
				$last_item = end($this->item->speaker_infos);
				foreach ($this->item->speaker_infos as $speaker_info) {
				?>
					<div class="splms-event-speaker splms-col-sm-6 splms-col-md-3">
						<a href="<?php echo $speaker_info->url ?>">
							<img src="<?php echo $speaker_info->image; ?>" alt="<?php echo $speaker_info->title; ?>">
						</a>
						<h4 class="title">
							<a href="<?php echo $speaker_info->url ?>"><?php echo $speaker_info->title; ?></a>
						</h4>
						<p class="designation"><?php echo $speaker_info->designation; ?></p>
					</div>
				<?php } ?>
			</div> <!-- //.row -->
		</div>
	<?php } // has speaker 
	?>

	<!-- event tickets -->
	<?php if (isset($this->item->pricing_tables) && is_array($this->item->pricing_tables) && count($this->item->pricing_tables)) { ?>
		<div id="splms-event-tickets" class="splms-event-tickets">
			<h3 class="splms-event-title"><?php echo Text::_('COM_SPLMS_EVENT_TICKETS'); ?></h3>
			<div class="splms-row splms-event-tickets-list">
				<?php foreach ($this->item->pricing_tables as $pricing_table) {
					$pricing_table['price'] = SplmsHelper::formatPrice($pricing_table['price']) ?: $pricing_table['price']; ?>
					<div class="splms-col-sm-4">
						<div class="event-pricing-table splms-text-center">
							<div class="splms-pricing-box">
								<div class="splms-pricing-header">
									<span class="splms-pricing-price"><?php echo $pricing_table['price'] ?></span>
									<div class="splms-pricing-title">
										<?php echo $pricing_table['title'] ?>
									</div>
								</div>
								<?php if ($pricing_table['description']) { ?>
									<div class="splms-pricing-features">
										<ul>
											<?php
											$features = explode("\n", $pricing_table['description']);
											foreach ($features as $feature) { ?>
												<li><?php echo $feature; ?></li>
											<?php } ?>
										</ul>
									</div>
								<?php } ?>
								<div class="splms-pricing-footer">
									<?php if ($pricing_table['purchase_url']) { ?>
										<a class="splms-btn splms-btn-info splms-btn-rounded" href="#">Proceed <i class="fa fa-chevron-right"></i></a>
									<?php } ?>
								</div>
							</div>
						</div> <!-- //.event-pricing-table -->
					</div> <!-- //.splms-col-sm-4 -->
				<?php } ?>
			</div> <!-- //.splms-row splms-event-tickets-list -->
		</div> <!-- //.splms-event-tickets -->
	<?php } ?>

	<h3 class="splms-event-location-title"><?php echo Text::_('COM_SPLMS_EVENT_DESCRIPTION'); ?></h3>
	<div class="splms-event-description">
		<?php echo $this->item->description; ?>
	</div>
	<?php if (!$this->params->get('disable_gmap', 0)) { ?>
		<?php if ($this->params->get('select_map', 0) == 0 && $this->item->event_address) { ?>
			<div class="splms-event-location-map">
				<h3 class="splms-event-location-title"><?php echo Text::_('COM_SPLMS_EVENT_LOCATION'); ?></h3>
				<div id="splms-event-map" class="splms-gmap-canvas" data-lat="<?php echo $this->map[0]; ?>" data-lng="<?php echo $this->map[1]; ?>" data-address="<?php echo $this->item->event_address; ?>" style="height:300px"></div>
			</div>
		<?php } ?>

		<?php if ($this->params->get('select_map', 1) == 1 && $this->item->event_address) { ?>
			<div class="splms-event-location-map">
				<h3 class="splms-event-location-title"><?php echo Text::_('COM_SPLMS_EVENT_LOCATION'); ?></h3>
				<div id="open-map" class="splms-gmap-canvas" style="height:300px"></div>
			</div>

		<?php } ?>
	<?php } ?>

	<div class="splms-event-detail-footer">
		<div class="splms-row">
			<div class="splms-event-purchase-btn splms-col-sm-4">
				<?php if ($this->item->buy_url) { ?>
					<a class="btn btn-primary" href="<?php echo $this->item->buy_url; ?>"><?php echo Text::_('COM_SPLMS_EVENT_BUY_TICKET'); ?></a>
				<?php } ?>
			</div>

			<?php if ($this->params->get('event_social_share', 1)) { ?>
				<div class="splms-event-shares splms-col-sm-8 text-right">
					<?php echo LayoutHelper::render('social_share', array('url' => $this->item->url, 'title' => $this->item->title)); ?>
				</div>
			<?php } ?>
		</div>
	</div>
</div> <!-- /#splms -->