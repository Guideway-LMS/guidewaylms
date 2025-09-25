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
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

class SplmsViewCoursescategory extends HtmlView{

	protected $item;
	protected $params;
	protected $subcategoryEnabled;
	protected $subcategory_items;

	function display($tpl = null) {
		// Assign data to the view
		$this->item = $this->get('Item');
		$app = Factory::getApplication();
		$this->params = $app->getParams();
		$menus = Factory::getApplication()->getMenu();
		$menu = $menus->getActive();

		//get Component Params
		$this->params = ComponentHelper::getParams('com_splms');

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			throw new \Exception(implode("\n", $errors), 500);
			return false;
		}

		// Load Lessons model
		BaseDatabaseModel::addIncludePath(JPATH_SITE.'/components/com_splms/models');
		// Load courses & lesson Model
		$model = BaseDatabaseModel::getInstance( 'Coursescategories', 'SplmsModel' );
		$courses_model = BaseDatabaseModel::getInstance('Courses', 'SplmsModel');
		$lessons_model = BaseDatabaseModel::getInstance( 'Lessons', 'SplmsModel' );

		// Add Script
		$doc = Factory::getDocument();
		$doc->addScript( Uri::root(true) . '/components/com_splms/assets/js/matchheight.js' );

		//Get Currency
		$currency = explode(':', $this->params->get('currency', 'USD:$'));
		$this->currency =  $currency[1];

		// Get image thumb
		$this->thumb_size = strtolower($this->params->get('course_thumbnail', '480X300'));

		// Check whether subcategory option is enabled or not
		$this->subcategoryEnabled = (int) $this->params->get('subcategory_enabled') === 1 ? 1 : 0;

		// Get Sub Category
		$this->subcategory_items = ($this->subcategoryEnabled) ? $model->getSubcategories($this->item->id) : '';
		
		$subacategories_id = array();
		if ($this->subcategory_items) {
			foreach ($this->subcategory_items as $key => $value) {
				array_push($subacategories_id, $value->id);
			}
		}
		
		// Get courses by Category
		$this->category_items = $model->getCoursesByCategory($this->item->id, $subacategories_id);

		if (!count($this->category_items)) {
			echo '<p class="alert alert-warning">' . Text::_('COM_SPLMS_NO_ITEMS_FOUND') . '</p>';
			return;
		}

		foreach ($this->category_items as &$this->category_item) {
			// Generate URL
			$this->category_item->url = Route::_('index.php?option=com_splms&view=course&id=' . $this->category_item->id . ':' . $this->category_item->alias . SplmsHelper::getItemid('course')); 
			// Get course teachers
			$this->category_item->teachers = $courses_model->getCourseTeachers( $this->category_item->id );
			// Get course lessons
			$this->category_item->lessons  = $lessons_model->getLessons( $this->category_item->id );
			// Count course lesosns Attachments
			$this->category_item->total_attachments = array();
			foreach ($this->category_item->lessons as $lesson) {
				if ($lesson->attachment) {
					$this->category_item->total_attachments[] = $lesson->attachment;
				}
			}
			// Get category alias for shuffling
			if ($this->category_item->coursecategory_id) {
				$this->category_item->coursecategory_alias = $model->getCourseCategoryInfo('alias', $this->category_item->coursecategory_id);
			}
			// Get Prices
			if ($this->category_item->price == 0) {
				$this->category_item->c_price = Text::_('COM_SPLMS_FREE');
			} else {
				$this->category_item->c_price = SplmsHelper::getPrice($this->category_item->price, $this->category_item->sale_price);
			}
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