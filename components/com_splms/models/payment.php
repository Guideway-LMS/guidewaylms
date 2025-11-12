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
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\HTML\HTMLHelper;

class SplmsModelPayment extends ListModel {
	/**
	 * Constant value for bill paid.
	 */
	const BILL_PAID 	= 1;
	/**
	 * Constant value for bill pending.
	 */
	const BILL_PENDING 	= 0;

	/**
	 * Constructor
	 *
	 * @param array $config
	 */
	public function __construct($config = array()) {
    	parent::__construct($config);
	}

	/**
	 * Insert order for Paypal.
	 *
	 * @param 	array $order_info
	 * @return 	boolean
	 * 
	 * @since 	1.0.0
	 */
	public function storeOrder($order_info = array()){
		//$payment_status = ($order_info['payment_status'] == 'Completed') ? 1 : 0 ;

		$params = ComponentHelper::getParams('com_splms');
		if ($params->get('shop_environment') == 'sandbox') {
			$payment_status = 1;
		} else {
			$payment_status = ($order_info['payment_status'] == 'Completed') ? 1 : 0 ;
		}

		try
		{
			$db = Factory::getDbo();
			$query = $db->getQuery(true);


			$date = Factory::getDate()->toSql();
			$user = Factory::getUser();

			$modified       = $date;
			$modified_by    = $user->get('id');
			$created        = $date;
			$created_by     = $user->get('id');

			$columns = array('order_user_id', 'course_id', 'order_price', 'order_payment_price', 'order_payment_id', 'order_payment_method', 'invoice_id', 'published', 'order_payment_currency','modified','modified_by','created','created_by');
			$values  = array($db->quote($order_info['user_id']), $db->quote($order_info['item_number']), $db->quote($order_info['payment_amount']), $db->quote($order_info['payment_amount']), $db->quote($order_info['txn_id']), $db->quote('paypal'), $db->quote($order_info['invoice_id']), $payment_status, $db->quote($order_info['payment_currency']),$db->quote($modified),$db->quote($modified_by),$db->quote($created),$db->quote($created_by));
	
			$query
				->insert($db->quoteName('#__splms_orders'))
				->columns($db->quoteName($columns))
				->values(implode(',', $values));
	
			$db->setQuery($query);
			$db->execute();
		}
		catch(Exception $e)
		{
			echo 'Caught exception: ',  $e->getMessage(), "\n";
		}
		

		return true;
	}

	/**
	 * Paypal refund order.
	 *
	 * @param 	array $order_info
	 * @return 	void
	 * @since 	1.0.0
	 */
	public function refundOrder($order_info) {

		$db = Factory::getDbo();
		$query = $db->getQuery(true);

		$date = Factory::getDate()->toSql();
		$user = Factory::getUser();

		$modified       = $date;
		$modified_by    = $user->get('id');
		$created        = $date;
		$created_by     = $user->get('id');

		// Fields to update.
		$fields = array(
			$db->quoteName('published') . ' = 0'
		);
		// Conditions for which records should be updated.
		$conditions = array(
		    $db->quoteName('order_payment_id') . ' = ' . $db->quote($order_info['txn_id']),
		    $db->quoteName('invoice_id') . ' = '.$db->quote($order_info['invoice_id']),
		    $db->quoteName('course_id') . ' = ' .$db->quote($order_info['item_number'])
		);
		$query->update($db->quoteName('#__splms_orders'))->set($fields)->where($conditions);
	    $db->setQuery($query);
	    $db->execute();

	}

	/**
	 * Check Duplicate Order.
	 *
	 * @param string $transaction_id
	 * @param string $itemid
	 * @param string $user_id
	 * @return boolean
	 * 
	 * @since 1.0.0
	 */
	public function getDublicateTransaction($transaction_id = '', $itemid = '', $user_id = '') {
		
		$date = Factory::getDate()->toSql();
		$user = Factory::getUser();

		$modified       = $date;
		$modified_by    = $user->get('id');
		$created        = $date;
		$created_by     = $user->get('id');

		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select($db->quoteName(array('id', 'order_payment_id')));
		$query->from($db->quoteName('#__splms_orders'));
		$query->where($db->quoteName('published')." = 1");
		if($transaction_id){
		    $query->where($db->quoteName('order_payment_id')." = ".$db->quote($transaction_id));    
		}
		if($itemid){
		    $query->where($db->quoteName('course_id')." = ".$db->quote($itemid));
		}
		if($user_id){
		    $query->where($db->quoteName('order_user_id')." = ".$db->quote($user_id));
		}
		$query->order('ordering DESC');
		$db->setQuery($query);
		$result = $db->loadObject();

	 	if($result){
		    return true;   
		}
		
		return false;
	}

