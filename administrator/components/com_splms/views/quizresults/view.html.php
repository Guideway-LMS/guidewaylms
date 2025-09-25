<?php
/**
* @package com_splms
* @author JoomShaper http://www.joomshaper.com
* @copyright Copyright (c) 2010 - 2024 JoomShaper
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

// No Direct Access
defined ('_JEXEC') or die('Resticted Aceess');

class SplmsViewQuizresults extends HtmlView{

	protected $items;
	protected $pagination;
	protected $state;
	public $filterForm;
	public $activeFilters;
	protected $sidebar;

	function display($tpl = null){

		// Get application
		$app = Factory::getApplication();
		$context = "com_splms.quizresults";

		// Get data from the model
		$this->items = $this->get('Items');
		$this->pagination = $this->get('Pagination');
		$this->state = $this->get('State');
		$this->filter_order = $this->state->get('list.ordering');
		$this->filter_order_Dir = $this->state->get('list.direction');
		$this->filterForm = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');

		$this->canDo = SplmsHelper::getActions();

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			throw new \Exception(implode('<br/>', $errors), 500);
			return false;
		}

		// has access visite this view
		SplmsHelper::hasVisitAccess();

		// Set the submenu
		SplmsHelper::addSubmenu('quizresults');
		$this->addToolBar();
		$this->sidebar = JHtmlSidebar::render();

		return parent::display($tpl);

	}

	protected function addToolBar() {
		ToolbarHelper::title(Text::_('COM_SPLMS_PAGE_HEADING') .  Text::_('COM_SPLMS_TITLE_QUIZRESULTS'));

		if ($this->canDo->get('core.create')) {
			ToolbarHelper::addNew('quizresult.add', 'JTOOLBAR_NEW');
		}

		if ($this->canDo->get('core.edit')) {
			ToolbarHelper::editList('quizresult.edit', 'JTOOLBAR_EDIT');
		}

		if ($this->canDo->get('core.edit.state')) {
			ToolbarHelper::publish('quizresults.publish', 'JTOOLBAR_PUBLISH', true);
			ToolbarHelper::unpublish('quizresults.unpublish', 'JTOOLBAR_UNPUBLISH', true);
			ToolbarHelper::archiveList('quizresults.archive');
			ToolbarHelper::checkin('quizresults.checkin');
		}

		if ($this->state->get('filter.published') == -2 && $this->canDo->get('core.delete')) {
			ToolbarHelper::deleteList('', 'quizresults.delete', 'JTOOLBAR_EMPTY_TRASH');
		} elseif ($this->canDo->get('core.edit.state') && $this->canDo->get('core.delete')) {
			ToolbarHelper::trash('quizresults.trash');
		}

		if ($this->canDo->get('core.admin')) {
			ToolbarHelper::divider();
			ToolbarHelper::preferences('com_splms');
		}
		
	}
}
