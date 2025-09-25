<?php
/**
* @package com_splms
* @subpackage  mod_splmseventcategories
*
* @author JoomShaper http://www.joomshaper.com
* @copyright Copyright (c) 2010 - 2024 JoomShaper
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/

// No Direct Access
defined ('_JEXEC') or die('Resticted Aceess');

?>

<div class="splms-category-module mod-splms-event-categories <?php echo $moduleclass_sfx; ?>">
	<ul class="category-module">
		<?php foreach ($items as $item) {				
			$itemsEvent = SplmsModelEvents::getEventByCategory($item->id);
		?>
			<li>
				<a class="mod-event-category-title " href="<?php echo $item->url; ?>">
					<?php echo $item->title; ?>
					<span class="count-events pull-right">  (<?php echo count($itemsEvent); ?>) </span>
				</a>
			</li>
		<?php } ?>
	</ul>
</div>