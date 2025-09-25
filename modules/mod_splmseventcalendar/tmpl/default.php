<?php
/**
* @package com_splms
* @subpackage  mod_splmseventcalendar
*
* @author JoomShaper http://www.joomshaper.com
* @copyright Copyright (c) 2010 - 2024 JoomShaper
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/

defined ('_JEXEC') or die('Resticted Aceess');
use Joomla\CMS\Language\Text;
?>

<?php if(!count((array)$events)){ 


	echo Text::_('MOD_SPLMSCOURSESEARCH_NO_ITEM_FOUND');
} ?>

<div id="mod-splms-event-calendar-<?php echo $module->id; ?>" class="mod-splms-event-calendar <?php echo $moduleclass_sfx; ?>"></div>
<script>
	jQuery(function($) {
		$("#mod-splms-event-calendar-<?php echo $module->id; ?>").eventcalendar({
			events: '<?php echo json_encode($events); ?>'
		});
	});
</script>