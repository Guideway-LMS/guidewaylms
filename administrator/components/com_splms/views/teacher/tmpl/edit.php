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

$doc = Factory::getDocument();
HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('behavior.keepalive');

if(SplmsHelper::getJoomlaVersion() < 4)
{
  HTMLHelper::_('formbehavior.chosen', 'select', null, array('disable_search_threshold' => 0 ));
}


$rowClass = JVERSION < 4 ? 'row-fluid' : 'row';
$colClass = JVERSION < 4 ? 'span' : 'col-lg-';
$JHtmlTag = JVERSION < 4 ? 'bootstrap' : 'uitab';
?>
<form action="<?php echo Route::_('index.php?option=com_splms&layout=edit&id=' . (int) $this->item->id); ?>"
  method="post" name="adminForm" id="adminForm" class="form-validate">
  <div class="form-horizontal">
    <div class="<?php echo $rowClass;?>">
      <div class="<?php echo $colClass;?>9">

        <?php echo HTMLHelper::_($JHtmlTag . '.startTabSet', 'myTab', array('active' => 'basic')); ?>

          <?php echo HTMLHelper::_($JHtmlTag . '.addTab', 'myTab', 'basic', 'Basic'); ?>
          <div>
            <?php echo $this->form->renderFieldset('basic'); ?>
          </div>
          <?php echo HTMLHelper::_($JHtmlTag . '.endTab'); ?>

          <?php echo HTMLHelper::_($JHtmlTag . '.addTab', 'myTab', 'skills', Text::_('COM_SPLMS_TEACHER_FIELD_SKILLS')); ?>
            <div>
              <?php echo $this->form->renderFieldset('skills'); ?>
            </div>
          <?php echo HTMLHelper::_($JHtmlTag . '.endTab'); ?>

          <?php echo HTMLHelper::_($JHtmlTag . '.addTab', 'myTab', 'education', Text::_('COM_SPLMS_TEACHER_FIELD_EDUCATION')); ?>
            <div>
              <?php echo $this->form->renderFieldset('education'); ?>
            </div>
          <?php echo HTMLHelper::_($JHtmlTag . '.endTab'); ?>

          <?php echo HTMLHelper::_($JHtmlTag . '.addTab', 'myTab', 'social', 'Social'); ?>
            <div>
              <?php echo $this->form->renderFieldset('social'); ?>
            </div>
          <?php echo HTMLHelper::_($JHtmlTag . '.endTab'); ?>

        <?php echo HTMLHelper::_($JHtmlTag . '.endTabSet'); ?>

      </div>

      <div class="<?php echo $colClass;?>3">
        <fieldset class="form-vertical">
          <?php echo $this->form->renderFieldset('sidebar'); ?>
        </fieldset>
      </div>
    </div>

  </div>

  <input type="hidden" name="task" value="course.edit" />
  <?php echo HTMLHelper::_('form.token'); ?>
</form>
