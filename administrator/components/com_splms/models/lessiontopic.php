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
use Joomla\CMS\Table\Table;
use Joomla\CMS\MVC\Model\AdminModel;

class SplmsModelLessiontopic extends AdminModel {
	protected $text_prefix = 'COM_SPLMS';

	public function getTable($name = 'Lessiontopic', $prefix = 'SplmsTable', $config = array()) {
		return Table::getInstance($name, $prefix, $config);
	}

	public function getForm($data = array(), $loadData = true) {
		$app = Factory::getApplication();
		$form = $this->loadForm('com_splms.lessiontopic','lessiontopic',array('control' => 'jform', 'load_data' => $loadData));

		if (empty($form)) {
			return false;
		}
		return $form;
	}

	public function loadFormData() {
		$data = Factory::getApplication()
			->getUserState('com_splms.edit.lessiontopic.data',array());

		if (empty($data)) {
			$data = $this->getItem();
		}
		return $data;
	}

	protected function canDelete($record) {
		if (!empty($record->id)) {
			if ($record->published != -2) {
				return ;
			}
			$user = Factory::getUser();
			return parent::canDelete($record);
		}
	}

	protected function canEditState($record) {
		return parent::canEditState($record);
	}

	public function getItem($pk = null) {
		return parent::getItem($pk);
	}

	public function save($data) {
		$input 	= Factory::getApplication()->input;
		$task 	= $input->get('task');

		if ($task == 'save2copy') {
			$originalTable = clone $this->getTable();
			$originalTable->load($input->getInt('id'));

			if ($data['title'] == $originalTable->title) {
				$data['title'] = $data['title'] . ' - Copy';
			}
			
			$data['published'] = 0;
		}
		if (parent::save($data))
			return true;
		return false;
	}
}
	
