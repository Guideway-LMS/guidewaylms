<?php
// No direct access
defined('_JEXEC') or die;

JHtml::_('behavior.formvalidator');
JHtml::_('behavior.keepalive');

use Joomla\CMS\Factory;

$doc = Factory::getDocument();
$doc->addStyleSheet(JUri::root(true) . '/administrator/components/com_splms/assets/css/tolbar_ai.css?v=' . time());
$doc->addScript(JUri::root(true) . '/administrator/components/com_splms/assets/js/guideway_ai.js');
$doc->addScript(JUri::root(true) . '/administrator/components/com_splms/assets/js/guideway_ai_chat.js');
?>

<style>
    .adminformlist li label {
        font-size: 1.15rem;
        font-weight: 600;
        margin-bottom: 5px;
    }
    .adminformlist li input[type="text"],
    .adminformlist li select,
    .adminformlist li textarea {
        font-size: 1.15rem;
        padding: 8px;
    }
    .adminformlist {
        list-style: none;
        padding-left: 0;
    }
    .adminformlist li {
        margin-bottom: 15px;
    }
</style>
<form action="<?php echo JRoute::_('index.php?option=com_splms&layout=edit&id=' . (int) $this->item->id); ?>"
      method="post" name="adminForm" id="adminForm" class="form-validate">

    <div class="width-60 fltlft">
        <fieldset class="adminform">
            <legend><?php echo JText::_('COM_SPLMS_ANNOUNCEMENT_DETAILS'); ?></legend>

            <ul class="adminformlist">
                <?php foreach ($this->form->getFieldset('details') as $field): ?>
                    <?php if ($field->fieldname == 'message'): ?>
                        <li>
                            <?php 
                            // === Toolbar AI ===
                            $hideGenerateQuestions = true; // Ocultar gerador de quiz no mural

                            // AI: Generate (Crie a descrição / Custom / PDF)
                            if (Factory::getUser()->authorise('ai.generate', 'com_splms')) {
                                include JPATH_COMPONENT_ADMINISTRATOR . '/views/announcement/tmpl/toolbar_ai_chat.php';
                            }

                            // AI: Refine (Organize / Revisar / Resumir)
                            if (Factory::getUser()->authorise('ai.refine', 'com_splms')) {
                                include JPATH_COMPONENT_ADMINISTRATOR . '/views/announcement/tmpl/toolbar_ai.php';
                            }
                            ?>
                        </li>
                    <?php endif; ?>
                    <?php if (!$field->hidden): ?>
                        <li>
                            <?php echo $field->label; ?>
                            <?php echo $field->input; ?>
                        </li>
                    <?php else: ?>
                        <?php echo $field->input; ?>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ul>
        </fieldset>
    </div>

    <div class="width-40 fltrt">
        <fieldset class="adminform">
            <legend><?php echo JText::_('COM_SPLMS_ANNOUNCEMENT_PUBLISHING'); ?></legend>

            <ul class="adminformlist">
                <?php foreach ($this->form->getFieldset('hidden') as $field): ?>
                    <?php echo $field->input; ?>
                <?php endforeach; ?>
            </ul>
        </fieldset>
    </div>

    <input type="hidden" name="task" value="">
    <?php echo JHtml::_('form.token'); ?>
</form>
