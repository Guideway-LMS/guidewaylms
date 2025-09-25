<?php
/**
 * @package     SP Movie Databse
 *
 * @copyright   Copyright (C) 2010 - 2021 JoomShaper. All rights reserved.
 * @license     GNU General Public License version 2 or later.
 */

defined('_JEXEC') or die('Restricted Access');

use Joomla\CMS\Language\Text;

$skills = $displayData['skills'];
?>

<?php if (!empty($skills) && is_array($skills)) { ?>
    <div class="splms-section">
        <h3 class="splms-section-title"><?php echo Text::_('COM_SPLMS_COMMON_SKILLS'); ?></h3>
        <div class="splms-person-skills">
            <?php foreach ($skills as $skill) { ?>
                <span class="splms-person-skill-badge">
                    <span class="badge"><?php echo (isset($skill['specialist_text']) && $skill['specialist_text']) ? $skill['specialist_text'] : $skill; ?></span>
                </spam>
            <?php } ?>
        </div>
    </div>
<?php } ?>

