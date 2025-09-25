<?php

use Joomla\CMS\Uri\Uri;
/**
* @package com_splms
* @author JoomShaper http://www.joomshaper.com
* @copyright Copyright (c) 2010 - 2024 JoomShaper
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/

// No Direct Access
defined ('_JEXEC') or die('Resticted Aceess');

$columns = $this->params->get('columns', '4');
?>

<div id="splms" class="splms view-splms-courses splms-course-categories">

	<?php if(count($this->items)) { ?>
		<!-- Column -->
		<?php foreach(array_chunk($this->items, $columns) as $this->items) { ?>
		<div class="splms-row splms-course-category-wrapper">
			
			<?php foreach ($this->items as $item) { ?>				
				<div class="splms-col-sm-<?php echo round(12/$columns); ?> splms-course-category">
					<div class="splms-coursescategory-wrapper">
						<div class="splms-cat-icon">
							<a href="<?php echo $item->url; ?>">
								<?php if($item->show == 1 && $item->image){ ?>
									<img src="<?php echo Uri::root() . $item->image; ?>">
								<?php } else { ?>
									<i class="fa fa-<?php echo $item->icon; ?>"></i>
								<?php } ?>
							</a>
						</div>
						<a class="splms-cat-title" href="<?php echo $item->url; ?>">
							<?php echo $item->title; ?>
							<span class="splms-cat-count">
								<?php echo '(' . $item->courses . ')'; ?> 
							</span>
						</a>
						<?php
							if($this->subcategoryEnabled){
								$subCatLinks = [];
								foreach($item->subcategories as $subCat){
									$subCatLinks[] = "<a href='{$subCat->url}'>{$subCat->title} ({$subCat->courses})</a>";
								}
								echo implode(', ',$subCatLinks);
							} 
						?>
					</div>
				</div>
			<?php } // END:: foreach ?>
		</div> <!-- /.splms-row -->
		<?php } // END:: array_chunk ?>
	<?php } //END:: Count items ?>

	<?php if ($this->params->get('hide_pagination') == 0) { //Pagination ?>
		<?php if ($this->pagination->pagesTotal > 1) { ?>
			<div class="pagination">
				<?php echo $this->pagination->getPagesLinks(); ?>
			</div>
		<?php } ?>
	<?php } ?>
	
</div>