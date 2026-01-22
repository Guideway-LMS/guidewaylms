<?php

/**
 * @package com_splms
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2024 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
 */

// No Direct Access
defined('_JEXEC') or die('Resticted Aceess');

require_once JPATH_COMPONENT . '/assets/vendor/autoload.php';

use Stripe\Stripe;
use Razorpay\Api\Api;
use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Stripe\Exception\ApiErrorException;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\MVC\Controller\FormController;
use Stripe\Checkout\Session as StripeSession;
use Razorpay\Api\Errors\SignatureVerificationError;

class SplmsControllerPayment extends FormController
{
	/**
	 * Get the model.
	 *
	 * @param 	string $name
	 * @param 	string $prefix
	 * @param 	array $config
	 * @return 	BaseDatabaseModel the model.
	 * 
	 * @since 	1.0.0
	 */
	public function getModel($name = 'form', $prefix = '', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);
		return $model;
	}

	/**
	 * Success handler
	 *
	 * @return 	void
	 * @since 	1.0.0
	 */
	public function success()
	{
		$input = Factory::getApplication()->input;

		if ($input->get('st') == 'Completed') {
			echo LayoutHelper::render('payment', array('contents' => array('success', Text::_('COM_SPLMS_PAYMENT_SUCCESSFUL'))));
		} elseif ($input->get('st') == 'Pending') {
			echo LayoutHelper::render('payment', array('contents' => array('success', Text::_('COM_SPLMS_PAYMENT_PENDING'))));
		}
		$cookie  = Factory::getApplication()->input->cookie;
		$cookie->set('lmsOrders', null, time() - 1);
	}

	/**
	 * Payment cancel handler
	 *
	 * @return 	void
	 * @since 	1.0.0
	 */
	public function paymencancel()
	{
		$output = '<div class="alert alert-danger">';
		$output .= '<p>' . Text::_('COM_SPLMS_PAYMENT_ERROR') . '</p>';
		$output .= '</div>';
		echo $output;
		return;
	}

	public function notify()
	{
		$input = Factory::getApplication()->input;
		$params = ComponentHelper::getParams('com_splms');

		// Load Lessons model
		BaseDatabaseModel::addIncludePath(JPATH_SITE . '/components/com_splms/models');
		$model = BaseDatabaseModel::getInstance('Payment', 'SplmsModel');

		if ($input->get('txn_id') && $input->get('txn_type')) {

			$raw_post_data = file_get_contents('php://input');
			$raw_post_array = explode('&', $raw_post_data);
			$myPost = array();

			foreach ($raw_post_array as $keyval) {
				$keyval = explode('=', $keyval);
				if (count($keyval) == 2) {
					if ($keyval[0] === 'payment_date') {
						if (substr_count($keyval[1], '+') === 1) {
							$keyval[1] = str_replace('+', '%2B', $keyval[1]);
						}
					}
					$myPost[$keyval[0]] = urldecode($keyval[1]);
				}
			}

			$req = 'cmd=_notify-validate';
			$get_magic_quotes_exists = false;

			if (function_exists('get_magic_quotes_gpc')) {
				$get_magic_quotes_exists = true;
			}

			foreach ($myPost as $key => $value) {
				if ($get_magic_quotes_exists == true && get_magic_quotes_gpc() == 1) {
					$value = urlencode(stripslashes($value));
				} else {
					$value = urlencode($value);
				}
				$req .= "&$key=$value";
			}

			$verify_url = ($params->get('shop_environment') == 'sandbox') ? 'https://ipnpb.sandbox.paypal.com/cgi-bin/webscr' : 'https://ipnpb.paypal.com/cgi-bin/webscr';

			$ch = curl_init($verify_url);
			curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
			curl_setopt($ch, CURLOPT_SSLVERSION, 6);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
			curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: Close'));
			$res = curl_exec($ch);

			if (!($res)) {

				$errno = curl_errno($ch);
				$errstr = curl_error($ch);
				Log::add("cURL error: [$errno] $errstr", Log::WARNING, 'com_splms');
				//curl_close($ch);
			}

			$info = curl_getinfo($ch);
			$http_code = $info['http_code'];
			if ($http_code != 200) {
				Log::add("PayPal responded with http code $http_code", Log::WARNING, 'com_splms');
			}
			curl_close($ch);


			if ($res == 'VERIFIED') {
				// check whether the payment_status is Completed
				// $dublicate_txn_id = $model->getDublicateTransaction($txn_id);
				if ($params->get('shop_environment') == 'sandbox') {
					$payment_conditions = $params->get('paypal_id') == $input->get('receiver_email', NULL, 'RAW');
				} else {
					$payment_conditions = ($input->get('payment_status') == 'Completed') && ($params->get('paypal_id') == $input->get('receiver_email', NULL, 'EMAIL'));
				}

				if ($payment_conditions) {

					for ($i = 1; $i <= $input->get('num_cart_items', 1); $i++) {
						$user_id = $input->get('custom');
						$user_info = Factory::getUser($user_id);
						$item_number = $input->get('item_number' . $i);
						$payment_amount = $input->get('mc_gross_' . $i);
						$payment_currency = $input->get('mc_currency');
						$txn_id = $input->get('txn_id');
						$invoice_id = $input->get('invoice');
						$payment_status = $input->get('payment_status', NULL, 'WORD');

						$orderinfo = array(
							'user_id' => $user_id,
							'payment_amount' => $payment_amount,
							'payment_currency' => $payment_currency,
							'txn_id' => $txn_id,
							'item_number' => $item_number,
							'invoice_id' => $invoice_id,
							'payment_status' => $payment_status
						);

						//Ordered Item						
						$orderItem			 =  array(new stdClass);
						$orderItem[0]->price =  $payment_amount;
						$orderItem[0]->title =	$input->get('item_name' . $i, '', 'RAW');

						$dublicate_item = $model->getDublicateTransaction('', $item_number, $user_id);
						if (!$dublicate_item) {
							$insertorder = $model->storeOrder($orderinfo);
							if ($insertorder && isset($user_info->email) && $user_info->email) {
								self::sendPurchasdeMail($orderItem, $user_info->email);
							}
						}
					}
				}
			} elseif (($input->get('payment_status') == 'Pending') || $input->get('payment_status') == 'Completed') {
				for ($i = 1; $i <= $input->get('num_cart_items', 1); $i++) {
					$user_id = $input->get('custom');
					$user_info = Factory::getUser($user_id);
					$item_number = $input->get('item_number' . $i);
					$payment_amount = $input->get('mc_gross_' . $i);
					$payment_currency = $input->get('mc_currency');
					$txn_id = $input->get('txn_id');
					$invoice_id = $input->get('invoice');
					$payment_status = $input->get('payment_status');

					$orderinfo = array(
						'user_id' => $user_id,
						'payment_amount' => $payment_amount,
						'payment_currency' => $payment_currency,
						'txn_id' => $txn_id,
						'item_number' => $item_number,
						'invoice_id' => $invoice_id,
						'payment_status' => $payment_status
					);

              		//Ordered Item
					$orderItem		 	 =	array(new stdClass);
					$orderItem[0]->price =  $payment_amount;
					$orderItem[0]->title =	$input->get('item_name' . $i, '', 'RAW');

					$dublicate_item = $model->getDublicateTransaction('', $item_number, $user_id);

					if (!$dublicate_item) {

						$insertorder = $model->storeOrder($orderinfo);
						if ($insertorder && isset($user_info->email) && $user_info->email) {
							self::sendPurchasdeMail($orderItem, $user_info->email);
						}
					}
				}
			} else { // End Verified
				if (($input->get('payment_status') == 'Refunded') || ($input->get('payment_status') == 'Reversed')) {
					$txn_id = $input->get('txn_id');
					$invoice_id = $input->get('invoice');
					$item_number = $input->get('item_number');

					$orderinfo = array(
						'txn_id' => $txn_id,
						'invoice_id' => $invoice_id,
						'item_number' => $item_number,
						'payment_status' => $payment_status
					);

					$model->refundOrder($orderinfo);
				}
			}
		}
	}

	/**
	 * Direct payment
	 *
	 * @return 	void
	 * @since 	1.0.0
	 */
	public function direactPayment()
	{
		$user = Factory::getUser();
		// Load Lessons model
		BaseDatabaseModel::addIncludePath(JPATH_SITE . '/components/com_splms/models');
		$model = BaseDatabaseModel::getInstance('Payment', 'SplmsModel');
		$cartModel = BaseDatabaseModel::getInstance('Cart', 'SplmsModel');

		//Get cookie
		$cookie  = Factory::getApplication()->input->cookie;
		$orderItems = $cartModel->getItems();
		
		if ($user->guest) {
			echo LayoutHelper::render('payment', array('contents' => array('warning', Text::_('COM_SPLMS_CART_LOGIN_TO_CHECKOUT'))));
		} elseif (isset($orderItems) && is_array($orderItems)) {
			if (count($orderItems)) {
				$model->insertOrders($orderItems);
				//clear cookie
				$cookie->set('lmsOrders', null, time() - 1);

				if (isset($user->email) && $user->email) {
					self::sendPurchasdeMail($orderItems, $user->email);
				}
				echo LayoutHelper::render('payment', array('contents' => array('success',Text::_('COM_SPLMS_DIRECT_ORDER_SUBMITED'))));
			} else {
				echo LayoutHelper::render('payment', array('contents' => array('warning',Text::_('COM_SPLMS_NO_ITEM_IN_CART'))));
			}
		} else {
			echo LayoutHelper::render('payment', array('contents' => array( 'warning',Text::_('COM_SPLMS_SOMETHING_WRONG'))));
		}
	}

	/**
	 * Purchange mail sender method.
	 *
	 * @param 	string $customer_mail
	 * @param 	mixed $item_orders
	 * @return 	void
	 * 
	 * @since 	1.0.0
	 */
	private function sendPurchasdeMail( $item_orders, $customer_mail = '' )
	{

		$order_courses	= array();
		$total_price	= 0;

		foreach ($item_orders as $key => $item_order) {
			$paymentPrice = $item_order->price;
			if (isset($item_order->sale_price) && (float) $item_order->sale_price > 0) {
				$paymentPrice = $item_order->sale_price;
			}

			$order_courses[$key] = $item_order->title;
			$total_price += $paymentPrice;
		}

		$course_name	= implode(', ', $order_courses);
		$total_price	= SplmsHelper::getPrice($total_price);
		

		$search_msg		= array('{courses}', '{total_price}');
		$replace_msg	= array($course_name, $total_price);

		$params				= ComponentHelper::getParams('com_splms');
		$notify_mail		= $params->get('notify_mail', '');
		$notify_name		= $params->get('notify_name', '');
		$confirmation_txt 	= $params->get('confirmation_txt', '');
		$subject 			= Text::_('COM_SPLMS_COURSE_ENROLLED') . $course_name;
		$message 			= '<p>' . str_ireplace($search_msg, $replace_msg, nl2br($confirmation_txt)) . '</p>';

		$mail = Factory::getMailer();
		$sender = array($notify_mail, $notify_name);
		$mail->setSender($sender);
		$mail->addRecipient($customer_mail);
		$mail->addCC($notify_mail);
		$mail->setSubject($subject);
		$mail->isHTML(true);
		$mail->Encoding = 'base64';
		$mail->setBody($message);
		$mail->Send();
	}

	/**
	 * Stripe Success Handler
	 *
	 * @return 	void
	 * 
	 * @since 	4.0.5
	 */
	public function stripeSuccess()
	{
		$input 		= Factory::getApplication()->input;
		$sessionId 	= $input->get('session_id', null);

		if (null === $sessionId) {
			die('Invalid');
		}

		$user 		= Factory::getUser();
		$cartModel 	= BaseDatabaseModel::getInstance('Cart', 'SplmsModel');
		$cookie  	= Factory::getApplication()->input->cookie;
		$orderItems = $cartModel->getItems();

		try {
			$app 	= Factory::getApplication('site');
			$params = $app->getParams('com_splms');
			$apiKey = $params->get('stripe_api_key', null);

			Stripe::setApiKey($apiKey);
			$checkoutData = StripeSession::retrieve($sessionId);
	
			if ('complete' === $checkoutData->status && 'paid' === $checkoutData->payment_status) {
				$model 		= BaseDatabaseModel::getInstance('Payment', 'SplmsModel');
				$orderId 	= $checkoutData->metadata->order_id;

				// update the order as PAID
				$updateData = [
					'published' => $model::BILL_PAID,
					'payment_note' => 'Stripe Payment ID: ' . $checkoutData->payment_intent
				];

				$model->updateOrder($orderId, $updateData);

				// send email on payment verify
				if (isset($user->email)) {
					self::sendPurchasdeMail($orderItems, $user->email);
				}

				$cookie->set('lmsOrders', '', time() - 1);

				echo LayoutHelper::render('payment', array('contents' => array('success', Text::_('COM_SPLMS_PAYMENT_SUCCESSFUL'))));

			
			} else {
				echo LayoutHelper::render('payment', array('contents' => array('success', Text::_('COM_SPLMS_PAYMENT_PENDING'))));
			}
		} catch (ApiErrorException $e) {
			Log::add($e->getMessage(), Log::ERROR, 'com_splms');
			echo LayoutHelper::render('payment', array('contents' => array('error', Text::_('COM_SPLMS_PAYMENT_ERROR'))));
		}
	}

	/**
	 * Stripe payment
	 *
	 * @return 	void
	 * 
	 * @since 	4.0.5
	 */
	public function stripePayment()
	{
		$user 		= Factory::getUser();
		$model 		= BaseDatabaseModel::getInstance('Payment', 'SplmsModel');
		$cartModel 	= BaseDatabaseModel::getInstance('Cart', 'SplmsModel');
		$orderItems = $cartModel->getItems();
		$app 		= Factory::getApplication('site');
		$params 	= $app->getParams('com_splms');
		$currency 	= explode(':', $params->get('currency', "USD:$"))[0];

		if ($user->guest) {
			echo LayoutHelper::render('payment', array('contents' => array('warning', Text::_('COM_SPLMS_CART_LOGIN_TO_CHECKOUT'))));
		}

		if (!isset($orderItems) || !is_array($orderItems)) {
			echo LayoutHelper::render('payment', array('contents' => array('warning', Text::_('COM_SPLMS_SOMETHING_WRONG'))));
		}

		if (0 === count($orderItems)) {
			echo LayoutHelper::render('payment', array('contents' => array('warning', Text::_('COM_SPLMS_NO_ITEM_IN_CART'))));
		}


		$orderId = $model->insertOrders($orderItems);

		// Make stripe checkout payload.
		$checkoutItems = [];
		foreach ($orderItems as $item) {

			$paymentPrice = $item->price;
			if ((float) $item->sale_price > 0) {
				$paymentPrice = $item->sale_price;
			}

			$checkoutItems[] = [
				'price_data' => [
					'currency' => $currency,
					'product_data' => [
						'name' => $item->title,
					],
					'unit_amount' => (float) $paymentPrice * 100, // Convert price amount to cent.
				],
				'quantity' => 1
			];
		}

		$successUrlParams = [
			'option' 	 => 'com_splms',
			'task'		 => 'payment.stripeSuccess',
			'session_id' => '{CHECKOUT_SESSION_ID}',
		];

		$payload = [
			'line_items' => $checkoutItems,
			'mode' => 'payment',
			'metadata' => [
				'order_id' => $orderId
			],
			'success_url' => Uri::base() . 'index.php?' . urldecode(http_build_query($successUrlParams)),
			'cancel_url' => Uri::base() . 'index.php?option=com_splms&task=payment.paymencancel',
		];
		//End checkout payload generation.


		$apiKey = $params->get('stripe_api_key', null);

		try {
			Stripe::setApiKey($apiKey);
			$checkoutSession = StripeSession::create($payload);
			Factory::getApplication()->redirect($checkoutSession->url);
		} catch (ApiErrorException $e) {
			Log::add($e->getMessage(), Log::ERROR, 'com_splms');
			echo LayoutHelper::render('payment', array('contents' => array('error', Text::_('COM_SPLMS_PAYMENT_ERROR'))));
		}
	}

	/**
	 * Razorpay payment
	 *
	 * @return void
	 * 
	 * @since 4.0.5
	 */
	public function razorpayPayment()
	{
		$user 		= Factory::getUser();
		$model 		= BaseDatabaseModel::getInstance('Payment', 'SplmsModel');
		$cartModel 	= BaseDatabaseModel::getInstance('Cart', 'SplmsModel');
		$orderItems = $cartModel->getItems();

		if ($user->guest) {
			echo LayoutHelper::render('payment', array('contents' => array('warning', Text::_('COM_SPLMS_CART_LOGIN_TO_CHECKOUT'))));
		}

		if (!isset($orderItems) || !is_array($orderItems)) {
			echo LayoutHelper::render('payment', array('contents' => array('warning', Text::_('COM_SPLMS_SOMETHING_WRONG'))));
		}

		if (0 === count($orderItems)) {
			echo LayoutHelper::render('payment', array('contents' => array('warning', Text::_('COM_SPLMS_NO_ITEM_IN_CART'))));
		}


		$orderId = $model->insertOrders($orderItems);

		$app		= Factory::getApplication('site');
		$params		= $app->getParams('com_splms');
		$keyId		= $params->get('razorpay_key_id', null);
		$keySecret	= $params->get('razorpay_key_secret', null);
		$storeName	= $params->get('razorpay_store_name', Text::_('COM_SPLMS_STORE_NAME'));
		$storeDesc	= $params->get('razorpay_store_desc', Text::_('COM_SPLMS_STORE_DESC'));
		$storeLogo	= Uri::base() . $params->get('razorpay_store_logo');
		$currency	= explode(':', $params->get('currency', "USD:$"))[0];

		$price = 0;
		foreach ($orderItems as $item) {
			$paymentPrice = $item->price;
			if ((float) $item->sale_price > 0) {
				$paymentPrice = $item->sale_price;
			}

			$price += $paymentPrice;
		}

		$api = new Api($keyId, $keySecret);
		$orderData = [
			'receipt'         => $orderId, //internal order ID
			'amount'          => $price * 100,
			'currency'        => $currency,
			'payment_capture' => 1
		];

		$razorpayOrder 	 = $api->order->create($orderData);
		$razorpayOrderId = $razorpayOrder['id'];
		$displayCurrency = $orderData['currency'];
		$displayAmount   = $price;

		$data = [
			"key"               => $keyId,
			"amount"            => $orderData['amount'],
			"name"              => $storeName,
			"description"       => $storeDesc,
			"image"             => $storeLogo,
			"prefill"           => [
				"name" 	=> $user->name,
				"email" => $user->email,
			],
			"theme" => [],
			"order_id"          => $razorpayOrderId,
		];

		if ($orderData['currency'] !== 'INR') {
			$data['display_currency']  = $displayCurrency;
			$data['display_amount']    = $displayAmount;
		}

		$session = Factory::getSession();
		$session->set('razorpay_order_id', $razorpayOrderId);

		ob_start();
		$json = json_encode($data);
		require_once(JPATH_COMPONENT_SITE . '/layouts/razorpay-payment-page.php');
		echo ob_get_clean();
	}

	/**
	 * Razorpay Payment verify
	 *
	 * @return void
	 * 
	 * @since 4.0.5
	 */
	public function razorpayVerify()
	{
		$session = Factory::getSession();
		if (!$session->has('razorpay_order_id')) {
			$this->paymencancel();
			die();
		}

		$input = Factory::getApplication()->input;

		$paymentId =  $input->get('razorpay_payment_id', null);
		$signature =  $input->get('razorpay_signature', null);

		if (empty($paymentId) || empty($signature)) {
			$this->paymencancel();
			die();
		}

		$app 		= Factory::getApplication('site');
		$user		= Factory::getUser();
		$params 	= $app->getParams('com_splms');
		$keyId 		= $params->get('razorpay_key_id', null);
		$keySecret 	= $params->get('razorpay_key_secret', null);

		$api = new Api($keyId, $keySecret);

		try {
			$cartModel 	= BaseDatabaseModel::getInstance('Cart', 'SplmsModel');
			$orderItems = $cartModel->getItems();

			$attributes = array(
				'razorpay_order_id' => $session->get('razorpay_order_id'),
				'razorpay_payment_id' => $paymentId,
				'razorpay_signature' => $signature
			);

			//check success
			$api->utility->verifyPaymentSignature($attributes);

			//fetch rayzorpay order data.
			$razorpayOrderData = $api->order->fetch($session->get('razorpay_order_id'));

			$model 		= BaseDatabaseModel::getInstance('Payment', 'SplmsModel');
			$orderId 	= $razorpayOrderData->receipt;
			$cookie  	= Factory::getApplication()->input->cookie;

			if ($razorpayOrderData->status === 'paid') {
				// update the order as PAID
				$updateData = [
					'published' => $model::BILL_PAID,
					'payment_note' => 'Razorpay Payment ID: ' . $paymentId
				];
				$model->updateOrder($orderId, $updateData);

				if (isset($user->email) && isset($user->email)) {
					self::sendPurchasdeMail($orderItems, $user->email);
				}
			}

			$session->clear('razorpay_order_id');
			$cookie->set('lmsOrders', null, time() - 1);

			if ($razorpayOrderData->status === 'paid') {
				echo LayoutHelper::render('payment', array('contents' => array('success', Text::_('COM_SPLMS_PAYMENT_SUCCESSFUL'))));
			} else {
				echo LayoutHelper::render('payment', array('contents' => array('success', Text::_('COM_SPLMS_PAYMENT_SUCCESSFUL'))));
			}
		} catch (SignatureVerificationError $e) {
			Log::add("Razorpay Error : " . $e->getMessage(), Log::WARNING, 'com_splms');
			$this->paymencancel();
			die();
		}
	}
}