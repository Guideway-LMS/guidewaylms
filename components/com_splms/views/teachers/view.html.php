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
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Uri\Uri;

class SplmsViewTeachers extends HtmlView{

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

		// filter
		if($this->params->get('teachers_filter', 1)) {
			$this->skills = $this->get('skills');
		}

		// Load Lessons model
		BaseDatabaseModel::addIncludePath(JPATH_SITE.'/components/com_splms/models');
		$lessons_model = BaseDatabaseModel::getInstance( 'Lessons', 'SplmsModel' );

		foreach ($this->items as $this->item) {
			$itemId = $menu->id;

			// Generate URL
			$this->item->url = new Uri(Route::_('index.php?option=com_splms&view=teacher&id='.$this->item->id.':'.$this->item->alias . '&Itemid=' . $itemId, false));

			//Get Teachers Lessons
			$this->item->teacher_total_lessons= count($lessons_model->getTeacherLessons($this->item->id));

			$specialistist_decodes = json_decode($this->item->specialist_in);

			if (isset($specialistist_decodes) && is_object($specialistist_decodes)) {
				$specialists = array();

				foreach ($specialistist_decodes as $key => $specialistist_decode) {
					$specialists[] = $specialistist_decode->specialist_text;
				}

				$this->item->specialist_in = implode(', ', $specialists);
			}
		}

		//Generate Item Meta
        SplmsHelper::itemMeta();

		parent::display($tpl);
	}

}
