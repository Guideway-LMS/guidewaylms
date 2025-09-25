<?php

/**
 * @package com_splms
 * @subpackage  mod_splmupcomingevents
 *
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2024 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
 */

// No Direct Access
defined ('_JEXEC') or die('Resticted Aceess');

use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Component\ComponentHelper;

// Get Thumb
$params = ComponentHelper::getParams('com_splms');
$thumb_size_small = strtolower($params->get('event_thumbnail_small', '100X60'));
$speaker_loaded = false;
?>

<div class="mod-splms-upcoming-events <?php echo $moduleclass_sfx; ?>">
	<?php if(count($items)){ ?>
		<ul class="splms-upcoming-events-lists">
			<?php foreach ($items as $item) {?>
			<li class="splms-upcoming-event clearfix">
				<?php if($show_speakers && count($item->speakers) && !empty($item->speakers) ) { ?>
					<div class="splms-upcoming-event-info">
				<?php } ?>
					<?php if (!empty($item->image)) : ?>
					<?php
						$filename = basename($item->image);
						$path = JPATH_BASE .'/'. dirname($item->image) . '/thumbs/' . File::stripExt($filename) . '_' . $thumb_size_small . '.' . File::getExt($filename);
						$src = Uri::base(true) . '/' . dirname($item->image) . '/thumbs/' . File::stripExt($filename) . '_' . $thumb_size_small . '.' . File::getExt($filename);

						if(File::exists($path)) {
							$thumb = $src;
						} else {
							$thumb = $this->item->image;
						}
					?>
					<a href="<?php echo $item->url; ?>">
						<img src="<?php echo $thumb; ?>" class="splms-event-img splms-img-responsive" alt="<?php echo $item->title; ?>">
					</a>
					<?php endif;?>
					<small class="splms-upcoming-event-date">
						<?php echo HTMLHelper::_('date', $item->event_start_date, 'DATE_FORMAT_LC4'); ?>
					</small>

					<strong class="splms-upcoming-event-title">
						<a href="<?php echo $item->url; ?>" class="splms-event-title"><?php echo $item->title; ?></a>
					</strong>
				<?php if($show_speakers && count($item->speakers) && !empty($item->speakers) ) { ?>
					</div>
				<?php } ?>
				<?php if($show_speakers && count($item->speakers) && !empty($item->speakers) ) { ?>
					<?php if($speaker_loaded != true) { ?>
						<div class="splms-upcoming-event-speakers-wrap">
							<?php foreach ($item->speakers as $speaker) { ?>
								<div class="splms-upcoming-event-speaker">
									<div class="splms-ue-speaker-media">
										<a href="<?php echo $speaker->url; ?>">
											<img src="<?php echo Uri::root() . $speaker->image; ?>" alt="<?php echo $speaker->title; ?>">
										</a>
									</div> <!-- /.splms-ue-speaker-media -->
									<div class="splms-ue-speaker-info">
										<p class="splms-ue-speaker-name">
											<a href="<?php echo $speaker->url; ?>">
												<?php echo $speaker->title; ?>
											</a>
											<small><?php echo $speaker->designation; ?></small>
										</p>
									</div> <!-- /.splms-ue-speaker-info -->
								</div> <!-- /.splms-upcoming-event-speaker -->
							<?php } ?>
						</div> <!-- /.splms-upcoming-event-speakers-wrap -->
					<?php } ?>
					<?php if($show_speakers == 'first') { $speaker_loaded = true; } ?>
				<?php } ?>
			</li>
			<?php } ?>
		</ul>
	<?php } else { ?>
		<p><?php echo Text::_('MOD_SPLMS_NO_ITEMS_FOUND');  ?></p>
	<?php } ?>
</div>
