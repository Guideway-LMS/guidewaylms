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
use Joomla\CMS\HTML\HTMLHelper;

$doc = Factory::getDocument();
HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('behavior.keepalive');
if(SplmsHelper::getJoomlaVersion() < 4)
{
	HTMLHelper::_('formbehavior.chosen','select',null,array('disable_search_threshold' => 0));
}

$rowClass = SplmsHelper::getJoomlaVersion() < 4 ? 'row-fluid' : 'row';
$colClass = SplmsHelper::getJoomlaVersion() < 4 ? 'span' : 'col-lg-';
?>

<form action="<?php echo Route::_('index.php?option=com_splms&view=lessiontopic&layout=edit&id=' . (int) $this->item->id); ?>" name="adminForm" id="adminForm" method="post" class="form-validate">
	
	<div class="form-horizontal">
		<div class="<?php echo $rowClass;?>">
		<div class="<?php echo $colClass;?>9">
			<?php echo $this->form->renderFieldset('basic'); ?>
		</div>

		<div class="<?php echo $colClass;?>3">
			<fieldset class="form-vertical">
			<?php echo $this->form->renderFieldset('sidebar'); ?>
			</fieldset>
		</div>
		</div>
	</div>

	<input type="hidden" name="task" value="lessiontopic.edit" />
	<?php echo HTMLHelper::_('form.token'); ?>
	</div>
</form>

	
