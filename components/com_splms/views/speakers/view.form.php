<?php

/**
 * @package     SP LMS
 *
 * @copyright   Copyright (C) 2010 - 2020 JoomShaper. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No Direct Access
defined ('_JEXEC') or die('Resticted Aceess');

class SplmsViewSpeakers extends FOFViewForm {
	public function display($tpl = null){
		//get model
		$model = $this->getModel();
		//get items
		$this->items = $model->getItemList();

		foreach ($this->items as &$this->item) {
			//Generate URL
			$this->item->url = JRoute::_('index.php?option=com_splms&view=speaker&id='.$this->item->splms_speaker_id.':'.$this->item->slug . splmshelper::getItemid('speakers'));
			//Get Speaker Events
			$splms_speaker_id_encode= '%"'.$this->item->splms_speaker_id.'"%';
			$this->item->speaker_events = count($model->getSpeakerEvents($splms_speaker_id_encode));
			
		}


		return parent::display($tpl = null);
	}
}
