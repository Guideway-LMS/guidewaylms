<?php
/**
 * @package     JoomShaper\Component\Splms
 * @subpackage  Administrator.Layout
 * @license     GNU General Public License
 */

// Acesso restrito
defined('_JEXEC') or die;

// Importa as classes modernas que vamos usar
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Token;

// Ativa os tooltips e a validação do formulário (baseado no XML)
HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.keepalive'); // Mantém a sessão ativa
HTMLHelper::_('behavior.formvalidator'); // Ativa a validação (required="true")

?>

<form action="<?php echo Route::_('index.php?option=com_splms&view=announcement&layout=edit&id=' . (int) $this->item->id); ?>"
      method="post" name="adminForm" id="item-form" class="form-validate form-horizontal">

    <div class="row">
        <div class="col-lg-9">
            <?php
            // Renderiza o fieldset 'details' (Título, Descrição, Curso)
            // que definimos no announcement.xml
            echo LayoutHelper::render('joomla.edit.fieldset', [
                'fieldset' => $this->form->getFieldset('details'),
                'control'  => 'jform'
            ]);
            ?>
        </div>

        <div class="col-lg-3">
            <?php
            // Renderiza o fieldset 'publish' (Status, Acesso, Autor, Data)
            // que definimos no announcement.xml
            echo LayoutHelper::render('joomla.edit.fieldset', [
                'fieldset' => $this->form->getFieldset('publish'),
                'control'  => 'jform'
            ]);
            ?>
        </div>
    </div>

    <input type="hidden" name="task" value="">
    <?php echo Token::render(); ?>
</form>