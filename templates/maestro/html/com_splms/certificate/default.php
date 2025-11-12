<?php

/**
 * @package com_splms
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2022 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
 */

// No Direct Access
defined('_JEXEC') or die('Resticted Aceess');

use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
?>

<div id="splms" class="splms view-certificate">
	<div class="row">
		<div class="col-lg-12">
			<div class="certificate">
				<div class="certificate-top-wrapper">
					<div class="row">
						<div class="col-md-6 left-part">
							<div class="header">
								<h3 class=""><?php echo Text::_('COM_SPLMS_CERTIFICATE_OF_CONPLETATION_TITLE'); ?><span><?php echo Text::_('COM_SPLMS_CERTIFICATE_OF_CONPLETATION_SUB_TITLE'); ?><span></h3>
							</div>

							<p class="info">
								<?php echo Text::_('COM_SPLMS_THIS_IS_CERTIFY'); ?> <br />
								<strong><i><?php echo (isset($this->item->student_info->name) && $this->item->student_info->name) ? $this->item->student_info->name : ''; ?></i></strong> <br /> <?php echo Text::_('COM_SPLMS_THIS_IS_SUCCESSFULLY_COMPLITED'); ?> <strong><i><?php echo $this->item->course; ?></i></strong> <?php echo Text::_('COM_SPLMS_CERTIFICATE_COURSE') . '.'; ?>
							</p>

							<p class="name"><?php echo $this->item->instructor; ?></p>
							<p class="course-title"><i><?php echo Text::_('COM_SPLMS_COURSE_INSTRUCTOR'); ?></i></p>
						</div>
						<div class="col-md-6 right-part">
							<img class="img-responsive student-img" src="<?php echo $this->item->student_image; ?>" alt="image" class="img-responsive">
						</div>
					</div>
				</div>
			</div>
			<div class="certificate-bottom-wraper">
				<div class="certificate-bottom-inner">
					<p><i class="fas fa-user-graduate"></i><?php echo (isset($this->item->student_info->name) && $this->item->student_info->name) ? $this->item->student_info->name : ''; ?></p>
					<p class="certificate-no"><i class="fa fa-certificate" aria-hidden="true"></i><span class="text-uppercase"><?php echo $this->item->certificate_no; ?></span></p>
					<p><?php echo $this->item->course; ?></p>
				</div>
			</div>
		</div>
	</div>
</div>