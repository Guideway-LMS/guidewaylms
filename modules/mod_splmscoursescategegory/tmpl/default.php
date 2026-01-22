<?php
/**
* @package com_splms
* @subpackage  mod_splmscoursescategegory
*
* @author JoomShaper http://www.joomshaper.com
* @copyright Copyright (c) 2010 - 2024 JoomShaper
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/

// No Direct Access
defined ('_JEXEC') or die('Resticted Aceess');

?>

<div class="mod-splms-course-categoies <?php echo $moduleclass_sfx; ?>">
	<ul>
		<?php foreach ($items as $item) {?>
			<li>
				<a href="<?php echo $item->url; ?>">
					<?php if (($params->get('show_icon')) && ($item->icon)) { ?>
						<i class="fa fa-<?php echo $item->icon; ?>"></i>
					<?php } ?>
					<span>
						<?php echo $item->title; ?>
					</span>
				</a>
			</li>
		<?php } ?>
	</ul>
</div>