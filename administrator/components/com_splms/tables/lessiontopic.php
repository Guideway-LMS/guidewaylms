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
use Joomla\CMS\Date\Date;
use Joomla\CMS\Table\Table;

class SplmsTableLessiontopic extends Table {
	public function __construct(&$db) {
		parent::__construct('#__splms_lessiontopics', 'id', $db);
	}

	public function bind($src, $ignore = array()) {
		return parent::bind($src, $ignore);
	}

	public function store($updateNulls = false) {
		$user = Factory::getUser();
		$app  = Factory::getApplication();
		$date = new Date('now', $app->getCfg('offset'));

		if ($this->id) {
			$this->modified = (string)$date;
			$this->modified_by = $user->get('id');
		}

		if (empty($this->created)) {
			$this->created = (string) $date;
		}

		if (empty($this->created_by)) {
			$this->created_by = $user->get('id');
		}

		if (empty($this->modified)) {
			$this->modified = (string)$date;
		}

		if (empty($this->modified_by)) {
			$this->modified_by = $user->get('id');
		}

		$table = Table::getInstance('Lessiontopic','SplmsTable');
		
		return parent::store($updateNulls);
	}

	public function check() {
		if (trim($this->title) == '') {
			throw new UnexpectedValueException(sprintf('The title is empty'));
		}
        return true;
    }
}
	
