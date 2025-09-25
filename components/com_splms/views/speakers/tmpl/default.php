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

// Get Columns
$columns = $this->params->get('columns', '4');

?>

<div id="splms" class="splms view-splms-speakers splms-speakers-list splms-persons">
	<div class="splms-row">
		<?php if(count($this->items)) { ?>

		<!-- Column -->
		<?php foreach ($this->items as $speaker) { ?>
		<div class="splms-person splms-col-md-<?php echo round(12/$columns); ?> splms-col-sm-6">
			<div class="splms-person-details">

				<img src="<?php echo $speaker->image; ?>" class="splms-person-img splms-img-responsive" alt="<?php echo $speaker->title; ?>">
				<div class="splms-person-info">
					<a class="splms-person-title" href="<?php echo $speaker->url; ?>">
						<?php echo $speaker->title; ?>
					</a>
					<small class="splms-person-designation"><?php echo $speaker->designation; ?></small>

					<?php if ( (!empty($speaker->social_facebook)) ||
						(!empty($speaker->social_linkedin)) ||
						(!empty($speaker->social_twitter)) ||
						(!empty($speaker->social_instagram)) ||
						(!empty($speaker->social_youtube))) { ?>

						<ul class="splms-persion-social-icons">
							<?php if (!empty($speaker->social_facebook) && $speaker->social_facebook != '#') { ?>			                		
							<li class="facebook">
								<a href="http://facebook.com/<?php echo $speaker->social_facebook; ?>" target="_blank"> 
									<i class="splms-icon-facebook"></i>
								</a>
							</li>
							<?php } if (!empty($speaker->social_linkedin) && $speaker->social_linkedin != '#') {?>
							<li class="linkedin">
								<a href="http://linkedin.com/<?php echo $speaker->social_linkedin; ?>" target="_blank"> 
									<i class="splms-icon-linkedin"></i>
								</a>
							</li>
							<?php } if (!empty($speaker->social_twitter) && $speaker->social_twitter != '#') {?>
							<li class="twitter">
								<a href="http://twitter.com/<?php echo $speaker->social_twitter; ?>" target="_blank"> 
									<i class="splms-icon-twitter"></i>
								</a>
							</li>
							<?php } if (!empty($speaker->social_instagram) && $speaker->social_instagram != '#') { ?>			                		
							<li class="instagram">
								<a href="https://www.instagram.com/<?php echo $speaker->social_instagram; ?>" target="_blank"> 
									<i class="splms-icon-instagram"></i>
								</a>
							</li>
							<?php } if (!empty($speaker->social_youtube) && $speaker->social_youtube != '#') {?>
							<li class="youtube">
								<a href="https://youtube.com/<?php echo $speaker->social_youtube; ?>" target="_blank"> 
									<i class="splms-icon-youtube"></i>
								</a>
							</li>
							<?php } ?>
						</ul>
					<?php } ?>	
				</div>

				<div class="splms-person-content">
					<div>
						<div class="vertical-top">
							<?php if (!empty($speaker->website) && $speaker->website != '#') { ?>
							<p>
								<?php echo Text::_('COM_SPLMS_COMMON_WEBSITE') . ': '; ?>
								<a href="<?php echo $speaker->website; ?>" target="_blank">
									<?php echo $speaker->website; ?>
								</a>
							</p>
							<?php } ?>
							<?php if (!empty($speaker->email) && $speaker->email != '#') { ?>
							<p>
								<?php echo Text::_('COM_SPLMS_COMMON_EMAIL') . ': '; ?>
								<a href="mailto:<?php echo $speaker->email; ?>" target="_blank">
									<?php echo $speaker->email; ?>
								</a>
							</p>
							<?php } ?>

							<?php if (!empty($speaker->speaker_events) && $speaker->speaker_events != '') { ?>
							<p>
								<?php echo Text::_('COM_SPLMS_COMMON_TOTAL') . ': '; ?>  
								<?php echo $speaker->speaker_events; ?>
								<?php echo Text::_('COM_SPLMS_COMMON_TOTAL_SESSION'); ?>  
							</p>
							<?php } ?>
						</div>
					</div>
				</div>
			</div><!--/.item-content-->
		</div>

		<?php }  ?>
		<?php } ?>
	</div>
</div>

<!-- BEGIN:: Pagination -->
<?php if ($this->params->get('hide_pagination') == 0) { ?>
<?php if ($this->pagination->pagesTotal > 1) { ?>
<div class="pagination">
	<?php echo $this->pagination->getPagesLinks(); ?>
</div>
<?php } ?>
<?php } ?>


<!-- END:: Pagination -->