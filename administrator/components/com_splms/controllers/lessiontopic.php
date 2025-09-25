
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

class SplmsControllerLessiontopic extends FormController {
	public function __construct($config = array()) {
		parent::__construct($config);
	}

	protected function allowAdd($data = array()) {
		return parent::allowAdd($data);
	}

	protected function allowEdit($data = array(), $key = 'id') {
        $id = (int) isset($data[$key]) ? $data[$key] : 0;
		$user = Factory::getUser();

		if (!$id) {
			return parent::allowEdit($data, $key);
		}

		if ($user->authorise('core.edit', 'com_splms.lessiontopic.' . $id)) {
			return true;
		}

		if ($user->authorise('core.edit.own', 'com_splms.lessiontopic.' . $id)) {
			$record = $this->getModel()->getItem($id);
			if (empty($record)) {
				return false;
			}
			return $user->id === $record->created_by;
		}
		return false;
	}
}
	
