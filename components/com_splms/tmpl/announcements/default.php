<?php
/**
 * @package     JoomShaper\Component\Splms
 * @subpackage  Site.Layout
 * @license     GNU General Public License
 */

// Acesso restrito
defined('_JEXEC') or die;

// Importa as classes modernas que vamos usar
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

// Adiciona alguns estilos básicos para os avisos.
// Esta é uma boa prática para não depender de arquivos CSS externos
// ao injetar uma view.
$doc = Factory::getDocument();
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

// [Checklist] Escrever o HTML/PHP (foreach) para exibir os avisos

// Verifica se o Model (Tarefa 1.5.2) retornou algum item
if (empty($this->items))
{
    // Exibe uma mensagem amigável se não houver avisos
    echo '<div class="alert alert-info">' 
        . Text::_('COM_SPLMS_NO_ANNOUNCEMENTS_FOUND') // TODO: Criar esta string de idioma
        . '</div>';
    return;
}
?>

<div class="splms-announcements-wrapper">
    <ul class="splms-announcement-list">
        
        <?php foreach ($this->items as $item) : ?>
            <li class="splms-announcement-item">
                
                <!-- Cabeçalho: Título -->
                <div class="splms-announcement-header">
                    <h3><?php echo $this->escape($item->title); ?></h3>
                </div>

                <!-- Meta: Autor e Data -->
                <div class="splms-announcement-meta">
                    <?php // TODO: Criar estas strings de idioma ?>
                    <strong><?php echo Text::_('COM_SPLMS_ANNOUNCEMENT_BY'); ?>:</strong> 
                    <?php echo $this->escape($item->author_name); ?>
                    
                    <span class="mx-2">|</span>

                    <strong><?php echo Text::_('COM_SPLMS_ANNOUNCEMENT_ON'); ?>:</strong> 
                    <?php echo HTMLHelper::_('date', $item->created_on, Text::_('DATE_FORMAT_LC3')); ?>
                </div>

                <!-- Corpo: A Mensagem (description) -->
                <div class="splms-announcement-body">
                    <?php
                        // Nota: Usamos 'echo' direto, sem 'escape'.
                        // O campo 'description' vem do editor de texto do admin,
                        // que foi salvo com 'filter="safehtml"' (Tarefa 1.3.2).
                        // Isso preserva a formatação (negrito, listas, etc.).
                        echo $item->description; 
                    ?>
                </div>
                
            </li>
        <?php endforeach; ?>
        
    </ul>
    
    <!-- Paginação (se houver) -->
    <?php if ($this->pagination->get('pages.total') > 1) : ?>
        <div class="pagination">
            <?php echo $this->pagination->getPagesLinks(); ?>
        </div>
    <?php endif; ?>
    
</div>