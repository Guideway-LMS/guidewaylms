<?php
/**
 * @package     SP Movie Databse
 *
 * @copyright   Copyright (C) 2010 - 2021 JoomShaper. All rights reserved.
 * @license     GNU General Public License version 2 or later.
 */

defined('_JEXEC') or die('Restricted Access');
$profile = $displayData['teacher'];
?>

<?php if ( (!empty($profile->social_facebook)) ||
	(!empty($profile->social_linkedin)) ||
	(!empty($profile->social_twitter)) ||
	(!empty($profile->social_youtube))) { ?>

	<ul class="splms-persion-social-icons">
		<?php if (!empty($profile->social_facebook && $profile->social_facebook)) { ?>
		<li class="facebook">
			<a href="https://facebook.com/<?php echo $profile->social_facebook; ?>" target="_blank">
				<i class="splms-icon-facebook"></i>
			</a>
		</li>
		<?php } if (!empty($profile->social_twitter) && $profile->social_twitter) {?>
		<li class="twitter">
			<a href="https://twitter.com/<?php echo $profile->social_twitter; ?>" target="_blank">
				<i class="splms-icon-twitter"></i>
			</a>
		</li>
		<?php } if (!empty($profile->social_linkedin) && $profile->social_linkedin) {?>
		<li class="linkedin">
			<a href="https://linkedin.com/<?php echo $profile->social_linkedin; ?>" target="_blank">
				<i class="splms-icon-linkedin"></i>
			</a>
		</li>
		<?php } if (!empty($profile->social_instagram) && $profile->social_instagram) { ?>
		<li class="instagram">
			<a href="https://instagram.com/<?php echo $profile->social_instagram; ?>" target="_blank">
				<i class="splms-icon-instagram"></i>
			</a>
		</li>
		<?php } if (!empty($profile->social_youtube) && $profile->social_youtube) {?>
		<li class="twitter">
			<a href="https://youtube.com/<?php echo $profile->social_youtube; ?>" target="_blank">
				<i class="splms-icon-youtube"></i>
			</a>
		</li>
		<?php } ?>
	</ul>
<?php } ?>