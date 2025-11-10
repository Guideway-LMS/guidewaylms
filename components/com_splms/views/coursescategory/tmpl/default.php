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
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;

$menu = Factory::getApplication()->getMenu()->getActive();
$params = !empty($menu) ? $menu->getParams() : new Registry;
$columns = $params->get('columns', 3);
?>

<div id="splms" class="splms splms-view-category-courses">
	<?php if (count($this->category_items)) { ?>
		<!-- Filter Buttons -->
		<div>
			<form action="<?php echo Route::_('index.php?option=com_splms&view=coursescategories', false); ?>" id="splms-coursescategories-filters-form" >
				<ul class="splms-filter-option">
					<?php
					if ($this->subcategory_items) { ?>
						<li class='selected btn btn-sm btn-primary' data-target="all">
							<?php echo Text::_('COM_SPLMS_SHUFFLE_SHOW_ALL'); ?>
						</li>
						<?php foreach ($this->subcategory_items as $key => $value) {
						?>
							<li class="btn btn-sm btn-primary" data-target="<?php echo $value->alias; ?>">
								<?php echo $value->title ?>
							</li>
					<?php }
					}
					?>
					<button type="reset" class="splms-btn btn btn-link splms-action-reset">
						<?php echo Text::_('COM_SPLMS_BTN_RESET'); ?>
					</button>
				</ul>
			</form>
		</div>

		<div class="splms-courses-list">

			<div class="splms-row splms-popular-course-wrapper splms-shuffle">
				<?php foreach ($this->category_items as $category_item) { ?>

					<div class="<?php echo ($columns > 3) ? 'splms-col-sm-6 splms-col-md-' . round(12 / $columns) : 'splms-col-md-' . round(12 / $columns); ?>" data-groups='["all","<?php echo $category_item->coursecategory_alias[0]; ?>"]'>
						<div class="splms-course splms-match-height">
							<div class="splms-common-overlay-wrapper">

								<?php $category_item->thumbnail = SplmsHelper::getThumbnail($category_item->image); ?>

								<img src="<?php echo $category_item->thumbnail; ?>" class="splms-course-img splms-img-responsive" alt="<?php echo $this->item->title; ?>">

								<?php if ($category_item->price == 0) {
									echo '<span class="splms-badge-free">' . Text::_('COM_SPLMS_FREE') . '</span>';
								} ?>

								<div class="splms-common-overlay">
									<div class="splms-vertical-middle">
										<div>
											<a href="<?php echo $category_item->url; ?>" class="splms-readmore btn btn-default">
												<?php echo Text::_('COM_SPLMS_DETAILS'); ?>
											</a>
										</div>
									</div>
								</div>
							</div>
							<div class="splms-course-info">
								<h3 class="splms-courses-title">
									<a href="<?php echo $category_item->url; ?>">
										<?php echo $category_item->title; ?>
									</a>
								</h3>

								<!-- Has teacher -->
								<?php if (!empty($teachers)) { ?>
									<div class="splms-course-teachers">
										<span><?php echo Text::_('COM_SPLMS_BY'); ?></span>

										<?php foreach ($teachers as $teacher) {
											// Get Last Item
											$last_item = end($teachers);
										?>
											<a href="<?php echo $teacher->url; ?>" class="splms-teacher-name">
												<strong>
													<?php echo $teacher->title; ?>
												</strong>
												<?php echo ($teacher == $last_item) ? '' : Text::_(', '); ?>
											</a>
										<?php } // END:: foreach 
										?>
									</div>
								<?php } // END:: has teahcer 
								?>

								<p class="splms-course-short-info"><?php echo $category_item->short_description; ?></p>
								<div class="splms-course-meta">
									<ul>
										<li><?php echo $category_item->c_price; ?></li>
										<li><?php echo count($category_item->lessons); ?> <?php echo Text::_('COM_SPLMS_COMMON_LESSONS'); ?></li>
										<li><?php echo count($category_item->total_attachments); ?> <?php echo Text::_('COM_SPLMS_COMMON_ATTACHMENTS'); ?></li>
									</ul>
								</div>
							</div>
						</div> <!-- /.splms-course -->
					</div> <!-- /.splms-col-sm -->

				<?php } //END:: foreach 
				?>
			</div> <!-- /.splms-row -->
			<?php //} //END:: array_chunk 
			?>

		</div> <!-- /.splms-courses-list -->

	<?php } ?>
</div>
<!-- /.splms -->