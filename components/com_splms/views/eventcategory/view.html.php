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
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Uri\Uri;

class SplmsViewEventcategory extends HtmlView{

	protected $item;
	protected $params;

	function display($tpl = null) {
		// Assign data to the view
		$this->item = $this->get('Item');
		$app = Factory::getApplication();
		$this->params = $app->getParams();
		$menus = Factory::getApplication()->getMenu();
		$menu = $menus->getActive();

		// Import Joomla component helper
		//get Component Params
		$this->lmsParams = ComponentHelper::getParams('com_splms');

		// Load Lessons model
		BaseDatabaseModel::addIncludePath(JPATH_SITE . '/components/com_splms/models');

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			throw new \Exception(implode("\n", $errors), 500);
			return false;
		}

		// Load courses & lesson Model
		$model 		    = BaseDatabaseModel::getInstance( 'Eventcategories', 'SplmsModel' );
		$events_model 	= BaseDatabaseModel::getInstance( 'Events', 'SplmsModel' );

		//Get Event By Category ID
		$this->category_items = $events_model->getEventByCategory($this->item->id);
		// Get thumb size
		$this->thumb_size = strtolower($this->lmsParams->get('event_thumbnail', '480X300'));

		foreach ($this->category_items as $this->item) {
			$filename = basename($this->item->image);
			$path = JPATH_BASE .'/'. dirname($this->item->image) . '/thumbs/' . File::stripExt($filename) . '_' . $this->thumb_size . '.' . File::getExt($filename);
			$src = Uri::base(true) . '/' . dirname($this->item->image) . '/thumbs/' . File::stripExt($filename) . '_' . $this->thumb_size . '.' . File::getExt($filename);

			if(File::exists($path)) {
				$this->item->thumb = $src;
			} else {
				$this->item->thumb = $this->item->image;	
			}
		}

		//Generate Item Meta
        $itemMeta               = array();
        $itemMeta['title']      = $this->item->title;
        $cleanText              = $this->item->description;
        $itemMeta['metadesc']   = HTMLHelper::_('string.truncate', OutputFilter::cleanText($cleanText), 155);
        if ($this->item->image) {
        	$itemMeta['image']  = Uri::base() . $this->item->image;
        }
        SplmsHelper::itemMeta($itemMeta);
		parent::display($tpl);
	}


}