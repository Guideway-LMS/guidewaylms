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
use Joomla\CMS\Language\Text;
use Joomla\CMS\Application\ApplicationHelper;

class SplmsTableTeacher extends Table{

	public function __construct(&$db) {
		parent::__construct('#__splms_teachers', 'id', $db);
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

		// Verify that the alias is unique
		$table = Table::getInstance('Teacher', 'SplmsTable');

		if ($table->load(array('alias' => $this->alias)) && ($table->id != $this->id || $this->id == 0)) {
			$this->setError(Text::_('COM_SPLMS_ERROR_UNIQUE_ALIAS'));

			return false;
		}

		return parent::store($updateNulls);
	}

	public function check(){
		// Check for valid name.
		if (trim($this->title) == '') {
			throw new UnexpectedValueException(sprintf('The title is empty'));
		}

		if (empty($this->alias)) {
			$this->alias = $this->title;
		}

		$this->alias = ApplicationHelper::stringURLSafe($this->alias, $this->language);

		if (trim(str_replace('-', '', $this->alias)) == '') {
			$this->alias = date('Y-m-d-H-i-s');
		}

		// Education
		if (is_array($this->education) && !empty($this->education)){
			$this->education = json_encode($this->education);
		}
		if (is_null($this->education) || empty($this->education)){
			$this->education = '';
		}

		//specialist_in
		if (is_array($this->specialist_in)){
			if (!empty($this->specialist_in)){
				$this->specialist_in = json_encode($this->specialist_in);
			}
		}
		if (is_null($this->specialist_in) || empty($this->specialist_in)){
			$this->specialist_in = '';
		}

		return true;
	}
	
}
