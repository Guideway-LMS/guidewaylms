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
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

class SplmsViewLessiontopics extends HtmlView {
	protected $items;
	protected $state;
	protected $pagination;
	protected $model;
	public $filterForm, $activeFilters;

	public function display($tpl = null) {
		$this->items			= $this->get('Items');
		$this->state			= $this->get('State');
		$this->pagination		= $this->get('Pagination');
		$this->model			= $this->getModel('lessiontopics');
		$this->filterForm		= $this->get('FilterForm');
		$this->activeFilters	= $this->get('ActiveFilters');

		$this->filter_order = $this->state->get('list.ordering');
		$this->filter_order_Dir = $this->state->get('list.direction');

		SplmsHelper::addSubmenu('lessiontopics');

		if (count($errors = $this->get('Errors'))) {
			throw new \Exception(implode('<br/>', $errors), 500);
			return false;
		}

		$this->addToolbar();
		$this->sidebar = JHtmlSidebar::render();
		return parent::display($tpl);
	}

	protected function addToolbar() {
		$state	= $this->get('State');
		$canDo	= SplmsHelper::getActions('com_splms','component');
		$user	= Factory::getUser();
		$bar	= Toolbar::getInstance('toolbar');

		if ($canDo->get('core.create')) {
			ToolbarHelper::addNew('lessiontopic.add');
		}

		if ($canDo->get('core.edit')) {
			ToolbarHelper::editList('lessiontopic.edit');
		}

		if ($canDo->get('core.edit.state')) {
			ToolbarHelper::publish('lessiontopics.publish','JTOOLBAR_PUBLISH',true);
			ToolbarHelper::unpublish('lessiontopics.unpublish','JTOOLBAR_UNPUBLISH',true);
			ToolbarHelper::archiveList('lessiontopics.archive');
			ToolbarHelper::checkin('lessiontopics.checkin');
		}

		if ($state->get('filter.published') == -2 && $canDo->get('core.delete')) {
			ToolbarHelper::deleteList('','lessiontopics.delete','JTOOLBAR_EMPTY_TRASH');
		} elseif ($canDo->get('core.edit.state')) {
			ToolbarHelper::trash('lessiontopics.trash');
		}

		if ($canDo->get('core.admin')) {
			ToolbarHelper::preferences('com_splms');
		}

		JHtmlSidebar::setAction('index.php?option=com_splms&view=lessiontopics');
		ToolbarHelper::title(Text::_('COM_SPLMS_PAGE_HEADING') .  Text::_('COM_SPLMS_LESSON_TOPICS'));
	}
}
	