	/**
	 * Get payment price.
	 *
	 * @param 	mixed $itemObj
	 * @return 	string payment price.
	 * 
	 * @since 	4.0.5
	 */
	public function getPaymentPrice($itemObj){
		$paymentPrice = $itemObj->price;
		if( (float) $itemObj->sale_price > 0 ){
			$paymentPrice = $itemObj->sale_price;
		}
		return $paymentPrice;
	}

	/**
	 * Generate unique invoice ID
	 *
	 * @return string
	 * @since 4.0.5
	 */
	public function generateInvoiceId(){
		$today 	= Factory::getDate();
		$today 	= HTMLHelper::_('date', $today, 'Ymd');
		$rand 	= strtoupper(substr(uniqid(sha1(time())),0,6));
		return $rand . $today;
	}

	/**
	 * Insert order for Direct Payment, Bank, Stripe.
	 *
	 * @param 	array 	$ordersinfo
	 * @return 	string 	return unique invoice ID.
	 * @since 	4.0.4
	 */
	public function insertOrders( $ordersinfo = array() ){
		$invoiceId = $this->generateInvoiceId();

		foreach ( $ordersinfo as $orderinfo ) {

			$input 			= Factory::getApplication()->input;
			$payment_method = $input->get('pm', 'direct', 'WORD');
			$payment_note 	= $input->get('payment_note', '', 'STRING');
			$user 			= Factory::getUser();

			$db 	= Factory::getDbo();
			$query 	= $db->getQuery(true);
			$date 	= Factory::getDate()->toSql();
			$user 	= Factory::getUser();

			$modified       = $date;
			$modified_by    = $user->get('id');
			$created        = $date;
			$created_by     = $user->get('id');

			$orderinfo->order_payment_id = $invoiceId;
			$orderinfo->order_payment_method = $payment_method;
			$orderinfo->invoice_id = $invoiceId;
			$orderinfo->payment_note = $payment_note;
			
			$paymentPrice = $this->getPaymentPrice($orderinfo);

			$columns = array('order_user_id', 'course_id', 'order_price', 'order_payment_price', 'order_payment_id', 'order_payment_method', 'invoice_id', 'payment_note', 'published','modified','modified_by','created','created_by');
			$values  = array($db->quote($user->id), $db->quote($orderinfo->id), $db->quote($orderinfo->price), $db->quote($paymentPrice), $db->quote($orderinfo->order_payment_id), $db->quote($orderinfo->order_payment_method), $db->quote($orderinfo->invoice_id), $db->quote($orderinfo->payment_note),0,$db->quote($modified),$db->quote($modified_by),$db->quote($created),$db->quote($created_by));

			$query
			    ->insert($db->quoteName('#__splms_orders'))
			    ->columns($db->quoteName($columns))
			    ->values(implode(',', $values));

		    $db->setQuery($query);
		    $db->execute();
		}

		return $invoiceId;
	}

	/**
	 * Update order data.
	 *
	 * @param 	string 	$orderId
	 * @param 	array 	$updateData
	 * @return 	boolean
	 * @since 	4.0.5
	 */
	public function updateOrder($orderId, $updateData ){
		try {
			$db = Factory::getDbo();
			$query = $db->getQuery(true);

			// update fields
			$fields = [];
            foreach($updateData as $column => $value){
                $value = is_numeric($value) ? $value: $db->quote($value);
                $fields[] = $db->quoteName($column) . ' = ' . $value;
            }

			$conditions = array(
				$db->quoteName('invoice_id') . ' = '.$db->quote($orderId), 
				$db->quoteName('order_payment_id') . ' = ' . $db->quote($orderId)
			);

			$query->update($db->quoteName('#__splms_orders'))->set($fields)->where($conditions);

			$db->setQuery($query);
			$db->execute();
			return true;
		} catch (\Exception $e) {
			return false;
		}
		
	}
}
