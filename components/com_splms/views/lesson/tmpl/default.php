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
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Factory;

// GUIDEWAY CUSTOM - 2025-11-11 - Joshua - Carregar sistema de progresso
$document = Factory::getDocument();
$document->addScript(JURI::root() . 'components/com_splms/assets/js/lesson-progress.js');

?>

<div id="splms" class="splms splms-lessons splms-lesson-details">

	<?php if(!empty($this->item->video_url)) { ?>
		<div class="lesson-video">
			<?php echo LayoutHelper::render('player', array('video'=> $this->item->video_url, 'thumbnail' => $this->item->vdo_thumb)); ?>
		</div>
	<?php } elseif($this->item->vdo_thumb) { ?>
		<div class="lesson-thumbnail">
			<img class="splms-img-responsive" src="<?php echo $this->item->vdo_thumb; ?>" alt="<?php echo $this->item->title; ?>">
		</div>
	<?php } ?>

	<div class="splms-lesson-description item-content">
		<h2><?php echo $this->item->title; ?></h2>
		<div class="splms-lesson-description">
			<?php echo $this->item->description; ?>
		</div>
	</div>

	<?php if (isset($this->item->attachment) && $this->item->attachment) { ?>
	<div class="item-content splms-lesson-attachment-wrapper">
		<a class="btn btn-default attachment-button" target="_blank" href="<?php echo Uri::root(). $this->item->attachment; ?>">
			<?php echo Text::_('COM_SPLMS_LESSON_DOWNLOAD_ATTACHMENT')?>
		</a>
	</div>
	<?php } ?>
	
	<!-- Has lesson -->
	<?php if (!empty($this->item->topics) && count($this->item->topics) && $this->item->topics) {?>
		<!-- start course-lessons  -->
		<div class="course-lessons">
			<h3><?php echo Text::_('COM_SPLMS_LESOSNS_LIST'); ?></h3>
			<div id="topicAccordion">
				<?php foreach ($this->item->topics as $key => $topic) { ?>
					<?php if (!empty($topic->lessons) && count($topic->lessons) && $topic->lessons) { ?>
						<div class="card">
							<div class="card-header" id="topicId<?php echo $key; ?>" data-toggle="collapse" data-target="#topicBody<?php echo $key; ?>" data-bs-toggle="collapse" data-bs-target="#topicBody<?php echo $key; ?>" aria-expanded="true">
								<span class="splms-topic-title"><?php echo $topic->title; ?></span>
							</div>
							<?php $selectedLesson = in_array($this->item->id, array_column($topic->lessons, 'id'));?>
							<div id="topicBody<?php echo $key; ?>" class="collapse <?php echo $selectedLesson ? 'show collapse in' : ''; ?>" data-parent="#topicAccordion" data-bs-parent="#topicAccordion">
								<div class="card-body">
									<ul class="lessons list-unstyled">
										<?php foreach ($topic->lessons as $lesson) { ?>
											<?php echo LayoutHelper::render('lesson', array('contents' => [$lesson, $this->courese->price, $this->isAuthorised, $this->item->id])); ?>
										<?php } ?>
									</ul>
								</div>
							</div>
						</div>
					<?php } ?>
				<?php } ?>
			</div>
		</div>
	<?php }?>

	<?php if(isset($this->teacher) && $this->teacher){ ?>
		<div class="splms-lesson-teacher-wrapper">
			<h2 class="splms-lesson-teacher-info-title"><?php echo Text::_('COM_SPLMS_COMMON_TEACHER_INFO'); ?></h2>
			<div class="splms-row">
				<div class="splms-teacher-img-wraper splms-col-sm-4 splms-col-md-3">
					<div class="splms-teacher-thumb">
						<img src="<?php echo $this->teacher->image; ?>" class="splms-img-responsive img-thumbnail" alt="<?php echo $this->teacher->title; ?>">
					</div>
				</div>
				<div class="splms-teacher-info-wraper splms-col-sm-8 splms-col-md-9">
					<h3 class="splms-lesson-teacher-name">
						<a href="<?php echo $this->teacher->url; ?>">
						<?php echo $this->teacher->title;?>
						</a>
					</h3>
					
					<ul class="teachers-details list-unstyled">
						<?php if (isset($this->teacher->specialist_in) && $this->teacher->specialist_in) {?>
							<li>
								<?php echo Text::_('COM_SPLMS_COMMON_SPECIALIST_IN'). ': '; ?>
								<?php  
									$specialist_in = (array)json_decode($this->teacher->specialist_in);
									foreach($specialist_in as $specialist)
									{
										echo (!next($specialist_in)) ? $specialist->specialist_text : $specialist->specialist_text . ", ";
									}
								?>
							</li>
						<?php } if (isset($this->teacher->experience) && $this->teacher->experience) {?>
							<li> 
								<?php echo Text::_('COM_SPLMS_COMMON_EXPERIENCE'). ': '; ?> 
								<?php echo $this->teacher->experience; ?>
							</li>
						<?php } if (isset($this->teacher->website) && $this->teacher->website) {?>
						<li>
							<?php echo Text::_('COM_SPLMS_COMMON_WEBSITE'). ': '; ?>
							<a href="<?php echo $this->teacher->website;?>">
								<?php echo $this->teacher->website; ?>
							</a>
						</li>
						<?php } ?>

						<li class="splms-teacher-social-icon">
							<ul class="list-unstyled list-inline splms-social-icon-list">
								<?php if (isset($this->teacher->social_facebook) && $this->teacher->social_facebook) {?>
								<li>
									<a href="<?php echo $this->teacher->social_facebook;?>">
										<span class="splms-icon-facebook"></span>
									</a>
								</li>
								<?php } ?>

								<?php if (isset($this->teacher->social_linkedin) && $this->teacher->social_linkedin) {?>
								<li>
									<a href="<?php echo $this->teacher->social_linkedin;?>">
										<span class="splms-icon-linkedin"></span>
									</a>
								</li>
								<?php } ?>

								<?php if (isset($this->teacher->social_twitter) && $this->teacher->social_twitter) {?>
								<li>
									<a href="<?php echo $this->teacher->social_twitter;?>">
										<span class="splms-icon-twitter"></span>
									</a>
								</li>
								<?php } ?>

								<?php if (isset($this->teacher->social_youtube) && $this->teacher->social_youtube) {?>
								<li>
									<a href="<?php echo $this->teacher->social_youtube;?>">
										<span class="splms-icon-youtube"></span>
									</a>
								</li>
								<?php } ?>
							</ul>
						</li>
						<?php if($this->teacher_description) { ?>
						<li>
							<p>
								<?php echo $this->teacher_description; ?>
							</p>
						</li>
						<?php } ?>
					</ul>
				</div>
			</div>
		</div>
	<?php } ?>
	
	<div class="splms-lesson-completed-lesson-wrapper">
		<?php if($this->user->guest) {
			$link =  base64_encode(Uri::getInstance()->toString());
			$login_link = Route::_('index.php?option=com_users&view=login'. SplmsHelper::getItemid('login') .'&return=' . $link);
		?>
			<a class="btn btn-primary" href="<?php echo $login_link; ?>">
				<?php echo Text::_('COM_SPLMS_LOGIN_TO_COMPLETE'); ?>
			</a>
		<?php } elseif(!$this->has_complete_lesson) {?>
			<form id="splms-completed-item-form">
				<input type="hidden" name="user_id" value="<?php echo $this->user->id; ?>">
				<input type="hidden" name="item_id" value="<?php echo $this->item->id; ?>">
				<input type="hidden" name="item_type" value="lesson">
				<a class="btn btn-primary" id="splms-completed-item" href="#">
					<?php echo Text::_('COM_SPLMS_LESSON_COMPLETE'); ?>
				</a>
			</form>
		<?php } else { ?>
			<a class="btn btn-primary" id="splms-completed-item" href="#">
				<?php echo Text::_('COM_SPLMS_LESSON_COMPLETED'); ?>
			</a>
		<?php } ?>
	</div>

</div> <!-- /#splms -->