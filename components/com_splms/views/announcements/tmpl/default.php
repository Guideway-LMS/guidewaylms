<?php
/**
 * @package     com_splms
 * @subpackage  site
 * @license     GNU General Public License
 */

// Acesso restrito
defined('_JEXEC') or die;

// Adiciona o CSS (Tarefa 1.5.5)
$doc = JFactory::getDocument();
$doc->addStyleDeclaration('
    .splms-announcement-list { 
        list-style: none; 
        padding-left: 0; 
    }
    .splms-announcement-item { 
        background: #f9f9f9; 
        border: 1px solid #eee; 
        border-radius: 8px; 
        margin-bottom: 20px; 
        padding: 20px;
    }
    .splms-announcement-header h3 { 
        margin-top: 0; 
        margin-bottom: 5px;
        font-size: 1.5rem;
    }
    .splms-announcement-meta { 
        font-size: 0.9em; 
        color: #666; 
        margin-bottom: 15px; 
        border-bottom: 1px solid #eee;
        padding-bottom: 10px;
    }
    .splms-announcement-body { 
        font-size: 1rem;
        line-height: 1.6;
    }
');

// Verifica se o Model (Tarefa 1.5.2) retornou algum item
if (empty($this->items))
{
    // Exibe uma mensagem amigável se não houver avisos
    echo '<div class="alert alert-info">' 
        . JText::_('COM_SPLMS_NO_ANNOUNCEMENTS_FOUND')
        . '</div>';
    return;
}
?>

<div class="splms-announcements-wrapper">
    <ul class="splms-announcement-list">
        
        <?php foreach ($this->items as $item) : ?>
            <li class="splms-announcement-item">
                
                <div class="splms-announcement-header">
                    <h3><?php echo $this->escape($item->title); ?></h3>
                </div>

                <div class="splms-announcement-meta">
                    <strong><?php echo JText::_('COM_SPLMS_ANNOUNCEMENT_BY'); ?>:</strong> 
                    <?php echo $this->escape($item->author_name); ?>
                    
                    <span class="mx-2">|</span>

                    <strong><?php echo JText::_('COM_SPLMS_ANNOUNCEMENT_ON'); ?>:</strong> 
                    <?php echo JHtml::_('date', $item->created_on, JText::_('DATE_FORMAT_LC3')); ?>
                </div>

                <div class="splms-announcement-body">
                    <?php
                        // Nota: Usamos 'echo' direto, sem 'escape', 
                        // pois o campo foi salvo com 'filter="safehtml"' no admin.
                        echo $item->description; 
                    ?>
                </div>
                
            </li>
        <?php endforeach; ?>
        
    </ul>
    
    <?php if ($this->pagination->get('pages.total') > 1) : ?>
        <div class="pagination">
            <?php echo $this->pagination->getPagesLinks(); ?>
        </div>
    <?php endif; ?>
    
</div>