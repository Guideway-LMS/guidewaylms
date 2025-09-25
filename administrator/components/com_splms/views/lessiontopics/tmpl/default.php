<?php
/**
 * @package com_splms
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2024 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
 */

// No Direct Access
defined ('_JEXEC') or die('Resticted Aceess');

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Layout\LayoutHelper;

$user 		= Factory::getUser();
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn 	= $this->escape($this->state->get('list.direction'));
$canOrder 	= $user->authorise('core.edit.state','com_splms');
$saveOrder = ($listOrder == 'a.ordering');
if($saveOrder && !empty($this->items))
{
	if(SplmsHelper::getJoomlaVersion() < 4)
	{
		$saveOrderingUrl = 'index.php?option=com_splms&task=lessiontopics.saveOrderAjax&tmpl=component';
		$html = HTMLHelper::_('sortablelist.sortable', 'lessiontopicList','adminForm', strtolower($listDirn),$saveOrderingUrl);
	}
	else
	{
		$saveOrderingUrl = 'index.php?option=com_splms&task=lessiontopics.saveOrderAjax&tmpl=component&' . Session::getFormToken() . '=1';
		HTMLHelper::_('draggablelist.draggable');
	}
}
HTMLHelper::_('jquery.framework', false);
?>



<form action="<?php echo Route::_('index.php?option=com_splms&view=lessiontopics'); ?>" method="POST" name="adminForm" id="adminForm">
	<?php if (SplmsHelper::getJoomlaVersion() < 4 && !empty($this->sidebar)) { ?>
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>

	<div id="j-main-container" class="span10" >
		<?php } else { ?>
			<div id="j-main-container"></div>
		<?php } ?>

		<?php echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>
		<div class="clearfix"></div>
		<?php if (!empty($this->items)) { ?>
			<table class="table table-striped" id="lessiontopicList">
				<thead>
					<tr>
						<th class="nowrap center hidden-phone" width="1%">
							<?php echo HTMLHelper::_('searchtools.sort', '', 'a.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-menu-2'); ?>
						</th>

						<th width="1%" class="hidden-phone">
							<input type="checkbox" name="checkall-toggle" value="" title="<?php echo Text::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" />
						</th>

						<th width="1%" class="nowrap center">
							<?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'a.published', $listDirn, $listOrder); ?>
						</th>

						<th>
							<?php echo HTMLHelper::_('searchtools.sort','JGLOBAL_TITLE','a.title',$listDirn,$listOrder); ?>
						</th>

						<th class="nowrap">
							<?php echo HTMLHelper::_('searchtools.sort',  'COM_SPLMS_TITLE_COURSE', 'a.course_id', $listDirn, $listOrder); ?>
						</th>
						
						<th width="8%" class="nowrap">
							<?php echo HTMLHelper::_('searchtools.sort','JAUTHOR','a.created_by',$listDirn,$listOrder); ?>
						</th>
						
						<th width="10%" class="nowrap hidden-phone">
							<?php echo HTMLHelper::_('searchtools.sort','COM_SPLMS_HEADING_DATE_CREATED','a.created',$listDirn,$listOrder); ?>
						</th>
						
						<th width="10%" class="nowrap hidden-phone">
							<?php echo HTMLHelper::_('searchtools.sort',  'JGRID_HEADING_ACCESS', 'a.access', $listDirn, $listOrder); ?>
						</th>
						
						<th width="8%" class="nowrap hidden-phone">
							<?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_LANGUAGE', 'a.language', $listDirn, $listOrder); ?>
						</th>
						
						<th width="2%" class="nowrap hidden-phone">
							<?php echo HTMLHelper::_('searchtools.sort','JGRID_HEADING_ID','a.id',$listDirn,$listOrder); ?>
						</th>

					</tr>
				</thead>

				<tfoot>
					<tr>
						<td colspan="10">
							<?php echo $this->pagination->getListFooter(); ?>
						</td>
					</tr>
				</tfoot>

				<?php if(SplmsHelper::getJoomlaVersion() < 4) :?>
				<tbody>
				<?php else: ?>
					<tbody <?php if ($saveOrder) :?> class="js-draggable" data-url="<?php echo $saveOrderingUrl; ?>" data-direction="<?php echo strtolower($listDirn); ?>" data-nested="false"<?php endif; ?>>
				<?php endif; ?>
					<?php foreach($this->items as $i => $item): ?>

						<?php
						$canCheckin	= $user->authorise('core.manage', 'com_checkin') || $item->checked_out == $user->get('id') || $item->checked_out == 0;
						$canChange		= $user->authorise('core.edit.state', 'com_splms') && $canCheckin;
						$canEdit		= $user->authorise( 'core.edit', 'com_splms' );
						?>

						<tr class="row<?php echo $i % 2; ?>" <?php echo SplmsHelper::getJoomlaVersion() < 4 ? 'sortable-group-id="1"' : 'data-draggable-group="1"';?>>
							<td class="order nowrap center hidden-phone">
								<?php if($canChange) :
									$disableClassName = '';
									$disabledLabel = '';
									if(!$saveOrder) :
										$disabledLabel = Text::_('JORDERINGDISABLED');
										$disableClassName = 'inactive tip-top';
									endif;
									?>

									<span class="sortable-handler hasTooltip <?php echo $disableClassName; ?>" title="<?php echo $disabledLabel; ?>">
										<i class="icon-menu"></i>
									</span>
									<input type="text" style="display: none;" name="order[]" size="5" class="width-20 text-area-order " value="<?php echo $item->ordering; ?>" >
								<?php else: ?>
									<span class="sortable-handler inactive">
										<i class="icon-menu"></i>
									</span>
								<?php endif; ?>
							</td>

							<td class="center hidden-phone">
								<?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
							</td>

							<td class="center">
								<?php if(SplmsHelper::getJoomlaVersion() < 4) : ?>
								<div class="btn-group">
									<?php echo HTMLHelper::_('jgrid.published', $item->published, $i, 'lessiontopics.', true,'cb');?>
									<?php
										if ($canChange) {
											HTMLHelper::_('actionsdropdown.' . ((int) $item->published === 2 ? 'un' : '') . 'archive', 'cb' . $i, 'lessiontopics');
											HTMLHelper::_('actionsdropdown.' . ((int) $item->published === -2 ? 'un' : '') . 'trash', 'cb' . $i, 'lessiontopics');
											echo HTMLHelper::_('actionsdropdown.render', $this->escape($item->title));
										}
									?>
								</div>
								<?php else : ?>
									<?php echo HTMLHelper::_('jgrid.published', $item->published, $i, 'lessiontopics.', true,'cb');?>
								<?php endif;?>
							</td>

							<td>
								<?php if ($item->checked_out) : ?>
									<?php echo HTMLHelper::_('jgrid.checkedout', $i,$item->editor, $item->checked_out_time, 'lessiontopics.', $canCheckin); ?>
								<?php endif; ?>

								<?php if ($canEdit) : ?>
									<a class="title" href="<?php echo Route::_('index.php?option=com_splms&task=lessiontopic.edit&id='. $item->id); ?>">
										<?php echo $this->escape($item->title); ?>
									</a>
								<?php else : ?>
									<?php echo $this->escape($item->title); ?>
								<?php endif; ?>
							</td>

							<td>
								<?php echo $this->escape($item->course_title); ?>
							</td>

							<td class="hidden-phone">
								<?php echo Factory::getUser($item->created_by)->get('username', $item->created_by); ?>
							</td>

							<td class="hidden-phone">
								<?php echo HTMLHelper::_('date', $item->created, 'd M, Y'); ?>
							</td>

							<td class="hidden-phone">
								<?php echo $this->escape($item->access_title); ?>
							</td>

							<td class="small nowrap hidden-phone">
								<?php if ($item->language == '*') : ?>
									<?php echo Text::alt('JALL', 'language'); ?>
								<?php else:?>
									<?php echo $item->language_title ? $this->escape($item->language_title) : Text::_('JUNDEFINED'); ?>
								<?php endif;?>
							</td>	

							<td align="center">
								<?php echo $item->id; ?>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php } else { ?>
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
			
		<?php } ?>

		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<?php echo HTMLHelper::_('form.token'); ?>
	</div>
</form>
	
