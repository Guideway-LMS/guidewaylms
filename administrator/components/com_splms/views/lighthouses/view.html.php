<?php
/**
 * @package		Joomla.Administrator
 * @subpackage	com_splms
 * @author      JoomShaper <support@joomshaper.com>
 * @copyright   Copyright (c) 2010 - 2021 JoomShaper
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
 */


// No Direct Access
defined('_JEXEC') or die('Resticted Aceess');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
/**
 * View to list of Lighthouses.
 *
 * @since  1.0.0
 */
class SplmsViewLighthouses extends HtmlView
{
	/**
	 * An array of items
	 *
	 * @var  	array
	 * @since	1.0.0
	 */
	protected $items;

	/**
	 * The model state
	 *
	 * @var  	object
	 * @since	1.0.0
	 */
	protected $state;

	/**
	 * The pagination object
	 *
	 * @var  	JPagination
	 * @since	1.0.0
	 */
	protected $pagination;

	/**
	 * The model class
	 *
	 * @var		JModel
	 * @since	1.0.0
	 */
	protected $model;

	/**
	 * Form object for search filters
	 *
	 * @var  	JForm
	 * @since	1.0.0
	 */
	public $filterForm;

	/**
	 * The active search filters
	 *
	 * @var  	array
	 * @since	1.0.0
	 */
	public $activeFilters;

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise an Error object.
	 * @since	1.0.0
	 */
	public function display($tpl = null)
	{
		$this->state			= $this->get('State');
		SplmsHelper::addSubmenu('lighthouses');

		$this->addToolbar();
		$this->sidebar = JHtmlSidebar::render();

		return parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	protected function addToolbar()
	{
		$state	= $this->get('State');
		$canDo	= SplmsHelper::getActions('com_splms', 'component');
		$user	= Factory::getUser();
		$bar	= Toolbar::getInstance('toolbar');

		if ($canDo->get('core.admin'))
		{
			ToolbarHelper::custom(null, 'refresh lighthouse-fix', '', 'Fix', false, false);
		}

		if ($canDo->get('core.admin'))
		{
			ToolbarHelper::preferences('com_splms');
		}

		JHtmlSidebar::setAction('index.php?option=com_splms&view=lighthouses');
		ToolbarHelper::title(Text::_('Lighthouse'), '');
	}
}

