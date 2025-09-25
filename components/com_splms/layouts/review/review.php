<?php
/**
 * @package     SP Movie Databse
 *
 * @copyright   Copyright (C) 2010 - 2021 JoomShaper. All rights reserved.
 * @license     GNU General Public License version 2 or later.
 */

defined('_JEXEC') or die('Restricted Access');

use Joomla\CMS\Layout\LayoutHelper;

$review = $displayData['review'];

if(isset($review) && $review) {
?>
<div class="review-wrap review-item" id="review-id-<?php echo $review->id; ?>" data-review_id="<?php echo $review->id; ?>">
	<div class="profile-img">
		<img src="<?php echo SplmsHelper::getAvatar($review->created_by); ?>" alt="">
	</div>
	<div class="review-box">
		<div class="reviewers-review">
			<p class="reviewers-name">
				<?php echo $review->name; ?>
			</p>
			<div class="date-time">
				<span class="sppb-meta-date" itemprop="dateCreated"><?php echo SplmsHelper::timeago($review->created); ?></span>
			</div>
			<div class="clearfix"></div>
		</div>
		<?php echo LayoutHelper::render('review.ratings', array('rating'=>$review->rating)); ?>
	</div>
	<div class="review-text-box">
		<?php if(isset($review->review) && $review->review) { ?>
		<div class="review-message">
			<p>
				<?php echo nl2br($review->review); ?>
			</p>
		</div>
		<?php } ?>
	</div>
</div>
<?php }

