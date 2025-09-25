<?php

/**
 * @package com_splms
 * @subpackage  mod_splmscart
 *
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2024 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
 */

// No Direct Access
defined ('_JEXEC') or die('Resticted Aceess');

use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Component\ComponentHelper;

//Joomla Component Helper & Get LMS Params
jimport('joomla.application.component.helper');
$splmsparams = ComponentHelper::getParams('com_splms');

//Get Currency
$currency = explode(':', $splmsparams->get('currency', 'USD:$'));
$currency =  $currency[1];

// Get image thumb
$thumb_size = strtolower($splmsparams->get('course_thumbnail_small', '100X60'));
$cart_url = Route::_('index.php?option=com_splms&view=cart'. splmshelper::getItemid('cart'));

?>

<div class="mod-splms-courses splms-cart-list <?php echo $moduleclass_sfx; ?>">
	<div class="cart-icon">
		<i class="fa fa-shopping-bag"></i>
	</div>
	
	<div class="splms-courses-list-wrap">
	<?php if(count($items)){ ?>
		<ul class="splms-courses-list">
			<?php $total = 0; ?>
			<?php foreach ($items as $item) { ?>
				<?php $generate_price = SplmsHelper::getPrice($item->price, $item->sale_price); ?>
				<?php $cart_price = ($item->sale_price > 0) ? $item->sale_price : $item->price; ?>
				<li class="mod-splms-course clearfix">
					<div>
						<a href="<?php echo $item->url; ?>">
							<a href="<?php echo $item->url; ?>">
								<img src="<?php echo $item->thumbnail; ?>" class="splms-course-img splms-img-responsive" alt="<?php echo $item->title; ?>">
							</a>
						</a>
					</div>
					
					<div class="course-title-wrap">
						<h3 class="splms-course-title">
							<a href="<?php echo $item->url; ?>" class="splms-course-title"><?php echo $item->title; ?></a>
						</h3>
					</div>

					<div class="mod-splms-course-price">
						<?php echo $generate_price; ?>
					</div>
				</li>
				<?php $total = $total + $cart_price; ?>
			<?php } ?>
		</ul>
		

		<div class="splms-courses-footer">
			<div>
				<a href="<?php echo $cart_url; ?>" class="btn btn-primary"><?php echo Text::_('MOD_SPLMS_CART_GOTO_CART');?></a>
			</div>
			<div class="total-cost">
				<p class="title"><?php echo Text::_('MOD_SPLMS_CART_YOUR_TOTAL');?></p>
				<div class="cost"><?php echo SplmsHelper::getPrice($total); ?></div>
			</div>
		</div>
		<?php } else {?>
			<div class="cart-text-wrap">
				<span class="title"><?php echo Text::_('MOD_SPLMS_CART_EMPTY');?></span>
			</div>
		<?php } ?>
	</div>
</div>

<script>
	
</script>
