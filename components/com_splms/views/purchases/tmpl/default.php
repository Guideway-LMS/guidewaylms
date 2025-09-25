<?php
/**
 * @package com_splms
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2024 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
 */

// No Direct Access
defined ('_JEXEC') or die('Resticted Aceess');

use Joomla\CMS\Language\Text;
?>

<div id="splms" class="splms splms-view-purchases">
	<?php if ($this->purchases) { ?>
	<!-- START purchased courses list -->
	<table class="table table-bordered table-striped">
	  	<thead>
			<th>#</th>
			<th><?php echo Text::_('COM_SPLMS_PURCHASED_COURSE_NAME'); ?></th>
			<th><?php echo Text::_('JSTATUS'); ?></th>
		</thead>

		<?php foreach ($this->purchases as $key=>$purchased_course) { 
			// Check course purchase status
		  	$course_status = ($purchased_course->published == 1) ? '<span class="label label-success">' . Text::_('JENABLED') . '</span>' : '<span class="label label-danger">' . Text::_('COM_SPLMS_PURCHASED_DISABLED_PENDING') . '</span>';
			?>
		<tr>
			<td width="20">
				<?php echo $key + 1; ?>
			</td>

			<td>
				<a href="<?php echo $purchased_course->url; ?>">
					<?php echo $purchased_course->course_name; ?>
				</a>
			</td>
			<td width="100">
				<?php echo $course_status; ?>
			</td>
		</tr>
		<?php } ?>
	</table>
	<!-- END:: purchased courses list -->
	<?php } ?>


	<?php if ($this->quiz_results) { ?>
	<!-- START Quiz Result list -->
	<table class="table table-bordered table-striped">
	  	<thead>
			<th>#</th>
			<th><?php echo Text::_('COM_SPLMS_QUIZ_NAME'); ?></th>
			<th><?php echo Text::_('COM_SPLMS_COURSE_NAME'); ?></th>
			<th><?php echo Text::_('COM_SPLMS_QUIZ_RESULT'); ?></th>
		</thead>

		<?php foreach ($this->quiz_results as $key=>$quiz_result) { ?>
		<tr>
			<td width="20">
				<?php echo $key + 1; ?>
			</td>

			<td width="30%">
				<?php echo $quiz_result->quiz_name; ?>
			</td>

			<td>
				<a href="<?php echo $quiz_result->course_url; ?>">
					<?php echo $quiz_result->course_name; ?>
				</a>
			</td>

			<td width="120">
				<?php echo $quiz_result->point . '/' .$quiz_result->total_marks . ' (' . round((($quiz_result->point/$quiz_result->total_marks)*100), 2) . '%)'; ?>
			</td>
		</tr>
		<?php } ?>
	</table>
	<!-- END:: Quiz Result list -->
	<?php } ?>


	<?php if ($this->user_certificates) { ?>
	<!-- START Quiz Result list -->
	<table class="table table-bordered table-striped">
	  	<thead>
			<th>#</th>
			<th><?php echo Text::_('COM_SPLMS_CERTIFICATE_NAME'); ?></th>
			<th><?php echo Text::_('COM_SPLMS_COURSE_INSTRUCTOR'); ?></th>
		</thead>

		<?php foreach ($this->user_certificates as $key=> $certicate) { ?>
		<tr>
			<td width="20">
				<?php echo $key + 1; ?>
			</td>

			<td width="30%">
				<a href="<?php echo $certicate->certificate_url; ?>">
					<?php echo $certicate->title; ?>
				</a>
			</td>

			<td>
				<?php echo $certicate->instructor; ?>
			</td>
		</tr>
		<?php } ?>
	</table>
	<!-- END:: Quiz Result list -->
	<?php } ?>

</div>