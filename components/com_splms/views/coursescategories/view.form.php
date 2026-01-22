<?php

/**
 * @package     SP LMS
 *
 * @copyright   Copyright (C) 2010 - 2020 JoomShaper. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No Direct Access
defined ('_JEXEC') or die('Resticted Aceess');

class SplmsViewCoursescategories extends FOFViewForm{

	public function display($tpl = null){
		// Get model
		$model = $this->getModel();
		$this->items = $model->getItemList();


		foreach ($this->items as &$this->item) {

			// Get courses from categories
			$this->item->courses = count( $model->getCoursesByCategory( $this->item->splms_coursescategory_id ) );

			//URL Generate
			$this->item->url = JRoute::_('index.php?option=com_splms&view=coursescategory&id=' . $this->item->splms_coursescategory_id . ':' . $this->item->slug . SplmsHelper::getItemid('coursescategory')); 


		}
		
		return parent::display($tpl = null);
	}

}