<?php
/**
 * @package     SP Movie Databse
 *
 * @copyright   Copyright (C) 2010 - 2021 JoomShaper. All rights reserved.
 * @license     GNU General Public License version 2 or later.
 */

defined('_JEXEC') or die('Restricted Access');

$rating 	= $displayData['rating'];

$class = '';
if(isset($displayData['class']) && $displayData['class']) {
	$class 		= $displayData['class'];
}

?>
<div class="sp-lms-rating <?php echo $class; ?>">
    <?php 
	$max_rating = 5;
	
	$j = 0;
	for($i = $rating; $i < $max_rating; $i++){
		echo '<i class="star far fa-star" aria-hidden="true" data-rating_val="'.($max_rating-$j).'"></i>';
		$j = $j+1;
	}
	for ($i = 0; $i < $rating; $i++) {
		echo '<i class="star fa fa-star" aria-hidden="true" data-rating_val="'.($rating - $i).'"></i>';
	} ?>
</div>