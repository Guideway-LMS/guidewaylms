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
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Component\ComponentHelper;

class SplmsViewEvents extends HtmlView{
	
	protected $items;
	protected $params;
	protected $layout_type;

	function display($tpl = null) {
		// Assign data to the view
		$this->model = $this->getModel();
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

		// Import Joomla component helper
		// Get Thumb Size
		$cParams 				= ComponentHelper::getParams('com_splms');
		$this->events_filter 	= $cParams->get('events_filter', false);
		$thumb_size 			= strtolower($cParams->get('event_thumbnail', '480X300'));

		//events months
		$this->months = array('1','2','3','4','5','6','7','8','9','10','11','12');

		foreach ($this->items as &$this->item) {
			// legacy compatibility
			$this->item->splms_speaker_id = $this->item->speaker_id;
			
			$this->item->url = new Uri(Route::_('index.php?option=com_splms&view=event&id=' . $this->item->id . ':' . $this->item->alias . '&Itemid=' . $menu->id, false));

			$filename = basename($this->item->image);
			$path = JPATH_BASE .'/'. dirname($this->item->image) . '/thumbs/' . File::stripExt($filename) . '_' . $thumb_size . '.' . File::getExt($filename);
			$src = Uri::base(true) . '/' . dirname($this->item->image) . '/thumbs/' . File::stripExt($filename) . '_' . $thumb_size . '.' . File::getExt($filename);

			if(File::exists($path)) {
				$this->item->thumb = $src;
			} else {
				$this->item->thumb = $this->item->image;	
			}
		}

		//Generate Item Meta
        SplmsHelper::itemMeta();
		parent::display($tpl);
	}
}