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
use Joomla\CMS\MVC\Controller\FormController;

class SplmsControllerCourse extends FormController {

	public function __construct($config = array()) {
		parent::__construct($config);
	}

	protected function allowAdd($data = array()) {
		return parent::allowAdd($data);
	}

	protected function allowEdit($data = array(), $key = 'id') {
		$recordId = (int) isset($data[$key]) ? $data[$key] : 0;
		$user = Factory::getUser();
		// Zero record (id:0), return component edit permission by calling parent controller method
		if (!$recordId) {
			return parent::allowEdit($data, $key);
		}
		// Check edit on the record asset (explicit or inherited)
		if ($user->authorise('core.edit', 'com_splms.course.' . $recordId)) {
			return true;
		}
		// Check edit own on the record asset (explicit or inherited)
		if ($user->authorise('core.edit.own', 'com_splms.course.' . $recordId)){
			// Existing record already has an owner, get it
			$record = $this->getModel()->getItem($recordId);
			if (empty($record)) {
				return false;
			}
			// Grant if current user is owner of the record
			return $user->id == $record->created_by;
		} 
		return false;
	}
	
}
