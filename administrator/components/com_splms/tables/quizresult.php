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

class SplmsTableQuizresult extends Table {

	public function __construct(&$db) {
		parent::__construct('#__splms_quizresults', 'id', $db);
	}

	public function store($updateNulls = false) {
		$date = Factory::getDate()->toSql();
		$user = Factory::getUser();

		if ($this->id) {
			$this->modified		= $date;
			$this->modified_by		= $user->get('id');
		} else {
			if (!(int) $this->created) {
				$this->created = $date;
			}
			if (empty($this->created_by)) {
				$this->created_by = $user->get('id');
			}
			if (!(int) $this->modified) {
				$this->modified = $date;
			}
			if (empty($this->modified_by)) {
				$this->modified_by = $user->get('id');
			}
		}
		return parent::store($updateNulls);
	}

}
