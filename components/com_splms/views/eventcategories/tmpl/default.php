<?php

/**
 * @package com_splms
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2024 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
 */

// No Direct Access
defined('_JEXEC') or die('Resticted Aceess');

use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

$columns = $this->params->get('columns', '2');

?>

<div id="splms" class="splms splms-view-event-categories">

	<?php if(count($this->items)) { ?>
		<!-- Column -->
		<?php foreach(array_chunk($this->items, $columns) as $this->items) { ?>
			<div class="splms-row">
				<?php foreach ($this->items as $this->item) { ?>

					<?php 
					$this->item->url = Route::_('index.php?option=com_splms&view=eventcategory&id=' . $this->item->id . ':' . $this->item->alias . SplmsHelper::getItemid('eventcategory'));
					?>

					<div class="splms-col-lg-<?php echo round(12 / $columns); ?> splms-col-md-6 splms-col-sm-12">
						<div class="splms-event-category">
							<img class="splms-event-category-img splms-img-responsive" src="<?php echo $this->item->image; ?>" alt="<?php echo $this->item->title; ?>">

							<div class="splms-event-category-info">
								<div>
									<h2><?php echo $this->item->title; ?></h2>
									<p>
										<?php echo HTMLHelper::_('string.truncate', strip_tags($this->item->description), $this->params->get('intro_limit', 150)); ?>
										<?php if ($this->params->get('show_readmore')) { ?>
											<a class="btn btn-primary" href="<?php echo $this->item->url; ?>"><?php echo $this->params->get('readmore_text', Text::_('COM_SPLMS_DETAILS')); ?></a>
										<?php } ?>
									</p>
								</div>
								<a href="<?php echo $this->item->url; ?>"><?php echo Text::_('COM_SPLMS_DETAILS'); ?></a>
							</div>
						</div>
					</div>
				<?php } ?>
			</div>
	<?php } } // END:: array_chunk ?>

	<?php if ($this->params->get('hide_pagination', 0) == 0) {?>
		<?php if ($this->pagination->pagesTotal > 1) { ?>
			<div class="pagination">
				<?php echo $this->pagination->getPagesLinks(); ?>
			</div>
		<?php } ?>
	<?php }?>
	
</div>