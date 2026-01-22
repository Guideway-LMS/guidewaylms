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
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

class SplmsViewLessiontopic extends HtmlView {
	protected $item;
	protected $form;

	public function display($tpl = null) {
		$this->item = $this->get('Item');
		$this->form = $this->get('Form');

		if (count($errors = $this->get('Errors'))) {
			throw new RuntimeException(implode("\n", $errors), 500);
			return false;
		}

		$this->addToolbar();
		return parent::display($tpl);
	}

	protected function addToolbar() {
		$input = Factory::getApplication()->input;
		$input->set('hidemainmenu',true);

		$user = Factory::getUser();
		$userId = $user->get('id');
		$isNew = $this->item->id == 0;
		$canDo = SplmsHelper::getActions('com_splms','component');

		ToolbarHelper::title(Text::_('COM_SPLMS_LESSON_TOPICS') . ': '.  ($isNew ? Text::_('COM_SPSPLMS_NEW') : Text::_('COM_SPSPLMS_EDIT')), 'pencil');

		if ($canDo->get('core.edit')) {
			ToolbarHelper::apply('lessiontopic.apply','JTOOLBAR_APPLY');
			ToolbarHelper::save('lessiontopic.save','JTOOLBAR_SAVE');
			ToolbarHelper::save2new('lessiontopic.save2new');
			ToolbarHelper::save2copy('lessiontopic.save2copy');
		}
		ToolbarHelper::cancel('lessiontopic.cancel','JTOOLBAR_CLOSE');
	}
}
	
