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
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;

class SplmsViewTeacher extends HtmlView{
	
	protected $item;
	protected $params;

	function display($tpl = null) {
		// Assign data to the view
		$app 			= Factory::getApplication();
		$this->item 	= $this->get('Item');
		$this->params 	= $app->getParams();
		$this->review 	= $this->params->get('review', false);
		$menus = Factory::getApplication()->getMenu();
		$menu = $menus->getActive();

		//get Component Params
		$this->params = ComponentHelper::getParams('com_splms');

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			throw new \Exception(implode("\n", $errors), 500);
			return false;
		}

		//Generate Item Meta
        $itemMeta               = array();
        $itemMeta['title']      = $this->item->title;
        $cleanText              = $this->item->description;
        $itemMeta['metadesc']   = HTMLHelper::_('string.truncate', OutputFilter::cleanText($cleanText), 155);
        if ($this->item->image) {
        	$itemMeta['image']      = Uri::base() . $this->item->image;
        }
        
        SplmsHelper::itemMeta($itemMeta);
		parent::display($tpl);
	}
	
}
