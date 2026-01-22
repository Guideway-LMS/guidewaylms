<?php
/**
* @package com_splms
* @author JoomShaper http://www.joomshaper.com
* @copyright Copyright (c) 2010 - 2024 JoomShaper
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/

use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Component\ComponentHelper;

// No Direct Access
defined ('_JEXEC') or die('Resticted Aceess');

class SplmsViewCart extends HtmlView {

	public function onAdd($tpl = null){
		return $this->onDisplay();
	}

	protected $items;
	protected $params;
	protected $layout_type;

	function display($tpl = null) {

		$app = Factory::getApplication();
		$this->params = $app->getParams();
		$menus = Factory::getApplication()->getMenu();
		$menu = $menus->getActive();

		// Check for errors.
		if ($errors = $this->get('Errors') && count($errors = $this->get('Errors'))) {
			Log::add(implode('<br />', $errors), Log::WARNING, 'jerror');
			return false;
		}

		// Params
		$this->params = ComponentHelper::getParams('com_splms');
		$this->currency = explode(':', $this->params->get('currency', "USD:$"));
		$this->payment_method = $this->params->get('payment_method', array('direct'));
		$this->bank_info = $this->params->get('bank_info', '');

		// Get Cart model
		$model = $this->getModel('Cart', 'SplmsModel');
		
		$this->carts	= $model->getItems();

		$this->user 	= Factory::getUser();

		$this->notify_url		= Uri::base() . 'index.php?option=com_splms&task=payment.notify';
		$this->return_success	= Uri::base() . 'index.php?option=com_splms&task=payment.success';
		$this->return_cencel	= Uri::base() . 'index.php?option=com_splms&task=payment.paymencancel';

		$this->direct_payment	= Uri::base() . 'index.php?option=com_splms&task=payment.direactPayment&pm=direct';
		$this->bank_payment		= Uri::base() . 'index.php?option=com_splms&task=payment.direactPayment&pm=bank';
		$this->stripe_payment	= Uri::base() . 'index.php?option=com_splms&task=payment.stripePayment&pm=stripe';
		$this->razorpay_payment	= Uri::base() . 'index.php?option=com_splms&task=payment.razorpayPayment&pm=razorpay';

		$this->layout_type = str_replace('_', '-', $this->params->get('layout_type', 'default'));
		
		parent::display($tpl);
	}

}
