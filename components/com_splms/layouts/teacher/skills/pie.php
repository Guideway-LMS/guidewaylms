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
            <div class="splms-person-progress">
                <?php
                $i = 1;
                foreach ($skills as $skill) {
                    $i = ($i == 5) ? 1 : $i;
                    switch ($i) {
                        case 1:
                            $pie_color = '#8062FE';
                            break;
                        case 2:
                            $pie_color = '#1DDBB7';
                            break;
                        case 3:
                            $pie_color = '#FD7563';
                            break;
                        case 4:
                            $pie_color = '#913A72';
                            break;

                        default:
                            $pie_color = '#999999';
                            break;
                    } ?>
                    <div class="splms-pie-chart" data-size="70" data-percent="<?php echo (isset($skill['specialist_number']) && $skill['specialist_number']) ? $skill['specialist_number'] : 0; ?>" data-barcolor="<?php echo $pie_color; ?>" data-trackcolor="#f5f5f5">
                        <div class="splms-chart-percent"><span><?php echo (isset($skill['specialist_number']) && $skill['specialist_number']) ? $skill['specialist_number'] : 0; ?>%</span></div>
                        <div class="info"><?php echo (isset($skill['specialist_text']) && $skill['specialist_text']) ? $skill['specialist_text'] : $skill; ?></div>
                    </div>
                    <?php $i++;
                } ?>
            </div>
        </div>
    </div>
<?php } ?>