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
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Table\Table;

class SplmsTableCertificate extends Table{

	public function __construct(&$db) {
		parent::__construct('#__splms_certificates', 'id', $db);
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
		if (empty($this->issue_date)) {
			$this->issue_date = null;
		}
		return parent::store($updateNulls);
	}

	public function check() {

		$today = Factory::getDate();
		$today = HTMLHelper::_('date', $today, 'Ymd');
		$rand = strtoupper(substr(uniqid(sha1(time())),0,4));
		$uniqueno = $rand .$today;

		if(empty($this->certificate_no)) {
			// Auto-fetch a alias
			$this->certificate_no = $uniqueno;
		} else {
			// if has certificate no
			$this->certificate_no = $this->certificate_no;
		}
		return true;
		//return parent::onAfterLoad($result);
	}

}
