<?php

/**
 * @package com_splms
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2024 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\HTML\HTMLHelper;

$doc = Factory::getDocument();
$doc->addStyleSheet(JURI::root(true) . '/administrator/components/com_splms/assets/css/tolbar_ai.css?v=' . time());
$doc->addScript(JUri::root(true) . '/administrator/components/com_splms/assets/js/guideway_ai.js');
$doc->addScript(JUri::root(true) . '/administrator/components/com_splms/assets/js/guideway_ai_chat.js');

HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('behavior.keepalive');
if(SplmsHelper::getJoomlaVersion() < 4)
{
  HTMLHelper::_('formbehavior.chosen', 'select', null, array('disable_search_threshold' => 0 ));
}

$rowClass = SplmsHelper::getJoomlaVersion() < 4 ? 'row-fluid' : 'row';
$colClass = SplmsHelper::getJoomlaVersion() < 4 ? 'span' : 'col-lg-';
?>
<form action="<?php echo Route::_('index.php?option=com_splms&layout=edit&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="adminForm" class="form-validate" enctype="multipart/form-data">
  <div class="form-horizontal">

    <div class="<?php echo $rowClass;?>">
      <div class="<?php echo $colClass;?>9">
        <?php 
          // Campos antes do toolbar
          echo $this->form->renderField('title');
          echo $this->form->renderField('alias');
          echo $this->form->renderField('short_description'); 

          // === Toolbar AI ===
          
          // AI: Generate (Crie a descrição / Custom / PDF)
          if (Factory::getUser()->authorise('ai.generate', 'com_splms')) {
              include JPATH_COMPONENT_ADMINISTRATOR . '/views/lesson/tmpl/toolbar_ai_chat.php';
          }

          // AI: Refine (Organize / Revisar / Resumir)
          if (Factory::getUser()->authorise('ai.refine', 'com_splms')) {
              include JPATH_COMPONENT_ADMINISTRATOR . '/views/lesson/tmpl/toolbar_ai.php';
          }

          // Editor de texto (TinyMCE)
          echo $this->form->renderField('description');

          // Demais campos do fieldset "basic"
          echo $this->form->renderField('video_url');
          echo $this->form->renderField('vdo_thumb');
          echo $this->form->renderField('video_duration');
          echo $this->form->renderField('attachment');
          echo $this->form->renderField('lesson_type');
          echo $this->form->renderField('lesson_format');
        ?>
      </div>

      <div class="<?php echo $colClass;?>3">
        <fieldset class="form-vertical">
          <?php echo $this->form->renderFieldset('sidebar'); ?>
        </fieldset>
      </div>
    </div>
  </div>

  <input type="hidden" name="task" value="lesson.edit" />
  <?php echo HTMLHelper::_('form.token'); ?>
</form>
