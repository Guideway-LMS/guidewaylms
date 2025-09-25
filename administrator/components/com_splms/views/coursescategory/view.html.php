<?php
/**
* @package com_splms
* @author JoomShaper http://www.joomshaper.com
* @copyright Copyright (c) 2010 - 2024 JoomShaper
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

class SplmsViewCoursescategory extends HtmlView {

	protected $form;
	protected $item;
	protected $canDo;
	protected $id;
	protected $params;

	public function display($tpl = null) {
		// Get the Data
		$model = $this->getModel('Coursescategory');
		$this->form = $this->get('Form');
		$this->item = $this->get('Item');
		$this->id = $this->item->id;

		$this->canDo = SplmsHelper::getActions($this->item->id);
		$this->params = ComponentHelper::getParams('com_splms');

		// Check for errors.
		if (count($errors = $this->get('Errors'))){
			throw new \Exception(implode('<br/>', $errors), 500);
			return false;
		}

		$this->addToolBar();
		parent::display($tpl);
	}

	protected function addToolBar() {
		$input = Factory::getApplication()->input;

		// Hide Joomla Administrator Main menu
		$input->set('hidemainmenu', true);

		$isNew = ($this->item->id == 0);

		ToolbarHelper::title(Text::_('COM_SPLMS_COURSE_CATEGORIES') . ': '.  ($isNew ? Text::_('COM_SPSPLMS_NEW') : Text::_('COM_SPSPLMS_EDIT')), 'pencil');

		if ($isNew) {
			// For new records, check the create permission.
			if ($this->canDo->get('core.create')) {
				ToolbarHelper::apply('coursescategory.apply', 'JTOOLBAR_APPLY');
				ToolbarHelper::save('coursescategory.save', 'JTOOLBAR_SAVE');
			}
			ToolbarHelper::cancel('coursescategory.cancel', 'JTOOLBAR_CANCEL');
		} else {
			if ($this->canDo->get('core.edit')) {
				// We can save the new record
				ToolbarHelper::apply('coursescategory.apply', 'JTOOLBAR_APPLY');
				ToolbarHelper::save('coursescategory.save', 'JTOOLBAR_SAVE');
			}
			ToolbarHelper::cancel('coursescategory.cancel', 'JTOOLBAR_CLOSE');
		}
	}
}
