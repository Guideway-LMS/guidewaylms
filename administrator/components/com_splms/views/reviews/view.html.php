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

class SplmsViewReviews extends HtmlView{

	protected $items;
	protected $pagination;
	protected $state;
	public $filterForm;
	public $activeFilters;
	protected $sidebar;

	function display($tpl = null){

		// Get application
		$app = Factory::getApplication();
		$context = "com_splms.reviews";

		// Get data from the model
		$this->items = $this->get('Items');
		$this->pagination = $this->get('Pagination');
		$this->state = $this->get('State');
		$this->filterForm = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');

		$this->canDo = SplmsHelper::getActions();

		$this->filter_order = $this->state->get('list.ordering');
		$this->filter_order_Dir = $this->state->get('list.direction');

		// Check for errors.
		if (count($errors = $this->get('Errors'))){
			throw new \Exception(implode('<br/>', $errors), 500);
			return false;
		}

		// has access visite this view
		SplmsHelper::hasVisitAccess();

		// Set the submenu
		SplmsHelper::addSubmenu('reviews');
		$this->addToolBar();
		$this->sidebar = JHtmlSidebar::render();

		return parent::display($tpl);
	}

	protected function addToolBar() {
		ToolbarHelper::title(Text::_('COM_SPLMS_PAGE_HEADING') .  Text::_('COM_SPLMS_REVIEWS'));

		if ($this->canDo->get('core.create')) {
			ToolbarHelper::addNew('review.add', 'JTOOLBAR_NEW');
		}
		if ($this->canDo->get('core.edit')) {
			ToolbarHelper::editList('review.edit', 'JTOOLBAR_EDIT');
		}

		if ($this->canDo->get('core.edit.state')) {
			ToolbarHelper::publish('reviews.publish', 'JTOOLBAR_PUBLISH', true);
			ToolbarHelper::unpublish('reviews.unpublish', 'JTOOLBAR_UNPUBLISH', true);
			ToolbarHelper::archiveList('reviews.archive');
			ToolbarHelper::checkin('reviews.checkin');
		}

		if ($this->state->get('filter.published') == -2 && $this->canDo->get('core.delete')) {
			ToolbarHelper::deleteList('', 'reviews.delete', 'JTOOLBAR_EMPTY_TRASH');
		} elseif ($this->canDo->get('core.edit.state') && $this->canDo->get('core.delete')) {
			ToolbarHelper::trash('reviews.trash');
		}

		if ($this->canDo->get('core.admin')) {
			ToolbarHelper::divider();
			ToolbarHelper::preferences('com_splms');
		}
	}
}
