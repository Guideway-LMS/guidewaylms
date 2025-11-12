<?php

/**
 * @package com_splms
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2024 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
 */

// No Direct Access
defined('_JEXEC') or die('Resticted Aceess');

use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

class SplmsViewCourses extends HtmlView
{

	protected $items;
	protected $params;
	protected $layout_type;

	function display($tpl = null)
	{
		// Assign data to the view
		$model = $this->getModel();
		$this->items = $this->get('items');
		$this->pagination	= $this->get('Pagination');

		$app = Factory::getApplication();
		$this->params = $app->getParams();
		$menus = Factory::getApplication()->getMenu();
		$menu = $menus->getActive();
		$this->menu_params = $menu->getParams();

		// Load Lessons model
		BaseDatabaseModel::addIncludePath(JPATH_SITE . '/components/com_splms/models');
		$lessons_model = BaseDatabaseModel::getInstance('Lessons', 'SplmsModel');


		// Loaad jQuary framework
		HTMLHelper::_('jquery.framework');

		// Add Script
		$doc = Factory::getDocument();
		$doc->addScript(Uri::root(true) . '/components/com_splms/assets/js/matchheight.js');
		$this->show_discount 			= $this->params->get('show_discount', 1);
		$this->course_filter_position   = $this->params->get('course_filter_position');
		$this->subcategory_enable 		= $this->params->get('show_subcategory', '');
		$this->category_id 				= Factory::getApplication()->input->getInt('category');
		// Get Data for filter
	
		if ($this->course_filter_position === 'top' && $this->category_id && $this->subcategory_enable == 1) {
		
			$this->course_categories 	= SplmsHelper::getCourseCategories($limit = NULL, $featured = NULL, $hideEmpty = true, $this->category_id);
		} else {
			$this->course_categories 	= SplmsHelper::getCourseCategories($limit = NULL, $featured = NULL, $hideEmpty = true);
		}
		$this->course_lavels 		= SplmsHelper::getCourseLavels();
		$this->filter_course_types 	= array(
			'paid' 					=> Text::_('COM_SPLMS_FILTER_PRICE_PAID'),
			'featured' 				=> Text::_('COM_SPLMS_FILTER_TYPE_FEATURED'),
			'free' 					=> Text::_('COM_SPLMS_FILTER_PRICE_FREE'),
		);

		// Get Currency
		$this->currency = explode(':', $this->params->get('currency', 'USD:$'));
		$this->currency =  $this->currency[1];

		// Get Coumn
		$this->columns = $menu->getParams()->get('columns', '2');

		// Get image thumb
		$this->thumb_size = strtolower($this->params->get('course_thumbnail', '480X300'));

		foreach ($this->items as &$this->item) {

			// legacy compatibility
			$this->item->splms_coursescategory_id = $this->item->coursecategory_id;
			$this->item->splms_course_id 		  = $this->item->id;

			// Get course teachers
			$this->item->teachers = $model->getCourseTeachers($this->item->id);
			// Get course lessons
			$this->item->lessons  = $lessons_model->getLessons($this->item->id);
			// Get category alias for shuffling
			$this->item->coursecategory_alias = $model->getCourseCategoryInfo('alias', $this->item->coursecategory_id);

			// Count course lesosns Attachments
			$this->item->total_attachments = array();
			foreach ($this->item->lessons as $lesson) {
				if ($lesson->attachment) {
					$this->item->total_attachments[] = $lesson->attachment;
				}
			} // END:: foreach

			// Get Prices
			if ($this->item->price == 0) {
				$this->item->course_price = Text::_('COM_SPLMS_FREE');
			} else {
				$this->item->course_price = SplmsHelper::getPrice($this->item->price, $this->item->sale_price);
			}

			// image thumb size
			$filename = basename($this->item->image);
			$path = JPATH_BASE . '/' . dirname($this->item->image) . '/thumbs/' . File::stripExt($filename) . '_' . $this->thumb_size . '.' . File::getExt($filename);
			$src = Uri::base(true) . '/' . dirname($this->item->image) . '/thumbs/' . File::stripExt($filename) . '_' . $this->thumb_size . '.' . File::getExt($filename);

			if (File::exists($path)) {
				$this->item->thumb = $src;
			} else {
				$this->item->thumb = $this->item->image;
			}

			//URL Generate
			$this->item->url = Route::_('index.php?option=com_splms&view=course&id=' . $this->item->id . ':' . $this->item->alias);
		}

		$this->layout_type = str_replace('_', '-', $this->params->get('layout_type', 'default'));

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			Log::add(implode('<br />', $errors), Log::WARNING, 'jerror');
			return false;
		}

		SplmsHelper::itemMeta();
		parent::display($tpl);
	}
}
