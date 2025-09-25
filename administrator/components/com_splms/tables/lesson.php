<?php
/**
 * @package com_splms
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2024 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Application\ApplicationHelper;

class SplmsTableLesson extends Table{

	public function __construct(&$db) {
		parent::__construct('#__splms_lessons', 'id', $db);
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

		if(empty($this->topic_id))
		{
			$this->topic_id = NULL;
		}
		if(empty($this->teacher_id))
		{
			$this->teacher_id = 0;
		}

		// Verify that the alias is unique
		$table = Table::getInstance('Lesson', 'SplmsTable');

		if ($table->load(array('alias' => $this->alias)) && ($table->id != $this->id || $this->id == 0)) {
			$this->setError(Text::_('COM_SPLMS_ERROR_UNIQUE_ALIAS'));

			return false;
		}

		return parent::store($updateNulls);
	}

	public function check() {

		// Check for valid name.
		if (trim($this->title) == '') {
			throw new UnexpectedValueException(sprintf('The title is empty'));
		}

		if (empty($this->alias)) {
			$this->alias = $this->title;
		}

		if (empty($this->ordering)) {
			$this->ordering = 0;
		}

		$this->alias = ApplicationHelper::stringURLSafe($this->alias, $this->language);

		if (trim(str_replace('-', '', $this->alias)) == '') {
			$this->alias = Factory::getDate()->format('Y-m-d-H-i-s');
		}

		// Check for valid course id.
		if (trim($this->course_id) == '') {
			throw new UnexpectedValueException(sprintf('The lesson topic is empty'));
		}

		return true;
	}

}
