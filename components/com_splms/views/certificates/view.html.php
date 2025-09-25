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
use Joomla\CMS\Log\Log;
use Joomla\CMS\Router\Route;
use Joomla\CMS\MVC\View\HtmlView;

class SplmsViewCertificates extends HtmlView
{
	protected $items;
	protected $params;
	protected $layout_type;
	function display($tpl = null) {
		// Assign data to the view
		$model = $this->getModel();
		$this->items = $this->get('items');
		$this->pagination	= $this->get('Pagination');

		$app = Factory::getApplication();
		$this->params = $app->getParams();
		$menus = Factory::getApplication()->getMenu();
		$menu = $menus->getActive();
		$this->layout_type = str_replace('_', '-', $this->params->get('layout_type', 'default'));
		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			Log::add(implode('<br />', $errors), Log::WARNING, 'jerror');
			return false;
		}

		foreach ($this->items as &$this->item) {
			$this->item->name = $model->getUser($this->item->userid)->name ;
			//Generate URL
			$this->item->url = Route::_('index.php?option=com_splms&view=certificate&id='.$this->item->id.':'.$this->item->userid . splmshelper::getItemid('certificates'));
		}
		
		//Generate Item Meta
        SplmsHelper::itemMeta();
		parent::display($tpl);
	}
	
}
