<?php 
/** 
 * @package GPTRANSLATE::CONFIG::administrator::components::com_gptranslate
 * @subpackage views
 * @subpackage config
 * @subpackage tmpl
 * @author Joomla! Extensions Store
 * @copyright (C) 2015 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html 
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
use Joomla\CMS\Language\Text;
?> 
<form action="index.php" method="post" name="adminForm" id="adminForm" class="form-validate configform">  
	<?php 
	$fieldSets = $this->params_form->getFieldsets();
	$tabs = array();
	$contents = array();
	foreach ($fieldSets as $name => $fieldSet) :
		$label = empty($fieldSet->label) ? Text::_('COM_GPTRANSLATE_'. strtoupper($name) .'_FIELDSET_LABEL') : Text::_($fieldSet->label);
		$tabs[] = "<li class='nav-item' id='" . $fieldSet->id . "-tab' aria-controls='$fieldSet->id' role='tab'><a class='nav-link' href='#$fieldSet->id' data-bs-toggle='tab' data-element='$fieldSet->id'>$label</a></li>";
		ob_start(); ?>
		<div id="<?php echo $fieldSet->id;?>" class="tab-pane" role="tabpanel" aria-labelledby="<?php echo $fieldSet->id;?>-tab">
		<?php  
		foreach ($this->params_form->getFieldset($name) as $field):
		?>
			<div class="control-group">
				<div class="control-label"><?php echo $field->label; ?></div>
				<div class="controls" aria-describedby="<?php echo $field->id;?>_arialbl"><?php echo $field->input; ?></div>
				<?php if(trim($field->description)):?>
					<div id="<?php echo $field->id;?>_arialbl"><small class="form-text text-muted"><?php echo Text::_($field->description)?></small></div>
				<?php endif;?>
			</div>
		<?php endforeach; ?>
		</div>
		<?php $contents[] = ob_get_clean();?>
	<?php endforeach; ?>
	
	<ul id="tab_configuration" class="nav nav-tabs" role="tablist" aria-label="<?php echo Text::_( 'COM_GPTRANSLATE_CONFIG' );?>"><?php echo implode('', $tabs);?></ul>
	<div id="config-gptranslate" class="tab-content current"><?php echo implode('', $contents);?></div> 
	<input type="hidden" name="option" value="<?php	echo $this->option;?>" /> 
	<input type="hidden" name="task" value="config.display" />
</form> 