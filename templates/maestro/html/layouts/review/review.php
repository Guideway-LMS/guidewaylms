<?php
/**
 * GUIDEWAY LMS - Review Item Layout Override
 * Template: Maestro
 * Overrides: components/com_splms/layouts/review/review.php
 * Feature: layout-avaliacoes (branch)
 */

defined('_JEXEC') or die('Restricted Access');

use Joomla\CMS\Layout\LayoutHelper;

$review = $displayData['review'];

if (isset($review) && $review) {
    // Build star HTML
    $rating = (int) $review->rating;
    $starsHtml = '';
    for ($i = 1; $i <= 5; $i++) {
        $class = $i <= $rating ? 'fa fa-star' : 'fa fa-star empty';
        $starsHtml .= '<i class="' . $class . '"></i>';
    }

    // Avatar
    $avatarSrc = SplmsHelper::getAvatar($review->created_by);
?>
<div class="gw-review-item review-wrap review-item" id="review-id-<?php echo $review->id; ?>" data-review_id="<?php echo $review->id; ?>">

    <div class="gw-review-avatar">
        <?php if ($avatarSrc): ?>
            <img src="<?php echo $avatarSrc; ?>" alt="<?php echo htmlspecialchars($review->name); ?>">
        <?php else: ?>
            <div class="gw-avatar-placeholder"><i class="fa fa-user"></i></div>
        <?php endif; ?>
    </div>

    <div class="gw-review-content">
        <div class="gw-review-header">
            <span class="gw-review-author"><?php echo htmlspecialchars($review->name); ?></span>
            <span class="gw-review-date"><?php echo SplmsHelper::timeago($review->created); ?></span>
        </div>

        <div class="gw-review-stars">
            <?php echo $starsHtml; ?>
        </div>

        <?php if (isset($review->review) && $review->review): ?>
            <div class="gw-review-text">
                <?php echo nl2br(htmlspecialchars($review->review)); ?>
            </div>
        <?php endif; ?>

        <div class="gw-review-actions">
            <a href="javascript:void(0);" title="Marcar como útil">
                <i class="fa fa-thumbs-o-up"></i> Útil
            </a>
            <a href="javascript:void(0);" title="Denunciar avaliação">
                <i class="fa fa-flag-o"></i> Denunciar
            </a>
        </div>
    </div>

</div>
<?php } ?>
