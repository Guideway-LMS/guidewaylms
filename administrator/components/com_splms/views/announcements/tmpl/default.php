<?php
/**
 * @package     JoomShaper\Component\Splms
 * @subpackage  Administrator.Layout
 * @license     GNU General Public License
 */

// Acesso restrito
defined('_JEXEC') or die;

// IMPORTANTE: NÃO ADICIONE DECLARAÇÕES 'use' EM ARQUIVOS TMPl.
// Use as classes legadas JHtml, JText, JRoute para compatibilidade no layout.

// Ativa os tooltips e a validação do formulário
JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect'); // Habilita a seleção de múltiplos itens

?>

<form action="<?php echo JRoute::_('index.php?option=com_splms&view=announcements'); ?>" method="post" name="adminForm" id="adminForm">

    <div class="clearfix"></div>

    <table class="table table-striped table-hover">
        
        <thead class="table-dark">
            <tr>
                <th width="1%" class="text-center">
                    <?php echo JHtml::_('grid.checkall'); ?>
                </th>
                
                <th class_="">
                    <?php echo JText::_('COM_SPLMS_ANNOUNCEMENT_TITLE'); ?>
                </th>
                
                <th width="20%">
                    <?php echo JText::_('COM_SPLMS_ANNOUNCEMENT_COURSE'); ?>
                </th>
                
                <th width="15%">
                    <?php echo JText::_('COM_SPLMS_ANNOUNCEMENT_AUTHOR'); ?>
                </th>

                <th width="10%">
                    <?php echo JText::_('COM_SPLMS_ANNOUNCEMENT_DATE'); ?>
                </th>
            </tr>
        </thead>
        
        <tbody>
            <?php if (!empty($this->items)) : ?>
                <?php foreach ($this->items as $i => $item) : 
                    // [Checklist] Adicionar o link no Título para a tela de edição
                    $linkEdit = JRoute::_('index.php?option=com_splms&task=announcement.edit&id=' . (int) $item->id);
                ?>
                    <tr class="row<?php echo $i % 2; ?>">
                        
                        <td class="text-center">
                            <?php echo JHtml::_('grid.id', $i, $item->id); ?>
                        </td>

                        <td>
                            <a href="<?php echo $linkEdit; ?>" title="<?php echo JText::_('JACTION_EDIT'); ?>">
                                <?php echo $this->escape($item->title); ?>
                            </a>
                        </td>

                        <td>
                            <?php echo $this->escape($item->course_title); ?>
                        </td>

                        <td>
                            <?php echo $this->escape($item->author_name); ?>
                        </td>
                        
                        <td>
                            <?php echo JHtml::_('date', $item->created_at, JText::_('DATE_FORMAT_LC4')); ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
        
        <tfoot>
            <tr>
                <td colspan="5">
                    <?php echo $this->pagination->getListFooter(); ?>
                </td>
            </tr>
        </tfoot>
        
    </table>

    <input type="hidden" name="task" value="">
    <input type="hidden" name="boxchecked" value="0">
    <?php echo JHtml::_('form.token'); ?>
</form>