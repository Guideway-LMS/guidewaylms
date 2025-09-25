<?php
/**
 * @package com_splms
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2024 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
 */

// No Direct Access
defined ('_JEXEC') or die('Resticted Aceess');

use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
?>

<?php if(!empty($this->item)) : ?>
<div id="splms" class="splms view-certificate">
	<div class="row">
		<div class="certificate">
			<div class="wrapper">
				<div class="splms-col-md-8 left-part">
					<div class="header">
						<div class="splms-row">
							<div class="splms-col-md-8">
								<h3 class=""><?php echo Text::_('COM_SPLMS_CERTIFICATE_OF_CONPLETATION'); ?></h3>
							</div>
							<div class="splms-col-md-4 certificate-image">
								<img class="img-responsive student-img" src="<?php echo $this->item->student_image; ?>" alt="image" class="img-responsive">
							</div>
						</div>
					</div>

					<p class="info">
						<?php echo Text::_('COM_SPLMS_THIS_IS_CERTIFY'); ?> <strong><i><?php echo (isset($this->item->student_info->name) && $this->item->student_info->name) ? $this->item->student_info->name : ''; ?></i></strong> <?php echo Text::_('COM_SPLMS_THIS_IS_SUCCESSFULLY_COMPLITED'); ?> <strong><i><?php echo $this->item->course; ?></i></strong> <?php echo Text::_('COM_SPLMS_CERTIFICATE_COURSE').'.'; ?>
					</p>

					<p class="name"><?php echo $this->item->instructor; ?></p>
					<p class="course-title"><i><?php echo Text::_('COM_SPLMS_COURSE_INSTRUCTOR'); ?></i></p>

					<p class="certificate-no"><?php echo Text::_('COM_SPLMS_CERTIFICATE_NO'); ?> <span class="text-uppercase"><?php echo $this->item->prefix . $this->item->certificate_no; ?></span></p>

					<?php if($this->item->issue_date && $this->item->issue_date!='0000-00-00') { ?>
					<p class="certificate-no"><?php echo Text::_('COM_SPLMS_CERTIFICATE_ISSUE_DATE'); ?><?php echo HTMLHelper::date($this->item->issue_date , 'd F Y'); ?></p>
					<?php } ?>
				</div>
				<div class="splms-col-md-4 right-part">
					<div class="content">
						<p class="logo">
							<?php if($this->item->logo){?>
								<img src="<?php echo $this->item->logo; ?>" alt="logo">
							<?php } ?>
						</p>

						<p class="course-type"><?php echo Text::_('COM_SPLMS_COURSE_TYPE'); ?></p>
						<p class="course-type-name"><?php echo $this->item->category; ?></p>

						<p class="course-name"><?php echo Text::_('COM_SPLMS_COURSE_NAME'); ?></p>
						<p><?php echo $this->item->course; ?></p>

						<p class="link"><a href="<?php echo Uri::root(); ?>"><?php echo $this->item->organization; ?></a></p>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<?php endif; ?>