<?php
/**
 * @package     SP Movie Databse
 *
 * @copyright   Copyright (C) 2010 - 2021 JoomShaper. All rights reserved.
 * @license     GNU General Public License version 2 or later.
 */

defined('_JEXEC') or die('Restricted Access');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Component\ComponentHelper;

$teacher = $displayData['teacher'];
$app = Factory::getApplication();
$params = $app->getParams();

$contact_tac 		= $params->get('contact_tac', 1);
$contact_tac_text = $params->get('contact_tac_text', '
    I agree with the <a href="#">Terms of Use</a> and <a href="#">Privacy Policy</a> and I declare that I have read the information that is required in accordance with <a href="http://eur-lex.europa.eu/legal-content/EN/TXT/?uri=uriserv:OJ.L_.2016.119.01.0001.01.ENG&amp;toc=OJ:L:2016:119:TOC" target="_blank">Article 13 of GDPR.</a>
');
$show_contact = $params->get('teacher_contact', '');
?>

<?php if ($show_contact && !empty($teacher->email)) { ?>
    <div class="splms-section">
		<h3 class="splms-section-title"><?php echo Text::_('COM_SPLMS_PERSON_CONTACT_TITLE'); ?></h3>
        <div id="splms-teacher-contact-form-wrapper" class="splms-teacher-contact-form">
            <form id="splms-teacher-contact-form">
                <div class="controls">
                    <label for="name"><?php echo Text::_('COM_SPLMS_PERSON_CONTACT_NAME_LABEL'); ?></label>
                    <input type="text" name="name" id="name" placeholder="<?php echo Text::_('COM_SPLMS_PERSON_CONTACT_NAME_LABEL') ?>" required="required">
                </div>

                <div class="controls">
                    <label for="email"><?php echo Text::_('COM_SPLMS_PERSON_CONTACT_EMAIL_LABEL'); ?></label>
                    <input type="email" name="email" id="email" placeholder="<?php echo Text::_('COM_SPLMS_PERSON_CONTACT_EMAIL_LABEL') ?>" required="required">
                </div>
            
                <div class="controls">
                    <label for="phone"><?php echo Text::_('COM_SPLMS_PERSON_CONTACT_PHONE_NUMBER_LABEL'); ?></label>
                    <input type="tel" name="phone" id="phone" placeholder="<?php echo Text::_('COM_SPLMS_PERSON_CONTACT_PHONE_NUMBER_LABEL') ?>" required="required">
                </div>

                <div class="controls">
                    <label for="subject"><?php echo Text::_('COM_SPLMS_PERSON_CONTACT_SUBJECT_LABEL'); ?></label>
                    <input type="text" name="subject" id="subject" placeholder="<?php echo Text::_('COM_SPLMS_PERSON_CONTACT_SUBJECT_LABEL') ?>" required="required">
                </div>

                <div class="controls">
                    <label for="message"><?php echo Text::_('COM_SPLMS_PERSON_CONTACT_MESSAGE_LABEL'); ?></label>
                    <textarea name="message" id="message" rows="5" class="sppb-form-control" placeholder="<?php echo Text::_('COM_SPLMS_PERSON_CONTACT_MESSAGE_LABEL') ?>" required="required"></textarea>
                </div>

                <?php if($contact_tac && $contact_tac_text) { ?>
                    <div class="controls">
                        <label class="form-checkbox">
                            <input type="checkbox" id="tac" name="tac" value="tac" required="true" data-apptac="true">
                            <?php echo $contact_tac_text; ?>
                        </label>
                    </div>
                <?php } ?>
                
                <?php
                $componentParams	= ComponentHelper::getParams('com_splms');
                $isCaptchaEnabled 	= (int) $componentParams->get('teacher_contact_recaptcha');
                
                if($isCaptchaEnabled){
                    PluginHelper::importPlugin('captcha');
                    Factory::getApplication()->triggerEvent('onInit',['splms_teacher_recaptcha']);
                    $recaptcha = Factory::getApplication()->triggerEvent('onDisplay',[null,'splms_teacher_recaptcha']);
                
                ?>
                    <div class="controls">
                        <?php echo (isset($recaptcha[0])) ? $recaptcha[0] : ''; ?>
                    </div>
                <?php } ?>

                <div class="controls">
                    <input type="hidden" name="teacher_email" value="<?php echo base64_encode($teacher->email); ?>">
                    <button type="submit" id="contact-submit"  class="btn btn-primary"><?php echo Text::_('COM_SPLMS_PERSON_CONTACT_SUBMIT'); ?></button>
                </div>
            </form>
            <div style="display:none;margin-top:10px;" class="splms-cont-status"></div>
        </div>
    </div>
<?php } ?>