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
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

class SplmsViewPurchases extends HtmlView{


	protected $items;
	protected $params;
	protected $layout_type;

	function display($tpl = null) {
		// Assign data to the view
		$this->items = $this->get('items');
		$this->pagination	= $this->get('Pagination');

		$app = Factory::getApplication();
		$this->params = $app->getParams();
		$menus = Factory::getApplication()->getMenu();
		$menu = $menus->getActive();

		if($menu) {
			//$this->params->merge($menu->params);
		}

		$this->layout_type = str_replace('_', '-', $this->params->get('layout_type', 'default'));
		// Check for errors.
		if ($errors = $this->get('Errors') && count($errors = $this->get('Errors'))) {
			Log::add(implode('<br />', $errors), Log::WARNING, 'jerror');
			return false;
		}

		$user = Factory::getUser();
		// Load Lessons model
		BaseDatabaseModel::addIncludePath(JPATH_SITE.'/components/com_splms/models');
		
		//Loan Courses model
		$courses_model = BaseDatabaseModel::getInstance( 'Courses', 'SplmsModel' );
		// load quiz model
		$quiz_model = BaseDatabaseModel::getInstance( 'Quizquestions', 'SplmsModel' );
		// load certificate model
		$certificate_model = BaseDatabaseModel::getInstance( 'Certificates', 'SplmsModel' );

		if($user->guest) {
			echo '<p class="alert alert-danger">' . Text::_('COM_SPLMS_PURCHASED_QUIZ_LOGIN') . '</p>';
			return;	
		}

		$purchased_course = $courses_model->getPurchasedCourse( $user->id );
		if (empty($purchased_course)) {
			echo '<p class="alert alert-warning">' . Text::_('COM_SPMS_NO_EMPTY_PURCHASED') . '</p>';
		}

		$this->purchases = $purchased_course;
		// quiz result
		$this->quiz_results = $quiz_model->getQuizResult( $user->id );

		if (empty($this->quiz_results)) {
			echo '<p class="alert alert-warning">' . Text::_('COM_SPMS_EMPTY_QUIZ_RESULT') . '</p>';
		}

		// certificates
		$this->user_certificates = $certificate_model->getCerficateById( $user->id );
		if (empty($this->user_certificates)) {
			echo '<p class="alert alert-warning">' . Text::_('COM_SPMS_EMPTY_CERTIFICATE') . '</p>';
		}

		parent::display($tpl);
	}

}
