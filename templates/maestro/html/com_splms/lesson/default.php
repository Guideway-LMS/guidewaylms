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
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;

// Carrega JS e CSS da notificação (Sprint 4 - Iris)
$doc = Factory::getDocument();
$doc->addScript(Uri::root() . 'media/gw-progress-alert/js/alerta-conclusao.js');
$doc->addStyleSheet(Uri::root() . 'media/gw-progress-alert/css/alerta-conclusao.css');

// GUIDEWAY CUSTOM - Joshua - Carregar handler de conclusão de aula
$doc->addScript(Uri::root() . 'components/com_splms/assets/js/lesson-complete-handler.js');
?>
$doc->addStyleSheet(Uri::root() . 'media/gw-progress-alert/css/alerta-conclusao.css');

?>

<div id="splms" class="splms splms-lessons splms-lesson-details">

	<div class="row">
		<div class="col-md-7">
			<div class="splms-lesson-video-wrapper">

				<?php if (!empty($this->item->video_url)) { ?>
					<div class="lesson-video">
						<?php echo LayoutHelper::render('player', array('video' => $this->item->video_url, 'thumbnail' => $this->item->vdo_thumb)); ?>
					</div>
				<?php } elseif ($this->item->vdo_thumb) { ?>
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
						<a class="btn btn-default attachment-button" target="_blank" href="<?php echo Uri::root() . $this->item->attachment; ?>">
							<?php echo Text::_('COM_SPLMS_LESSON_DOWNLOAD_ATTACHMENT') ?>
						</a>
					</div>
				<?php } ?>
			</div>

		</div>
		<div class="col-md-5">
			<?php if (!empty($this->lessons) && count($this->lessons) && $this->lessons) { ?>
				<!-- start course-lessons  -->
				<div class="course-lessons">
					<h3><?php echo Text::_('COM_SPLMS_LESOSNS_LIST'); ?></h3>
					<ul class="lessons list-unstyled">
						<?php foreach ($this->lessons as $lesson) { ?>
							<?php $active_lesson = ($this->item->id == $lesson->id) ? ' active' : ''; ?>
							<?php if ($lesson->lesson_type == 0 || $this->isAuthorised != '' || $this->courese->price == 0) : ?>
								<li class="lesson<?php echo $active_lesson; ?>">
									<?php if (!empty($lesson->video_url)) : ?>
										<span>
											<a href="<?php echo $lesson->lesson_url; ?>">
												<?php echo $lesson->title; ?>
											</a>
										</span>
										<span class="pull-right lesson-duration">
											<span><?php echo Text::_('COM_SPLMS_COMMON_DURATION') . Text::_(': '); ?></span>
											<?php echo $lesson->video_duration; ?>
										</span>
									<?php else : ?>
										<a href="<?php echo $lesson->lesson_url; ?>">
											<i class="splms-icon-book"></i>
											<?php echo $lesson->title; ?>
										</a>
									<?php endif; ?>
								</li>
							<?php else : ?>
								<li class="lesson splms-lesson-unauthorised">
									<span>
										<i class="splms-icon-book"></i>
										<i class="splms-icon-lock"></i>
										<?php echo $lesson->title; ?>
									</span>
									<span class="pull-right lesson-duration">
										<span><?php echo Text::_('COM_SPLMS_COMMON_DURATION') . Text::_(': '); ?></span>
										<?php echo $lesson->video_duration; ?>
									</span>
								</li>
							<?php endif; // end else
							?>

						<?php } ?>
					</ul>
				</div>
			<?php } ?>

		</div>
	</div>




	<!-- Has lesson -->

	<div class="splms-lesson-completed-lesson-wrapper">
		<?php if ($this->user->guest) {
			$link =  base64_encode(Uri::getInstance()->toString());
			$login_link = Route::_('index.php?option=com_users&view=login' . SplmsHelper::getItemid('login') . '&return=' . $link);
		?>
			<a class="btn btn-primary" href="<?php echo $login_link; ?>">
				<?php echo Text::_('COM_SPLMS_LOGIN_TO_COMPLETE'); ?>
			</a>
		<?php } elseif (!$this->has_complete_lesson) { ?>
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
