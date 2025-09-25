<?php
/**
* @package com_splms
* @author JoomShaper http://www.joomshaper.com
* @copyright Copyright (c) 2010 - 2024 JoomShaper
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/

// No direct access to this file
defined('_JEXEC') or die('Restricted Access');

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Layout\LayoutHelper;

if(SplmsHelper::getJoomlaVersion() < 4)
{
	HTMLHelper::_('formbehavior.chosen', 'select');
}

$doc = Factory::getDocument();
$doc->addStylesheet( Uri::root(true) . '/administrator/components/com_splms/assets/css/font-awesome.min.css' );

$user		= Factory::getUser();
$userId		= $user->get('id');

$listOrder = $this->escape($this->filter_order);
$listDirn = $this->escape($this->filter_order_Dir);
$saveOrder = $listOrder == 'a.ordering';

if ($saveOrder && !empty($this->items))
{
	if(SplmsHelper::getJoomlaVersion() < 4)
	{
		$saveOrderingUrl = 'index.php?option=com_splms&task=reviews.saveOrderAjax&tmpl=component';
		HTMLHelper::_('sortablelist.sortable', 'reviewList', 'adminForm', strtolower($listDirn), $saveOrderingUrl, false, true);
	}
	else
	{
		$saveOrderingUrl = 'index.php?option=com_splms&task=reviews.saveOrderAjax&tmpl=component&' . Session::getFormToken() . '=1';
		HTMLHelper::_('draggablelist.draggable');
	}
}
?>



<form action="<?php echo Route::_('index.php?option=com_splms&view=reviews'); ?>" method="post" id="adminForm" name="adminForm">
		<?php if (SplmsHelper::getJoomlaVersion() < 4 && !empty( $this->sidebar)) : ?>
		<div id="j-sidebar-container" class="span2">
			<?php echo $this->sidebar; ?>
		</div>
			<div id="j-main-container" class="span10">
		<?php else : ?>
			<div id="j-main-container">
		<?php endif; ?>

		<?php echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>

		<?php if (empty($this->items)) : ?>
			<?php if (SplmsHelper::getJoomlaVersion() < 4) :?>
			<div class="alert alert-no-items">
				<?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
			</div>
			<?php else : ?>
				<div class="alert alert-info">
					<span class="icon-info-circle" aria-hidden="true"></span><span class="visually-hidden"><?php echo Text::_('INFO'); ?></span>
					<?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
				</div>
			<?php endif; ?>
		<?php else : ?>
			<table class="table table-striped" id="reviewList">
				<thead>
				<tr>
					<th width="2%" class="nowrap center hidden-phone">
						<?php echo HTMLHelper::_('searchtools.sort', '', 'a.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-menu-2'); ?>
					</th>
					<th width="2%" class="hidden-phone">
						<?php echo HTMLHelper::_('grid.checkall'); ?>
					</th>
					<th width="1%" class="nowrap center">
						<?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'a.published', $listDirn, $listOrder); ?>
					</th>
					<th>
						<?php echo HTMLHelper::_('searchtools.sort', 'COM_SPLMS_REVIEWS_FIELD_TITLE', 'a.review', $listDirn, $listOrder); ?>
					</th>
					<th width="15%" class="nowrap hidden-phone">
						<?php echo HTMLHelper::_('searchtools.sort',  'COM_SPLMS_FIELD_COURSE_NAME', 'a.course_id', $listDirn, $listOrder); ?>
					</th>
					<th width="10%" class="nowrap hidden-phone">
						<?php echo HTMLHelper::_('searchtools.sort',  'JAUTHOR', 'a.created_by', $listDirn, $listOrder); ?>
					</th>
					<th width="10%" class="nowrap hidden-phone">
						<?php echo HTMLHelper::_('searchtools.sort', 'COM_SPLMS_HEADING_DATE_CREATED', 'a.created', $listDirn, $listOrder); ?>
					</th>
					<th width="1%" class="nowrap hidden-phone">
						<?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
					</th>
				</tr>
				</thead>

				<tfoot>
					<tr>
						<td colspan="12">
							<?php echo $this->pagination->getListFooter(); ?>
						</td>
					</tr>
				</tfoot>

					<?php if(SplmsHelper::getJoomlaVersion() < 4) :?>
					<tbody>
					<?php else: ?>
						<tbody <?php if ($saveOrder) :?> class="js-draggable" data-url="<?php echo $saveOrderingUrl; ?>" data-direction="<?php echo strtolower($listDirn); ?>" data-nested="false"<?php endif; ?>>
					<?php endif; ?>
					<?php if (!empty($this->items)) : ?>
						<?php foreach ($this->items as $i => $item) :
							$item->max_ordering = 0;
							$ordering   = ($listOrder == 'a.ordering');
							$canEdit    = $user->authorise('core.edit', 'com_splms.review.' . $item->id) || ($user->authorise('core.edit.own',   'com_splms.review.' . $item->id) && $item->created_by == $userId);
							$canCheckin = $user->authorise('core.manage', 'com_checkin') || $item->checked_out == $userId || $item->checked_out == 0;
							$canChange  = $user->authorise('core.edit.state', 'com_splms.review.' . $item->id) && $canCheckin;
							$link = Route::_('index.php?option=com_splms&task=review.edit&id=' . $item->id);
						?>
							<tr class="row<?php echo $i % 2; ?>" <?php echo SplmsHelper::getJoomlaVersion() < 4 ? 'sortable-group-id="1"' : 'data-draggable-group="1"';?>>
								<td class="order nowrap center hidden-phone">
									<?php
									$iconClass = '';
									if (!$canChange)
									{
										$iconClass = ' inactive';
									}
									elseif (!$saveOrder)
									{
										$iconClass = ' inactive tip-top hasTooltip" title="' . HTMLHelper::tooltipText('JORDERINGDISABLED');
									}
									?>
									<span class="sortable-handler<?php echo $iconClass ?>">
										<span class="icon-menu"></span>
									</span>
									<?php if ($canChange && $saveOrder) : ?>
										<input type="text" style="display:none" name="order[]" size="5" value="<?php echo $item->ordering; ?>" class="width-20 text-area-order " />
									<?php endif; ?>
								</td>
								<td class="hidden-phone">
									<?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
								</td>

								<td class="center">
									<?php echo HTMLHelper::_('jgrid.published', $item->published, $i, 'reviews.', $canChange);?>
								</td>

								<td>
									<?php if ($item->checked_out) : ?>
										<?php echo HTMLHelper::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'reviews.', $canCheckin); ?>
									<?php endif; ?>
									<div class="item-rating" style="margin-bottom: 10px;">
										<?php for ($i=0; $i < $item->rating; $i++) { ?>
											<i class="fa fa-star" style="color: #ffc000;"></i>
										<?php } ?>

										<?php for ($i=0; $i < 5-$item->rating; $i++) { ?>
											<i class="fa fa-star-o" style="color: #ffc000;"></i>
										<?php } ?>

										<?php echo '('. $item->rating .')'; ?>
									</div><!-- /.item-rating -->

									<?php if($item->review){ ?>
										<div class="item-review" style="margin-bottom: 10px;">
											<?php echo HTMLHelper::_('string.truncate', $item->review, 250); ?>
										</div> <!-- /.item-review -->
									<?php } ?>

									<?php if ($canEdit) : ?>
										<a href="<?php echo Route::_('index.php?option=com_splms&task=review.edit&id='.$item->id);?>">
											<?php echo Text::_('COM_SPLMS_EDIT_REVIEW_FIELD_TITLE'); ?>
										</a>
									<?php endif ; ?>
								</td>
								<td class="hidden-phone">
									<?php echo $item->course_title; ?>
								</td>
								<td class="hidden-phone">
									<a class="hasTooltip" href="<?php echo Route::_('index.php?option=com_users&task=user.edit&id=' . (int) $item->created_by); ?>" title="<?php echo Text::_('JAUTHOR'); ?>">
										<?php echo $this->escape($item->author_name); ?>
									</a>
								</td>
								<td class="nowrap small hidden-phone">
									<?php
									echo $item->created > 0 ? HTMLHelper::_('date', $item->created, Text::_('DATE_FORMAT_LC4')) : '-';
									?>
								</td>
								<td align="center" class="hidden-phone">
									<?php echo $item->id; ?>
								</td>
							</tr>
						<?php endforeach; ?>
					<?php endif; ?>
				</tbody>
			</table>
		<?php endif; ?>
	</div>

	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<?php echo HTMLHelper::_('form.token'); ?>
</form>
