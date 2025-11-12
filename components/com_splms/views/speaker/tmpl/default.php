<?php
/**
* @package com_splms
* @author JoomShaper http://www.joomshaper.com
* @copyright Copyright (c) 2010 - 2024 JoomShaper
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/

// No Direct Access
defined ('_JEXEC') or die('Resticted Aceess');

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

?>

<div id="splms" class="splms view-splms-speaker splms-person">

	<div class="splms-row">
		<div class="splms-col-sm-4">
			<img src="<?php echo $this->item->image ?>" class="splms-person-image splms-img-responsive" alt="<?php echo $this->item->title; ?>">
		</div>

		<div class="splms-col-sm-8">

			<div class="splms-person-details">

				<h3 class="splms-person-title">
					<?php echo $this->item->title; ?>
					<small class="splms-person-designation"><?php echo $this->item->designation; ?></small>
				</h3>

				<?php if (!empty($this->item->website) && $this->item->website) { ?>
				<p class="splms-person-website">
					<?php echo Text::_('COM_SPLMS_COMMON_WEBSITE') . ': '; ?>
					<a href="<?php echo $this->item->website; ?>" target="_blank"> <?php echo $this->item->website; ?> </a>
				</p>
				<?php } ?>

				<?php if (isset($this->item->email)) { ?>
				<p class="splms-person-email">
					<?php echo Text::_('COM_SPLMS_COMMON_EMAIL') . ': '; ?> 
					<a href="mailto: <?php echo $this->item->email; ?>"><?php echo $this->item->email; ?></a>
				</p>
				<?php } ?>

				<?php if ( (!empty($this->item->social_facebook)) ||
					(!empty($this->item->social_linkedin)) ||
					(!empty($this->item->social_twitter)) ||
					(!empty($this->item->social_instagram)) ||
					(!empty($this->item->social_youtube))) { ?>

					<ul class="splms-persion-social-icons">
						<?php if (!empty($this->item->social_facebook) && $this->item->social_facebook) { ?>			                		
						<li class="facebook">
							<a href="https://facebook.com/<?php echo $this->item->social_facebook; ?>" target="_blank"> 
								<i class="splms-icon-facebook"></i>
							</a>
						</li>
						<?php } if (!empty($this->item->social_linkedin) && $this->item->social_linkedin) {?>
						<li class="linkedin">
							<a href="https://linkedin.com/<?php echo $this->item->social_linkedin; ?>" target="_blank"> 
								<i class="splms-icon-linkedin"></i>
							</a>
						</li>
						<?php } if (!empty($this->item->social_twitter) && $this->item->social_twitter) {?>
						<li class="twitter">
							<a href="https://twitter.com/<?php echo $this->item->social_twitter; ?>" target="_blank"> 
								<i class="splms-icon-twitter"></i>
							</a>
						</li>
						<?php } if (!empty($this->item->social_instagram) && $this->item->social_instagram) { ?>			                		
						<li class="instagram">
							<a href="https://instagram.com/<?php echo $this->item->social_instagram; ?>" target="_blank"> 
								<i class="splms-icon-instagram"></i>
							</a>
						</li>
						<?php } if (!empty($this->item->social_youtube) && $this->item->social_youtube) {?>
						<li class="youtube">
							<a href="https://youtube.com/<?php echo $this->item->social_youtube; ?>" target="_blank"> 
								<i class="splms-icon-youtube"></i>
							</a>
						</li>
						<?php } ?>
					</ul>
				<?php } ?>

				<div class="splms-person-description">
					<?php echo $this->item->description; ?>
				</div>

				<?php if(isset($this->speaker_events) && count($this->speaker_events)) { ?>
				<div class="splms-speakers-event">
					<h3><?php echo $this->item->title . '\'s '. Text::_('COM_SPLMS_COMMON_EVENTS_LIST'); ?></h3>
					<div class="splms-speaker-events">
						<ul>
							<?php foreach ($this->speaker_events as $speaker_event) { ?>
							<li>
								<span>
									<a href="<?php echo $speaker_event->url; ?>">
										<?php echo $speaker_event->title; ?>
									</a>
								</span>
								<span class="pull-right">
									<?php echo HTMLHelper::_('date', $speaker_event->event_start_date , Text::_('d M Y')); ?>
								</span>
							</li>
							<?php } ?>
						</ul>
					</div>
				</div>
				<?php } ?>

			</div>
		</div>
	</div>		
</div>