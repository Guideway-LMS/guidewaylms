<?php
/**
* @package com_splms
* @author JoomShaper http://www.joomshaper.com
* @copyright Copyright (c) 2010 - 2024 JoomShaper
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/

// No Direct Access
defined ('_JEXEC') or die('Resticted Aceess');

use Joomla\CMS\Language\Text;

$columns = $this->params->get('columns', '3');

?>

<div id="splms" class="splms view-splms-quiz-list">

	<?php foreach(array_chunk($this->items, $columns) as $this->items) { ?>
		<div class="splms-row">
			<?php foreach ($this->items as $item) { ?>
				<div class="splms-col-sm-6 splms-col-md-4 splms-col-lg-<?php echo round(12/$columns); ?>">
					<div class="quiz-item-wrapper">
						<div class="quiz-banner">
							<img class="img-responsive" src="<?php echo $item->image; ?>" alt="<?php echo $item->title; ?>">
						</div>
						<div class="quiz-description">
							<h3 class="quiz-title"><a href="<?php echo $item->url; ?>"><?php echo $item->title; ?></a></h3>
							<?php echo $item->description; ?>
							<h4 class="quiz-course-name"><?php echo $item->cat_name; ?></h4>
							<h4 class="quiz-duration"><?php echo Text::_('COM_SPLMS_COMMON_DURATION');?>: <?php echo $item->duration; ?> Sec </h4>
							<a href="<?php echo $item->url; ?>" class="btn btn-primary"><?php echo Text::_('COM_SPMS_START_THE_QUIZ'); ?></a>
						</div>
					</div>
				</div>
			<?php } ?>
		</div> <!-- /.splms-row -->
	<?php }// END:: array chunk ?>
	
</div> <!-- /.splms -->

<?php if ($this->params->get('hide_pagination') == 0) {?>
	<?php if ($this->pagination->pagesTotal > 1) { ?>
	<div class="pagination">
		<?php echo $this->pagination->getPagesLinks(); ?>
	</div>
	<?php } ?>
<?php } ?>


