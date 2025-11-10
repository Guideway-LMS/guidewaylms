<?php
/**
* @package com_splms
* @author JoomShaper http://www.joomshaper.com
* @copyright Copyright (c) 2010 - 2024 JoomShaper
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Version;

$version = new Version();
$JoomlaVersion = $version->getShortVersion();

if (version_compare($JoomlaVersion, '4.0.0', '>='))
{
	JLoader::registerAlias('JHtmlSidebar', 'Joomla\CMS\HTML\Helpers\Sidebar');
}
class SplmsViewDashboard extends HtmlView {

	public function display($tpl = null) {

		$model = $this->getModel('Dashboard');
		$this->orders = $model->getOrders();
		$this->courses = $model->getCourses();
		$this->lessons = $model->getLessons();
		$this->total = $model->getTotalSales();
		$this->earnings = SplmsHelper::getPrice($this->total);
		$this->students = $model->getTotalStudent();
		$this->courseList = $model->getCourseList();
		$this->orderList = $model->getOrderList();
		$this->orderList = (!isset($this->orderList) && !count((array)$this->orderList)) ? array() : $this->orderList;
		
		$this->canDo = SplmsHelper::getActions();

		// has access visite this view
		SplmsHelper::hasVisitAccess();

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			throw new \Exception(implode('<br/>', $errors), 500);
			return false;
		}

		SplmsHelper::addSubmenu('dashboard');
		$this->addToolBar();
		$this->sidebar = JHtmlSidebar::render();
		parent::display($tpl);
	}

	protected function addToolBar() {
		ToolbarHelper::title(Text::_('COM_SPLMS_PAGE_HEADING') .  Text::_('COM_SPLMS_MANAGER_DASHBOARD'));

		if ($this->canDo->get('core.admin')) {
			ToolbarHelper::preferences('com_splms');
		}
	}

}
