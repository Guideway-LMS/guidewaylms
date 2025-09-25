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
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

class SplmsViewCertificate extends HtmlView
{
	protected $item;
	protected $params;

	function display($tpl = null) {
		// Assign data to the view
		$this->item   = $this->get('Item');
		$app 		  = Factory::getApplication();
		$this->params = $app->getParams();
		$menus 		  = Factory::getApplication()->getMenu();
		$menu 		  = $menus->getActive();

		//Joomla Component Helper & Get LMS Params
		$lmsparams = ComponentHelper::getParams('com_splms');

		// Load Lessons model
		BaseDatabaseModel::addIncludePath(JPATH_SITE.'/components/com_splms/models');

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			throw new \Exception(implode("\n", $errors), 500);
			return false;
		}

		if (is_null($this->item)) {
			echo '<p class="alert alert-danger">' . Text::_('COM_SPLMS_ERROR_ITEM_NOT_FOUND') . '</p>';
			return false;	
		}


		// Load models
		$model = BaseDatabaseModel::getInstance( 'certificates', 'SplmsModel' );
		$courses_model = BaseDatabaseModel::getInstance('Courses', 'SplmsModel');

		// Get Course Category Name
		$this->item->category 		= $model->getCourseCategory($this->item->coursescategory_id)->title;
		// Get Course Name
		$this->item->course 		= $courses_model->getCourse($this->item->course_id)->title;
		//Certificate Logo
		$this->item->logo 			= $lmsparams->get('certificate_logo');
		//certificate no
		$this->item->prefix 		= $lmsparams->get('certificate_prefix', 'Joomshaper');
		// Get Organizer
		$this->item->organization 	= $lmsparams->get('organization', 'Joomshaper');
		// Student info
		$this->item->student_info 	= $model->getUser($this->item->userid);

		$student_lmsimage 			= ( $this->item->student_info && isset($this->item->student_info->profile_value->avatar) ) ? Uri::root() . json_decode($this->item->student_info->profile_value)->avatar : '' ;

		//** Student image **//
		//if inserted from lms
		if (isset($this->item->student_image) &&  $this->item->student_image) {
			$this->item->student_image = Uri::root() . $this->item->student_image;
		}
		// else has lms user profile
		elseif ($student_lmsimage) {
			$this->item->student_image = $student_lmsimage;
		}
		// else taking from gravatar
		else{
			$student_email = 'email@email.com';
			if (isset($this->item->student_info) && $this->item->student_info) {
				$student_email = $this->item->student_info->email;
			}
			// Generating Hash (Removing Spaces (Make all Lower Case))
			$this->item->student_image = 'http://www.gravatar.com/avatar/' . md5(strtolower(trim($student_email))) . '?s=120';
		}

		//Generate Item Meta
		$itemMeta               = array();
		$itemMeta['title']      = (isset($this->item->student_info->name) && $this->item->student_info->name) ? $this->item->student_info->name : '' ;
		$cleanText              = (isset($this->item->course) && $this->item->course) ? $this->item->course : '';
		$itemMeta['metadesc']   = HTMLHelper::_('string.truncate', OutputFilter::cleanText($cleanText), 155);
		if (isset($this->item->student_image) && $this->item->student_image) {
			$itemMeta['image']      = $this->item->student_image;
		}

		SplmsHelper::itemMeta($itemMeta);
			
		parent::display($tpl);
	}
}