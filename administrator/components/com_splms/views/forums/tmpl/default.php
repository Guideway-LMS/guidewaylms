<?php
/**
 * @package     SP LMS
 * @subpackage  Components
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Language\Text;


HTMLHelper::_('formbehavior.chosen', 'select');
?>

<form action="<?php echo Route::_('index.php?option=com_splms&view=forums'); ?>" method="post" name="adminForm" id="adminForm">
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
		<div class="row-fluid">
			<div class="span12">
                <!-- Search Tools removido temporariamente pois filterForm nao foi criado ainda -->
                <?php // echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>
                
                 <div id="filter-bar" class="btn-toolbar">
                    <div class="filter-search btn-group pull-left">
                        <label for="filter_search" class="element-invisible"><?php echo Text::_('JSEARCH_FILTER_LABEL'); ?></label>
                        <input type="text" name="filter_search" id="filter_search" placeholder="<?php echo Text::_('JSEARCH_FILTER'); ?>" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" title="<?php echo Text::_('JSEARCH_FILTER'); ?>" />
                    </div>
                    <div class="btn-group pull-left">
                        <button class="btn hasTooltip" type="submit" title="<?php echo Text::_('JSEARCH_FILTER_SUBMIT'); ?>"><i class="icon-search"></i></button>
                        <button class="btn hasTooltip" type="button" title="<?php echo Text::_('JSEARCH_FILTER_CLEAR'); ?>" onclick="document.getElementById('filter_search').value='';this.form.submit();"><i class="icon-remove"></i></button>
                    </div>
                </div>
			</div>
		</div>

		<table class="table table-striped" id="forumList">
			<thead>
				<tr>
					<th width="1%" class="center">
						<?php echo HTMLHelper::_('grid.checkall'); ?>
					</th>
					<th class="nowrap">
						Título
					</th>
					<th width="15%">
						Autor
					</th>
					<th width="15%">
						Curso
					</th>
					<th width="10%" class="center">
						Respostas
					</th>
                    <th width="10%" class="center">
						Resolvido
					</th>
					<th width="10%" class="center">
						Data
					</th>
					<th width="1%" class="nowrap center">
						ID
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
			<tbody>
				<?php if (!empty($this->items)) : ?>
					<?php foreach ($this->items as $i => $item) : ?>
						<tr class="row<?php echo $i % 2; ?>">
							<td class="center">
								<?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
							</td>
							<td>
                                <a href="<?php echo Route::_('index.php?option=com_splms&task=forum.edit&id=' . (int) $item->id); ?>">
                                    <?php echo $this->escape($item->title); ?>
                                </a>
							</td>
							<td>
								<?php echo $this->escape($item->author_name); ?>
							</td>
							<td>
								<?php echo $this->escape($item->course_title); ?>
							</td>
                            <td class="center">
                                <?php echo (int) $item->total_answers; // Model nao traz isso por padrao no getListQuery simples, mas adicionamos no Model ?>
                            </td>
                            <td class="center">
                                <?php echo ($item->solved) ? '<span class="label label-success">Sim</span>' : '<span class="label">Não</span>'; ?>
                            </td>
							<td class="center">
								<?php echo HTMLHelper::_('date', $item->created_on, 'd/m/Y H:i'); ?>
							</td>
							<td class="center">
								<?php echo (int) $item->id; ?>
							</td>
						</tr>
					<?php endforeach; ?>
				<?php else : ?>
                    <tr>
                        <td colspan="8" class="center">Nenhuma pergunta encontrada.</td>
                    </tr>
                <?php endif; ?>
			</tbody>
		</table>

		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<?php echo HTMLHelper::_('form.token'); ?>
	</div>
</form>
