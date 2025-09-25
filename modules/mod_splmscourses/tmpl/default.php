<?php

/**
 * @package com_splms
 * @subpackage  mod_splmscourses
 *
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2024 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
 */

// No Direct Access
defined('_JEXEC') or die('Resticted Aceess');

use Joomla\CMS\Language\Text;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Component\ComponentHelper;

//Joomla Component Helper & Get LMS Params
$splmsparams = ComponentHelper::getParams('com_splms');

//Get Currency
$currency = explode(':', $splmsparams->get('currency', 'USD:$'));
$currency =  $currency[1];

// Get image thumb
$thumb_size = strtolower($splmsparams->get('course_thumbnail_small', '100X60'));

?>

<div class="mod-splms-courses <?php echo $moduleclass_sfx; ?>">

	<ul class="splms splms-courses-list">
		<div class="splms-row">
			<?php foreach (array_chunk($items, $columns) as $items) { ?>
				<?php foreach ($items as $item) {
					$discount_percentage = '';
					if ($show_discount && ($item->price > $item->sale_price) && ($item->sale_price != '0.00' && $item->price != '0.00')) {
						$discount_percentage = (($item->price - $item->sale_price) * 100) / $item->price;
					} ?>
					<?php $item->price = ($item->price == 0) ? Text::_('MOD_SPLMS_COURSES_FREE') : SplmsHelper::getPrice($item->price, $item->sale_price); ?>
					<div class="splms-col-sm-6 splms-col-md-<?php echo round(12 / $columns); ?>">
						<li class="mod-splms-course clearfix">
							<a href="<?php echo $item->url; ?>">
								<?php
								$filename = basename($item->image);
								$path = JPATH_BASE . '/' . dirname($item->image) . '/thumbs/' . File::stripExt($filename) . '_' . $thumb_size . '.' . File::getExt($filename);
								$src = JURI::base(true) . '/' . dirname($item->image) . '/thumbs/' . File::stripExt($filename) . '_' . $thumb_size . '.' . File::getExt($filename);

								if (File::exists($path)) {
									$thumb = $src;
								} else {
									$thumb = $item->image;
								}
								?>
								<a href="<?php echo $item->url; ?>" class="img-wrapper">
									<img src="<?php echo $thumb; ?>" class="splms-course-img splms-img-responsive" alt="<?php echo $item->title; ?>">
									<?php if ($show_discount && isset($discount_percentage) && $discount_percentage) { ?>
										<span class="splms-course-discount-price"><?php echo round($discount_percentage) . Text::_('MOD_SPLMS_COURSES_PERCENT_OFF'); ?></span>
									<?php } ?>
								</a>
							</a>

							<strong class="splms-course-title">
								<a href="<?php echo $item->url; ?>" class="splms-course-title"><?php echo $item->title; ?></a>
							</strong>

							<div class="mod-splms-course-price">
								<?php echo $item->price; ?>
							</div>

						</li> <!-- mod-splms-course -->
					</div>
			<?php }
			} ?>
		</div> <!-- splms-row -->
	</ul> <!-- splms-courses-list -->
</div>

<?php
