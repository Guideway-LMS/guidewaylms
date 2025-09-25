<?php
/**
 * @package     SP LMS
 *
 * @copyright   Copyright (C) 2010 - 2021 JoomShaper. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No Direct Access
defined ('_JEXEC') or die('Resticted Aceess');

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\FormController;

class SplmsControllerSpeakers extends FormController{

	public function __construct($config = array()){
		parent::__construct($config);
	}

	public function onBeforeBrowse(){
		$app		= Factory::getApplication();
		$params		= $app->getParams();

		$this->getThisModel()->limit( $params->get('limit', 6) );
		$this->getThisModel()->limitstart($this->input->getInt('limitstart', 0));
		return true;
	}

}