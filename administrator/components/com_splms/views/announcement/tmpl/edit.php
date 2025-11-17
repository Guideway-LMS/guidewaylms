<?php
// No direct access
defined('_JEXEC') or die;

JHtml::_('behavior.formvalidator');
JHtml::_('behavior.keepalive');
?>

<form action="<?php echo JRoute::_('index.php?option=com_splms&layout=edit&id=' . (int) $this->item->id); ?>"
      method="post" name="adminForm" id="adminForm" class="form-validate">

    <div class="width-60 fltlft">
        <fieldset class="adminform">
            <legend><?php echo JText::_('COM_SPLMS_ANNOUNCEMENT_DETAILS'); ?></legend>

            <ul class="adminformlist">
                <?php foreach ($this->form->getFieldset('details') as $field): ?>
                    <li>
                        <?php echo $field->label; ?>
                        <?php echo $field->input; ?>
                    </li>
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
