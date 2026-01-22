<?php
/**
 * @package     SP LMS
 * @subpackage  Components
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;

class SplmsViewForums extends HtmlView
{
	protected $items;
	protected $pagination;
	protected $state;
    protected $sidebar;

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{
		$this->items         = $this->get('Items');
		$this->pagination    = $this->get('Pagination');
		$this->state         = $this->get('State');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors), 500);
		}

		// Sidebar do SP LMS
		SplmsHelper::addSubmenu('forums');
		$this->sidebar = JHtmlSidebar::render();

		$this->addToolbar();

		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 */
	protected function addToolbar()
	{
		ToolbarHelper::title('Fórum de Dúvidas', 'comments');

        // Em J3/J5 Legacy custom, as vezes o ToolbarHelper exibe botoes padrao
		ToolbarHelper::deleteList('', 'forums.delete');
        
        // Preferencias
		ToolbarHelper::preferences('com_splms');
	}
}
