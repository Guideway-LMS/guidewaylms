<?php
/**
 * @package     SP LMS
 *
 * @copyright   Copyright (C) 2010 - 2020 JoomShaper. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No Direct Access
defined ('_JEXEC') or die('Resticted Aceess');

class SplmsViewCertificates extends FOFViewForm {
	public function display($tpl = null){
		//get model
		$model = $this->getModel();
		//get items
		$this->items = $model->getItemList();

		foreach ($this->items as &$this->item) {
			
			$this->item->name = $model->getUser($this->item->userid)->name ;
			//Generate URL
			$this->item->url = JRoute::_('index.php?option=com_splms&view=certificate&id='.$this->item->splms_certificate_id.':'.$this->item->userid . splmshelper::getItemid('certificates'));
		}


		return parent::display($tpl = null);
	}
}
