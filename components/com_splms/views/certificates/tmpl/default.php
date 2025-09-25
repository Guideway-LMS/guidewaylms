<?php
/**
* @package com_splms
* @author JoomShaper http://www.joomshaper.com
* @copyright Copyright (c) 2010 - 2024 JoomShaper
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/

// No Direct Access
defined ('_JEXEC') or die('Resticted Aceess');

// Get Columns
$columns = $this->params->get('columns', '4');

?>

<div id="splms" class="splms view-splms-certificates splms-certificxate-list">
	<div class="splms-row">
		<?php if(count($this->items)) { ?>

		<!-- Column -->
		<?php foreach ($this->items as $item) { ?>
		<div class="splms-col-md-<?php echo round(12/$columns); ?> splms-col-sm-6">
			<h3>
				<a href="<?php echo $item->url; ?>"><?php echo $item->name; ?>
				</a>
			</h3>
		</div>
		<?php }  ?>
		<!-- END:: Column -->
		<?php } ?>
	</div>
</div>

<!-- BEGIN:: Pagination -->
<?php if ($this->params->get('hide_pagination') == 0) { ?>
<?php if ($this->pagination->pagesTotal > 1) { ?>
<div class="pagination">
	<?php echo $this->pagination->getPagesLinks(); ?>
</div>
<?php } ?>
<?php } ?>
<!-- END:: Pagination -->