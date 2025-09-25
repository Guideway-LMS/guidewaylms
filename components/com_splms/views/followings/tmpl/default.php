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
use Joomla\CMS\Layout\LayoutHelper;

$columns = $this->params->get('columns', '3');
?>
<div id="splms" class="splms view-splms-techers splms-techers-list splms-persons">
	<?php if(count($this->items)) { ?>
		<div class="splms-row">
		<?php foreach ($this->items as $this->item) { ?>
			<div class="splms-person splms-col-md-<?php echo round(12/$columns); ?> splms-col-sm-6">
				<div class="splms-person-details">
					<img src="<?php echo $this->item->image; ?>" class="splms-person-img splms-img-responsive" alt="<?php echo $this->item->title; ?>">

					<div class="splms-person-info">
						<a class="splms-person-title" href="<?php echo $this->item->url; ?>">
							<?php echo $this->item->title; ?>
						</a>

						<?php if($this->item->designation){ ?>
							<p><?php echo $this->item->designation; ?></p>
						<?php } ?>

						<?php echo LayoutHelper::render('teacher.social_icons', array('teacher'=> $this->item)); ?>
					</div>

					<div class="splms-person-content">
						<div>
							<div class="vertical-top">
								<?php if (!empty($this->item->website)) { ?>
								<p>
									<?php echo Text::_('COM_SPLMS_COMMON_WEBSITE') . ': '; ?>
									<a href="<?php echo $this->item->website; ?>" target="_blank">
										<?php echo $this->item->website; ?>
									</a>
								</p>
								<?php } ?>

								<?php if (!empty($this->item->experience)) { ?>
								<p>
									<?php echo Text::_('COM_SPLMS_COMMON_EXPERIENCE') . ': '; ?>
									<?php echo $this->item->experience; ?>
								</p>
								<?php } ?>

								<?php if (!empty($this->item->skills)) { ?>
								<p>
									<?php echo Text::_('COM_SPLMS_COMMON_SKILLS') . ': '; ?>
									<?php echo $this->item->skills; ?>
								</p>
								<?php } ?>

								<p>
									<?php echo Text::_('COM_SPLMS_COURSES') . ': '; ?>
									<?php echo $this->item->courses; ?>
								</p>
							</div>
						</div>
					</div>
					
				</div>
			</div>
			<?php } ?>
		</div>
	<?php } else { ?>
		<div class="splms-message no-item">
			<div class="alert alert-warning" role="alert"><?php echo Text::_('COM_SPLMS_EVENTS_NO_ITEM_FOUND'); ?></div>
		</div>
	<?php } ?>
</div>

<?php echo $this->pagination->getPagesLinks(); ?>

