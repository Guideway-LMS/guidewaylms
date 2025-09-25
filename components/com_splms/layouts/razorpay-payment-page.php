<?php

/**
 * @package     SP LMS
 *
 * @copyright   Copyright (C) 2010 - 2021 JoomShaper. All rights reserved.
 * @license     GNU General Public License version 2 or later.
 */

defined('_JEXEC') or die('Restricted Access');

use Joomla\CMS\Uri\Uri;

$cancelUrl = Uri::base() . 'index.php?option=com_splms&task=payment.paymencancel';
$verifyUrl = Uri::base() . 'index.php?option=com_splms&task=payment.razorpayVerify';
?>

<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<form name='razorpayform' action="<?php echo $verifyUrl; ?>" method="POST">
    <input type="hidden" name="razorpay_payment_id" id="razorpay_payment_id">
    <input type="hidden" name="razorpay_signature" id="razorpay_signature">
</form>
<script>
    var options = <?php echo $json ?>;

    options.handler = function(response) {
        document.getElementById('razorpay_payment_id').value = response.razorpay_payment_id;
        document.getElementById('razorpay_signature').value = response.razorpay_signature;
        document.razorpayform.submit();
    };

    options.modal = {
        ondismiss: function() {
            // goto payment cancel URL
            window.location.href = '<?php echo $cancelUrl; ?>';
        },
        // Boolean indicating whether pressing escape key 
        // should close the checkout form. (default: true)
        escape: false,
        // Boolean indicating whether clicking translucent blank
        // space outside checkout form should close the form. (default: false)
        backdropclose: false
    };

    var rzp = new Razorpay(options);
    rzp.open();
</script>