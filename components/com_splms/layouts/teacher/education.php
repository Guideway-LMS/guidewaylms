<?php
/**
 * @package     SP Movie Databse
 *
 * @copyright   Copyright (C) 2010 - 2021 JoomShaper. All rights reserved.
 * @license     GNU General Public License version 2 or later.
 */

defined('_JEXEC') or die('Restricted Access');
$education = $displayData['education'];
?>

<?php if(!empty($education)) { ?>
    <div class="splms-section">
    <h3 class="splms-section-title"><?php echo JText::_('COM_SPLMS_EDUCATION'); ?></h3>
        <?php $education = json_decode($education, false); ?>
        <div class="splms-person-education">
            <div class="splms-education-institutes">
                <?php foreach($education as $institute) { ?>
                    <?php if(!empty($institute->institute_name)) { ?>
                        <div class="splms-education-institute">
                            <?php if(!empty($institute->institute_logo)) { ?>
                                <div class="splms-education-institute-logo">
                                    <img src="<?php echo $institute->institute_logo; ?>" alt="<?php echo $institute->institute_name; ?>" />
                                </div>
                            <?php } ?>

                            <div class="splms-education-institute-info">
                                <div class="splms-education-institute-name">
                                    <?php echo $institute->institute_name; ?>
                                </div>

                                <?php if(!empty($institute->institute_location) || !empty($institute->education_session)) { ?>
                                    <div class="splms-education-institute-meta">
                                        <?php if(!empty($institute->institute_location)) { ?>
                                            <span class="splms-education-institute-location"><i class="splms-icon-location-pin"></i> <?php echo $institute->institute_location; ?></span>
                                        <?php } ?>

                                        <?php if(!empty($institute->education_session)) { ?>
                                            <span class="splms-education-institute-session"><?php echo $institute->education_session; ?></span>
                                        <?php } ?>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                    <?php } ?>
                <?php } ?>
            </div>
        </div>
    </div>
<?php } ?>