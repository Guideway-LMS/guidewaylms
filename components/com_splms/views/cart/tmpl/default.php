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
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
?>

<div id="splms" class="splms splms-view-cart">

<?php if (empty($this->carts)) {
	echo Factory::getApplication()->enqueueMessage( Text::_('COM_SPLMS_NO_ITEM_IN_CART'), 'warning');
} ?>

<?php if (!empty($this->carts) && count((array)$this->carts) ) { ?>
<table class="table table-bordered table-striped">

  	<thead>
		<th><?php echo Text::_('COM_SPLMS_CART_COURSE_NAME'); ?></th>
		<th><?php echo Text::_('COM_SPLMS_CART_COURSE_PRICE'); ?></th>
	</thead>

	<?php $total = 0; ?>
	<?php $total_sale_price = 0; ?>
	<?php $sale_price = 0; ?>
	<?php foreach ($this->carts as $this->cart) { ?>
	<tr>
		<td>
			<?php echo $this->cart->title; ?>
		</td>
		<td width="200">
			<div class="price-info-wrap">
				<?php echo SplmsHelper::getPrice($this->cart->price, $this->cart->sale_price); ?>
				<a href="#" class="btn btn-danger btn-xs btn-remove-cart pull-right" data-course="<?php echo $this->cart->id; ?>"><i class="splms-icon-error"></i></a>
			</div>
		</td>
	</tr>
	<?php $total = $total + $this->cart->price; ?>

	<?php 
		if($this->cart->sale_price != 0.00)
		{
			$sale_price = $sale_price + ($this->cart->price - $this->cart->sale_price); 
		}
		else
		{
			$sale_price = $sale_price + 0.0;
		}	
	?>

	<?php } ?>
	<?php (empty($sale_price)) ? 0.0 : $total_sale_price = $total - $sale_price;?>
	<tr>
		<td class="text-right">
			<strong><?php echo Text::_('COM_SPLMS_CART_PRICE_TOTAL'); ?>: </strong>
		</td>
		<td width="200">
			<?php echo SplmsHelper::getPrice($total, $total_sale_price); ?>
		</td>
	</tr>

</table>

<div class="splms-cart">
	<div class="splms-row">
		<div class="splms-col-sm-8 splms-payment-methods">

			<div class="splms-payment-methods-wrap">
				<?php $checked = true; ?>
				<?php if( $this->payment_method == 'all' || ( is_array($this->payment_method) && in_array('paypal', $this->payment_method) ) ) { ?>
				<div class="pull-left splms-slt-payment-method payment-method-paypal">
					<label>
						<input type="radio" name="payment-method" value="paypal" <?php echo $checked ? 'checked="checked"' : ''; ?>>
						<img style="display: inline-block; " class="splms-img-responsive splms-img-paypal" src="<?php echo Uri::base(true) . '/components/com_splms/assets/images/paypal-payment.png'; ?>">
					</label>
				</div>
				<?php $checked = false; ?>
				<?php } ?>

				<?php if( $this->payment_method == 'all' || ( is_array($this->payment_method) && in_array('stripe', $this->payment_method) ) ) { ?>
				<div class="pull-left splms-slt-payment-method payment-method-stripe">
					<label>
						<input type="radio" name="payment-method" value="stripe" <?php echo $checked ? 'checked="checked"' : ''; ?>>
						<img style="display: inline-block; " class="splms-img-responsive splms-img-stripe" src="<?php echo Uri::base(true) . '/components/com_splms/assets/images/stripe-payment.png'; ?>">
					</label>
				</div>
				<?php $checked = false; ?>
				<?php } ?>

				<?php if( $this->payment_method == 'all' || ( is_array($this->payment_method) && in_array('razorpay', $this->payment_method) ) ) { ?>
				<div class="pull-left splms-slt-payment-method payment-method-razorpay">
					<label>
						<input type="radio" name="payment-method" value="razorpay" <?php echo $checked ? 'checked="checked"' : ''; ?>>
						<img style="display: inline-block; " class="splms-img-responsive splms-img-razorpay" src="<?php echo Uri::base(true) . '/components/com_splms/assets/images/razorpay-payment.png'; ?>">
					</label>
				</div>
				<?php $checked = false; ?>
				<?php } ?>

				<?php if( $this->payment_method == 'all' || ( is_array($this->payment_method) && in_array('direct', $this->payment_method) ) ) { ?>
					<div class="pull-left splms-slt-payment-method payment-method-direct">
						<label>
							<input type="radio" name="payment-method" value="direct" <?php echo $checked ? 'checked="checked"' : ''; ?>>
								<img style="display: inline-block; " class="splms-img-responsive splms-img-direct" src="<?php echo Uri::base(true) . '/components/com_splms/assets/images/direct-payment.png'; ?>">
							</input>
						</label>
					</div>
					<?php $checked = false; ?>
				<?php } ?>

				<?php if( $this->payment_method == 'all' || ( is_array($this->payment_method) && in_array('bank', $this->payment_method) ) ) { ?>
					<div class="pull-left splms-slt-payment-method payment-method-bank">
						<label>
							<input type="radio" name="payment-method" value="bank" <?php echo $checked ? 'checked="checked"' : ''; ?>>
								<img style="display: inline-block; " class="splms-img-responsive splms-img-bank" src="<?php echo Uri::base(true) . '/components/com_splms/assets/images/bank-transfer.png'; ?>">
							</input>
						</label>
					</div>
					<?php $checked = false; ?>
				<?php } ?>
			</div> <!-- ./splms-payment-methods-wrap -->
		</div>

		<div class="splms-col-sm-4 clearfix splms-payment-submit">
			<?php
			if($this->user->guest) {
				$link =  base64_encode(Route::_(Uri::root() . 'index.php?option=com_splms&view=cart' . SplmsHelper::getItemid('cart'), false));
				$login_link = Route::_(Uri::root() . 'index.php?option=com_users&view=login'. SplmsHelper::getItemid('login') .'&return=' . $link, false);
				?>
					<a href="<?php echo $login_link; ?>" class="btn btn-primary pull-right"><?php echo Text::_('COM_SPLMS_CART_LOGIN_TO_CHECKOUT'); ?></a>
				<?php
			} else {
				
				if( $this->payment_method == 'all' || ( is_array($this->payment_method) && in_array('paypal', $this->payment_method) ) ) {

					if ($this->params->get('shop_environment') == 'sandbox') {
						$action = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
					} else {
						$action = 'https://www.paypal.com/cgi-bin/webscr';
					} ?>
					<form action="<?php echo $action; ?>" method="post" class="pull-right splms-paypal-form splms-payment-method payment-method-paypal">
						<input type="hidden" name="cmd" value="_cart">
						<input type="hidden" name="upload" value="1">
						<input type="hidden" name="business" value="<?php echo $this->params->get('paypal_id'); ?>">

						<?php
						$i = 1;
						foreach ($this->carts as $this->cart) { ?>
						<?php $price = !empty((double) $this->cart->sale_price) ? $this->cart->sale_price : $this->cart->price;
						
						?>
						<div id="item_<?php echo $i; ?>" class="itemwrap">
							<input type="hidden" name="item_name_<?php echo $i; ?>" value="<?php echo $this->cart->title; ?>">
							<input type="hidden" name="item_number_<?php echo $i; ?>" value="<?php echo $this->cart->id; ?>">
							<input type="hidden" name="quantity_<?php echo $i; ?>" value="1">
							<input type="hidden" name="amount_<?php echo $i; ?>" value="<?php echo $price; ?>">
						</div>
						<?php $i++; } ?>

						<input type="hidden" name="invoice" value="<?php echo time().rand( 1000 , 9999 ); ?>">
						<input type="hidden" name="custom" value="<?php echo $this->user->id; ?>">
						<input type="hidden" name="currency_code" value="<?php echo $this->currency[0]; ?>">

						<input type="hidden" name="notify_url" value="<?php echo $this->notify_url; ?>"/>
						<input type="hidden" name="return" value="<?php echo $this->return_success; ?>"/>
						<input type="hidden" name="cancel_return" value="<?php echo $this->return_cencel; ?>"/>
						<button type="submit" class="btn btn-success" name="submit"><?php echo Text::_('COM_SPLMS_CART_PROCEED_CHECKOUT'); ?></button>
					</form>
				<?php } ?>

				<?php if( $this->payment_method == 'all' || ( is_array($this->payment_method) && in_array('stripe', $this->payment_method) ) ) { ?>
					<div class="splms-payment-method payment-method-stripe pull-right">
						<a href="<?php echo $this->stripe_payment; ?>" class="btn btn-success"><?php echo Text::_('COM_SPLMS_CART_PROCEED_CHECKOUT'); ?></a>
					</div>
				<?php } ?>

				<?php if( $this->payment_method == 'all' || ( is_array($this->payment_method) && in_array('razorpay', $this->payment_method) ) ) { ?>
					<div class="splms-payment-method payment-method-razorpay pull-right">
						<a href="<?php echo $this->razorpay_payment; ?>" class="btn btn-success"><?php echo Text::_('COM_SPLMS_CART_PROCEED_CHECKOUT'); ?></a>
					</div>
				<?php } ?>

				<?php if( $this->payment_method == 'all' || ( is_array($this->payment_method) && in_array('direct', $this->payment_method) ) ) { ?>
					<div class="splms-payment-method payment-method-direct pull-right">
						<a href="<?php echo $this->direct_payment; ?>" class="btn btn-success"><?php echo Text::_('COM_SPLMS_CART_PROCEED_CHECKOUT'); ?></a>
					</div>
				<?php } ?>

				<?php if( $this->payment_method == 'all' || ( is_array($this->payment_method) && in_array('bank', $this->payment_method) ) ) { ?>
					<div class="splms-payment-method payment-method-bank pull-right">
						<a href="<?php echo $this->bank_payment; ?>" class="btn btn-success btn-bankpayment"><?php echo Text::_('COM_SPLMS_CART_PROCEED_CHECKOUT'); ?></a>
					</div>
				<?php } ?>

				<?php
			} ?>
		</div>
	</div>
	<div class="clearfix"></div>
	<div class="splms-payment-methods-text-wrap">
		<?php if ($this->bank_info) { ?>
			<div class="splms-payment-method-bank splms-row">
				<div class="splms-payment-method-bank-info splms-col-sm-6">
					<h3><?php echo Text::_('COM_SPLMS_PAYMENT_METHOD_BANK_INFO'); ?></h3>
					<p class="splms-bank-info"><?php echo $this->bank_info; ?></p>
				</div>

				<div class="form-group splms-payment-method-payment-note splms-col-sm-6">
					<label for="comment"><?php echo Text::_('COM_SPLMS_PAYMENT_NOTE'); ?></label>
					<textarea name="splms-payment-note" class="form-control" rows="5" id="splms-payment-note"></textarea>
				</div>
			</div>
		<?php } ?>
	</div> <!--  /.splms-payment-methods-text-wrap -->

</div>
<?php }?>



</div>
