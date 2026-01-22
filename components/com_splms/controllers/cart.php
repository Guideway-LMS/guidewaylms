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
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;

class SplmsControllerCart extends BaseController {
    
    public function add() {
		$cookie             = Factory::getApplication()->input->cookie;
		$input  			= Factory::getApplication()->input;
		$course 			= (int) $input->get('course', 0, 'INT');
		$courses 			= array();
		
		// Set cookie data
		$exist_orders = $cookie->get('lmsOrders', base64_encode(serialize(array())));
		$courses = (array)unserialize(base64_decode($exist_orders));
		
		if(!array_key_exists($course, $courses)) {
			$courses[$course]['product'] = $course;
		}

		$orders = array_map('unserialize', array_unique(array_map('serialize', $courses)));
		$cookie->set('lmsOrders', base64_encode(serialize($orders)), $expire = 0, Uri::base(true) );
		
		$output = array(
			'redirect'=>Route::_('index.php?option=com_splms&view=cart' . SplmsHelper::getItemid('cart')),
			'button_text'=>Text::_('COM_SPLMS_ORDER_CHECKOUT')
		);

		echo json_encode($output);
		die;
    }
    
    public function remove() {	
		$cookie             = Factory::getApplication()->input->cookie;
		$input  			= Factory::getApplication()->input;
		$course 			= (int) $input->get('course', 0, 'INT');
        $courses 			= array();
		
		// Set cookie data
		$exist_orders = $cookie->get('lmsOrders', base64_encode(serialize(array())));
		$exist_orders = unserialize(base64_decode($exist_orders));
		
		if(array_key_exists($course, $exist_orders)) {
			unset($exist_orders[$course]);
		}

		$orders = array_map('unserialize', array_unique(array_map('serialize', $exist_orders)));
		$cookie->set('lmsOrders', base64_encode(serialize($orders)), $expire = 0, Uri::base(true) );
		
		$output = array(
			'redirect'=>Route::_('index.php?option=com_splms&view=cart' . SplmsHelper::getItemid('cart'))
		);

		echo json_encode($output);
		die;
	}
}