<?php

/**
 * @package com_splms
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2022 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
 */

// No Direct Access
defined('_JEXEC') or die('Resticted Aceess');

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Layout\LayoutHelper;

HTMLHelper::_('jquery.framework');
$input = Factory::getApplication()->input;
$doc = Factory::getDocument();
$user = Factory::getUser();
?>

<div id="splms" class="splms view-splms-course course-details">
	<div class="splms-course">
		<div class="splms-course-details-title">
			<h2 class="course-title">
				<?php echo $this->item->title; ?>
			</h2>
		</div>

		<div class="splms-course-details-course-level">
			<?php if ($this->item->level) { ?>
				<span class="course-level"><?php echo $this->item->level; ?></span>
			<?php } ?>
		</div>

		<div class="splms-course-details-course-short-info">
			<?php if ($this->item->short_description) { ?>
				<div class="splms-section splms-course-intro">
					<div class="splms-course-introtext">
						<?php echo $this->item->short_description; ?>
					</div>
				</div>
			<?php } ?>
		</div>

		<div class="splms-course-details-course-features">
			<?php if (isset($this->item->course_infos) && $this->item->course_infos && count($this->item->course_infos)) { ?>
				<div class="splms-course-information">
					<div class="splms-course-sessions-meta">
						<?php foreach ($this->item->course_infos as $course_info) { ?>
							<div class="splms-course-info-media-wrap">
								<div class="splms-course-info-media-type">
									<?php if (!empty($course_info['icon_image'])) {
										if ($course_info['icon_image'] == 'icon') { ?>
											<i class="<?php echo $course_info['icon'] ?> course_info-icon"></i>
										<?php } elseif ($course_info['icon_image'] == 'image') { ?>
											<img src="<?php echo Uri::root() . $course_info['image']; ?>" alt="" class="mcourse_info-image">
									<?php }
									}   ?>
								</div>
								<div>
									<h5><?php echo $course_info['info_text'] ?></h5>
									<span class="count"><?php echo $course_info['info_number'] ?></span>
								</div>
							</div>
						<?php } ?>
					</div>
				</div>
			<?php } ?>
		</div>

		<div class="splms-course-banner">
			<?php if (!empty($this->item->video_url)) { ?>
				<div class="splms-course-video">
					<?php echo LayoutHelper::render('player', array('video' => $this->item->video_url, 'thumbnail' => $this->item->image)); ?>
				</div>
			<?php } elseif ($this->item->image) { ?>
				<div class="course-thumbnail">
					<img class="splms-img-responsive" src="<?php echo $this->item->image; ?>" alt="<?php echo $this->item->title; ?>">
				</div>
			<?php } ?>

			<?php
			if ($this->item->price == 0) {
				echo '<span class="splms-badge-free">' . Text::_('COM_SPLMS_FREE') . '</span>';
			}
			?>
		</div>

		<div class="row">
			<div class="splms-col-md-8">
				<!-- Navigation -->
				<div class="nav-area ">
					<div class="container">
						<ul>
							<li><a href="#course-about"><?php echo Text::_('COM_SPLMS_COURSE_ABOUT'); ?></a></li>
							<li><a href="#course-lessons"><?php echo Text::_('COM_SPLMS_COURSE_LESSONS'); ?></a></li>
							<li><a href="#course-instructor"><?php echo Text::_('COM_SPLMS_COURSE_INSTRUCTOR'); ?></a></li>
							<li><a href="#course-reviews"><?php echo Text::_('COM_SPLMS_COURSE_REVIEWS'); ?></a></li>
						</ul>
					</div>
				</div>
				<!-- Navigation -->

				<?php if ($this->item->description) { ?>
					<div id="course-about" class="splms-course-description">
						<?php //echo HTMLHelper::_('content.prepare', $this->item->description);
						echo $this->item->description;
						?>
					</div>
				<?php } ?>

				<?php if ((!empty($this->item->topics) && count($this->item->topics)) || (!empty($this->item->lessons) && count($this->item->lessons))) { ?>
					<div id="course-lessons" class="course-lessons">
						<h3><?php echo Text::_('COM_SPLMS_LESSONS'); ?></h3>
						<?php if (!empty($this->item->topics) && count($this->item->topics)) { ?>
							<div id="topicAccordion">
								<?php foreach ($this->item->topics as $key => $topic) { ?>
									<div class="card">
										<div class="card-header" id="topicId<?php echo $key; ?>" data-toggle="collapse" data-target="#topicBody<?php echo $key; ?>" data-bs-toggle="collapse" data-bs-target="#topicBody<?php echo $key; ?>" aria-expanded="true">
											<span class="splms-topic-title"><?php echo $topic->title; ?></span>
										</div>

										<div id="topicBody<?php echo $key; ?>" class="collapse <?php echo $key == 0 ? 'show collapse in' : ''; ?>" data-parent="#topicAccordion" data-bs-parent="#topicAccordion">
											<div class="card-body">
												<ul class="list-unstyled">
													<?php foreach ($topic->lessons as $lesson) { ?>
														<?php echo LayoutHelper::render('course.content', array('contents' => array($lesson, $this->item->price, $this->isAuthorised, 0))); ?>
													<?php } ?>
												</ul>
											</div>
										</div>
									</div>
								<?php } ?>
							</div>
						<?php } else { ?>
							<ul class="list-unstyled">
								<?php foreach ($this->item->lessons as $key => $lesson) { ?>
									<?php echo LayoutHelper::render('course.content', array('contents' => array($lesson, $this->item->price, $this->isAuthorised, 0))); ?>
								<?php } ?>
							</ul>
						<?php } ?>
					</div>

					<div class="splms-course-announcements splms-section">
    					<h3 class="splms-title">Mural de Avisos</h3>

					<?php
						echo LayoutHelper::render(
							'announcements.list',
							['items' => $this->announcements]
						);
					?>
					</div>

				<?php } ?>

				<!-- Has teacher -->
				<?php if (!empty($this->teachers)) { ?>
					<div id="course-instructor" class="splms-course-teachers">
						<h3><?php echo Text::_('COM_SPLMS_MEET_OUR_COURSE_TEACHER'); ?></h3>
						<div class="splms-row">
							<?php foreach ($this->teachers as $teacher) { ?>
								<div class="splms-course-teacher">
									<a href="<?php echo $teacher->url; ?>"><img src="<?php echo $teacher->image; ?>" alt="<?php echo $teacher->title; ?>"></a>
									<h4><a href="<?php echo $teacher->url; ?>"><?php echo $teacher->title; ?></a></h4>
									<small>
										<?php
										echo $teacher->specialist_in;
										?>
									</small>
									<div class="splms-teacher-bio">
										<?php echo $teacher->description; ?>
									</div>
								</div>
							<?php } ?>
						</div>
					</div>
				<?php } ?>
				<!-- END::  teacher -->

				<?php if ($this->review) { ?>
					<div id="course-reviews" class="user-reviews">
						<div class="reviews-menu">
							<div class="title-wrap">
								<h3 class="title"><?php echo Text::_('COM_SPLMS_REVIEWS'); ?></h3>
								<div class="myreviews-wrap">
									<ul class="list-inline list-style-none">
										<?php if ($this->myReview) { ?>
											<li><a id="splms-my-review" class="btn btn-primary" href="#"><i class="splms-icon-write"></i> <?php echo Text::_('COM_SPLMS_EDIT_REVIEW'); ?></a></li>
										<?php } ?>

										<?php if ($user->guest) { ?>
											<li><a href="<?php echo Route::_('index.php?option=com_users&view=login&return=' . base64_encode('index.php?option=com_splms&view=course&id=' . $this->item->id . ':' . $this->item->alias . SplmsHelper::getItemid('courses'))); ?>" class="btn btn-primary"><i class="fa fa-pencil-square-o"></i> <?php echo Text::_('COM_SPLMS_LOGIN_TO_REVIEW'); ?></a></li>
										<?php } ?>
									</ul>
								</div>
							</div>

							<div class="reviews-wrapper">
								<d iv class="reviews-status">
									<?php if (isset($this->ratings) && $this->ratings->count) {
										$rating = $this->ratings->total / $this->ratings->count;
										$rating = number_format($rating, 1);
									} else {
										$rating = 0;
									} ?>
									<span class="total"><?php echo $rating; ?></span>
									<div class="sp-lms-rating ">
										<?php echo LayoutHelper::render('review.ratings', array('rating' => $rating)); ?>
									</div>
									<p class="avg-rating"><?php echo Text::_('COM_SPLMS_REVIEW_AVARAGE_RATING'); ?></p>
								</d>
								<div class="total-reviews">
									<div class="sp-lms-rating ">
										<?php echo LayoutHelper::render('review.ratings', array('rating' => $rating)); ?>
									</div>
									<?php echo round($rating / (5 / 100), 2); ?>% <span class="total-review"><?php echo count($this->reviews); ?> Ratings</span>
								</div>
							</div>
						</div>
						<!--/.reviews-menu -->
						<div class="clearfix"></div>

						<?php echo LayoutHelper::render('review.form', array('review' => $this->myReview, 'item_id' => $this->item->id, 'url' => 'index.php?option=com_splms&view=course&id=' . $this->item->id . ':' . $this->item->alias . SplmsHelper::getItemid('courses'))); ?>

						<div id="reviews">
							<?php foreach ($this->reviews as $key => $this->review) {
								echo LayoutHelper::render('review.review', array('review' => $this->review));
							} ?>
						</div>

						<?php if ($this->showLoadMore) { ?>
							<a id="splms-load-review" class="btn btn-link btn-lg btn-block" data-item_id="<?php echo $this->item->id; ?>" href="#"><i class="fa fa-refresh"></i> <?php echo Text::_('COM_SPLMS_REVIEW_LOAD_MORE'); ?></a>
						<?php } ?>
					</div>
					<!--/.user-reviews-->
				<?php } ?>
			</div>

			<div class="splms-col-md-4">
				<!-- start course-header -->
				<div class="course-header clearfix">
					<div class="course-short-info">

						<div class="apply-now">
							<div class="price_info">
								<?php echo $this->coursePrice; ?>
							</div>
							<?php if (($this->item->price != 0) && ($this->isAuthorised == '')) { ?>
								<a class="btn btn-primary" id="addtocart" data-course="<?php echo $this->item->id; ?>" data-user="<?php echo $this->user->id; ?>" data-price="<?php echo $this->item->price; ?>" href="#">
									<i class="splms-icon"></i>
									<?php echo Text::_('COM_SPLMS_BUY_NOW'); ?>
								</a>
							<?php } elseif ($this->isAuthorised != '') { ?>
								<a class="btn btn-primary" href="javascript:void(0);">
									<i class="splms-icon"></i>
									<?php echo Text::_('COM_SPLMS_PURCHASED'); ?>
								</a>
							<?php } ?>
						</div>

						<ul class="course-info">
							<?php if (!empty($this->teachers)) { ?>
								<li class="splms-course-teacher">
									<?php if ($this->total_teachers == 1) { ?>
										<i class="splms-icon-teacher"></i>
										<?php foreach ($this->teachers as $teacher) { ?>
											<a href="<?php echo $teacher->url; ?>">
												<?php echo $teacher->title; ?>
											</a>
										<?php } ?>
									<?php } elseif ($this->total_teachers  > 1) { ?>
										<i class="splms-icon-users"></i>
										<a href="javascipt:void(0);" id="splms-multiteacher-toogle"><?php echo Text::_('COM_SPLMS_COMMON_MULTIPLE_TEACHERS'); ?></a>
										<ul class="splms-course-multi-teachers">
											<?php foreach ($this->teachers as $teacher) { ?>
												<li><a href="<?php echo $teacher->url; ?>"><?php echo $teacher->title; ?></a></li>
											<?php } ?>
										</ul>
									<?php } ?>
								</li>
							<?php } ?>

							<li class="total-hours">
								<i class="splms-icon-video-cam"></i>
								<?php echo $this->item->duration; ?>
							</li>
							<li class="total-duration">
								<i class="splms-icon-video-cam"></i>
								<?php echo $this->item->courseDuration; ?>
							</li>

							<?php if ($this->total_enrolled) { ?>
								<li class="total-studnets"><i class="splms-icon-graduate"></i> <?php echo $this->total_enrolled; ?></li>
							<?php }
							if ($this->item->lessonsCount) { ?>
								<li class="total-lessons"><i class="splms-icon-book"></i> <?php echo $this->item->lessonsCount; ?> <?php echo Text::_('COM_SPLMS_COMMON_LESSONS'); ?></li>
							<?php }
							if (isset($this->item->admission_deadline) && $this->item->admission_deadline != '0000-00-00 00:00:00') { ?>
								<li class="admission-deadline" title="<?php echo Text::_('COM_SPLMS_ADMISSION_DEADLINE'); ?>"><i class="splms-icon-calendar"></i> <?php echo HTMLHelper::_('date', $this->item->admission_deadline, 'DATE_FORMAT_LC3'); ?></li>
							<?php } ?>
							<li class="course-details-total-review">
								<div class="reviews-status">
									<i class="fa fa-star"></i>
									<?php if (isset($this->ratings) && $this->ratings->count) {
										$rating = $this->ratings->total / $this->ratings->count;
										$rating = number_format($rating, 1);
									} else {
										$rating = 0;
									} ?>
									<span class="total"><?php echo $rating; ?></span>
									<span class="title">(<?php echo $this->ratings->count; ?> <?php echo Text::_('COM_SPLMS_RATINGS'); ?>)</span>
								</div>
							</li>
						</ul>
					</div>
				</div> <!-- end course-header -->

				<div class="splms-course-introduction">
					<?php if ($this->params->get('course_social_share', 1)) { ?>
						<div class="splms-section splms-course-social-share">
							<h3 class="splms-section-title"><?php echo Text::_('COM_SPLMS_SOCIAL_SHARE'); ?></h3>
							<?php echo LayoutHelper::render('social_share', array('url' => $this->item->link, 'title' => $this->item->title)); ?>
						</div>
					<?php } ?>
				</div>
			</div>
		</div>

		<!-- Has quiz -->
		<?php if (!empty($this->quizzes) && count($this->quizzes) && $this->quizzes) { ?>
			<div class="splms-course-quizzes">
				<h3><?php echo Text::_('COM_SPLMS_QUIZ'); ?></h3>
				<ul class="list-unstyled">
					<?php foreach ($this->quizzes as $quiz) {
						$qtype = ($quiz->quiz_type == 1) ? Text::_('COM_SPLMS_PAID') : Text::_('COM_SPLMS_FREE');
					?>
						<li>
							<span>
								<i class="fa fa-question-circle"></i>
								<a href="<?php echo $quiz->url; ?>">
									<?php echo $quiz->title; ?>
								</a>
							</span>
							<span class="pull-right">
								<?php echo $qtype; ?>
							</span>
						</li>
					<?php } // END:: foreach 
					?>
				</ul>
			</div>
		<?php } ?>
		<!-- END::  quiz -->

		<?php if (isset($this->item->course_schedules) && $this->item->course_schedules && count($this->item->course_schedules) && $this->item->course_schedules) { ?>
			<div class="splms-course-class-rotuines">
				<div class="splms-class-routines">
					<h3 class="splms-title"><?php echo Text::_('COM_SPLMS_CLASS_TIMES'); ?></h3>
					<table class="table table-bordered">
						<thead>
							<tr>
								<?php foreach ($this->schedule_days_lang as $schedule_day) { ?>
									<th><?php echo $schedule_day; ?></th>
								<?php } ?>
							</tr>
						</thead>
						<tbody>
							<tr>
								<?php foreach ($this->schedule_days as $schedule_day) { ?>
									<td class="splms-class-routines-day-<?php echo $schedule_day; ?>">
										<?php if (!in_array($schedule_day, $this->hasdays)) { ?>
											<div class="splms-class-routines-text no-schedule">
											<?php } ?>
											<?php foreach ($this->item->course_schedules as $course_schedule) { ?>
												<?php if ($schedule_day == $course_schedule['day']) { ?>
													<div class="splms-class-routines-text has-schedule">
														<?php echo $course_schedule['text']; ?>
													</div>
												<?php } ?>
											<?php } // foreach course_schedules
											?>
									</td>
								<?php } //foreach schedule_days 
								?>
							</tr>
						</tbody>
					</table>
				</div>
			</div> <!-- //.splms-course-class-rotuines -->
		<?php } ?>

		<?php if ($this->show_related_courses) {
			if (isset($this->related_courses) && is_array($this->related_courses)) { ?>
				<div class="splms-similar-courses">
					<h3 class="splms-title"><?php echo Text::_('COM_SPLMS_SIMILAR_CLASSES'); ?></h3>
					<?php if (count($this->related_courses) > 0) { ?>
						<div class="splms-courses-list splms">
							<div class="splms-row">
								<?php foreach ($this->related_courses as $related_course) { ?>
									<div class="splms-col-sm-6 splms-col-md-4">
										<div class="splms-course">
											<a href="<?php echo $related_course->url; ?>">
												<img src="<?php echo $related_course->thumb; ?>" class="splms-course-img splms-img-responsive" />
											</a>
											<div class="splms-content-wrap">
												<h4 class="splms-course-title">
													<a href="<?php echo $related_course->url; ?>"><?php echo $related_course->title; ?></a>
												</h4>
												<div class="splms-course-cat">
													<?php echo $related_course->category_name; ?>
												</div>
												<div class="splms-course-time">
													<?php echo $related_course->course_time; ?>
												</div>
												<div class="splms-course-details-btn">
													<a href="<?php echo $related_course->url; ?>" class="btn btn-primary"><?php echo Text::_('COM_SPLMS_DETAILS'); ?></a>
												</div>
											</div> <!-- /.splms-content-wrap -->
										</div> <!-- /.splms-course -->
									</div> <!-- //.splms-col-sm-4 -->
								<?php } ?>
							</div> <!-- /.splms-row -->
						</div><!-- //.splms-courses-list -->
					<?php } else { ?>
						<p><?php echo Text::_('COM_SPLMS_NO_ITEMS_FOUND'); ?></p>
					<?php } ?>
				</div> <!-- //.splms-similar-courses -->
			<?php } ?>
		<?php } ?>
	</div>
</div>