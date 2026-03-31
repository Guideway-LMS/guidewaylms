<?php
/**
 * @package com_splms
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2022 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
 * GUIDEWAY CUSTOM - Restaurado Info Original + Barra Comentada (V23)
 */

// No Direct Access
defined('_JEXEC') or die('Restricted Aceess');

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

Factory::getDocument()->addStyleSheet(Uri::root() . 'templates/maestro/css/splms-progress.css');

HTMLHelper::_('jquery.framework');
$user = Factory::getUser();
$isEnrolled = ($this->isAuthorised != '');
$mainColClass = $isEnrolled ? 'splms-col-md-12' : 'splms-col-md-8';

// --- LOGICA DE CONCLUSÃO DO CURSO ---
$isCourseCompleted = false;
if (!$user->guest) {
    BaseDatabaseModel::addIncludePath(JPATH_SITE . '/components/com_splms/models');
    $lessonsModel = BaseDatabaseModel::getInstance('Lessons', 'SplmsModel');
    if ($lessonsModel) {
        $isCourseCompleted = $lessonsModel::hasCompleted($this->item->id, $user->id, 'course');
    }
}

// --- LOGICA DO CERTIFICADO ---
$submission_id = 0;
if (!$user->guest) {
    $db = Factory::getDbo();
    $queryCert = $db->getQuery(true)
        ->select('s.id')
        ->from($db->quoteName('#__splms_submissions', 's'))
        ->join('INNER', $db->quoteName('#__splms_lessons', 'l') . ' ON ' . $db->quoteName('l.id') . ' = ' . $db->quoteName('s.lesson_id'))
        ->where($db->quoteName('s.user_id') . ' = ' . (int)$user->id)
        ->where($db->quoteName('l.course_id') . ' = ' . (int)$this->item->id)
        ->order($db->quoteName('s.id') . ' DESC');
    $db->setQuery($queryCert, 0, 1); 
    $submission_result = $db->loadResult();
    if ($submission_result) { $submission_id = $submission_result; }
}
?>

<div id="splms" class="splms view-splms-course course-details">
  <div class="splms-course">
    
    <div class="splms-course-details-title">
      <h2 class="course-title"><?php echo $this->item->title; ?></h2>
    </div>

    <div class="splms-course-details-course-level">
      <?php if ($this->item->level) { ?>
        <span class="course-level"><?php echo $this->item->level; ?></span>
      <?php } ?>
    </div>

    <?php if ($this->item->short_description && !$isEnrolled) { ?>
      <div class="splms-course-details-course-short-info">
        <div class="splms-section splms-course-intro">
          <div class="splms-course-introtext">
            <?php echo $this->item->short_description; ?>
          </div>
        </div>
      </div>
    <?php } ?>

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
                  } ?>
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

    <?php if (!$isEnrolled) : ?>
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
        <?php if ($this->item->price == 0) { echo '<span class="splms-badge-free">' . Text::_('COM_SPLMS_FREE') . '</span>'; } ?>
      </div>
    <?php endif; ?>

    <div class="row">
      <div class="<?php echo $mainColClass; ?>">
        
        <div class="nav-area ">
          <div class="container">
            <ul class="gw-course-nav">
              <li><a href="#course-about">📌 <?php echo Text::_('COM_SPLMS_COURSE_ABOUT'); ?></a></li>
              <li><a href="#course-lessons">📚 <?php echo Text::_('COM_SPLMS_COURSE_LESSONS'); ?></a></li>
              <li><a href="#course-instructor">👨‍🏫 <?php echo Text::_('COM_SPLMS_COURSE_INSTRUCTOR'); ?></a></li>
              <li><a href="#course-reviews">⭐ <?php echo Text::_('COM_SPLMS_COURSE_REVIEWS'); ?></a></li>
              
              <?php if (!$user->guest && $isEnrolled) : ?>
              <li>
                <?php 
                    $certDownloadUrl = Route::_('index.php?option=com_splms&task=certificate.generate&submission_id=' . $submission_id . '&course_id=' . (int)$this->item->id);
                ?>
                <a href="<?php echo $certDownloadUrl; ?>" id="certificate-btn" class="certificate-btn enabled" target="_blank">
                  🎓 Certificado
                </a>
              </li>
              <?php endif; ?>
            </ul>
          </div>
        </div>

        <?php if (!Factory::getUser()->guest && $this->isAuthorised) : ?>
          <div class="splms-course-announcements splms-section guideway-announcements">
            <h3 class="splms-title guideway-section-title"><i class="fa fa-bullhorn guideway-icon" aria-hidden="true"></i> Mural de Avisos</h3>
            <?php echo LayoutHelper::render('announcements.list', ['items' => $this->announcements]); ?>
          </div>
        <?php endif; ?>

        <?php if ($this->item->description && !$isEnrolled) { ?>
          <div id="course-about" class="splms-course-description splms-section guideway-course-section">
            <?php echo $this->item->description; ?>
          </div>
        <?php } ?>

        <?php if ((!empty($this->item->topics) && count($this->item->topics)) || (!empty($this->item->lessons) && count($this->item->lessons))) { ?>
          <div id="course-lessons" class="course-lessons splms-section guideway-course-section">
            <h3><i class="fa fa-book guideway-icon" aria-hidden="true"></i><?php echo Text::_('COM_SPLMS_LESSONS'); ?></h3>
            <div id="topicAccordion">
                <?php if (!empty($this->item->topics)) : ?>
                    <?php foreach ($this->item->topics as $key => $topic) : ?>
                      <div class="card">
                        <div class="card-header" id="topicId<?php echo $key; ?>" data-toggle="collapse" data-target="#topicBody<?php echo $key; ?>" data-bs-toggle="collapse" data-bs-target="#topicBody<?php echo $key; ?>" aria-expanded="true">
                          <span class="splms-topic-title"><?php echo $topic->title; ?></span>
                        </div>
                        <div id="topicBody<?php echo $key; ?>" class="collapse <?php echo $key == 0 ? 'show collapse in' : ''; ?>" data-parent="#topicAccordion">
                          <div class="card-body">
                            <ul class="list-unstyled">
                              <?php foreach ($topic->lessons as $lesson) { 
                                  echo LayoutHelper::render('course.content', array('contents' => array($lesson, $this->item->price, $this->isAuthorised, 0))); 
                              } ?>
                            </ul>
                          </div>
                        </div>
                      </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
          </div>
        <?php } ?>        <?php if ($isEnrolled) : ?>
            <div class="splms-course-forum splms-section guideway-course-section mt-4" style="margin-top: 70px !important;">
              <h3 data-bs-toggle="collapse" data-bs-target="#forumAccordionBody" style="cursor: pointer; display: flex; justify-content: space-between; align-items: center;">
                <span><i class="fa fa-comments guideway-icon" aria-hidden="true"></i>Fórum de Dúvidas</span>
                <i class="fa fa-chevron-down"></i>
              </h3>
              <div id="forumAccordionBody" class="collapse mt-3">
              <?php
              try {
                  if (!class_exists('SplmsViewForum')) { require_once JPATH_SITE . '/components/com_splms/views/forum/view.html.php'; }
                  if (!class_exists('SplmsModelForum')) { require_once JPATH_SITE . '/components/com_splms/models/forum.php'; }
                  $forumModel = new SplmsModelForum();
                  $forumView = new SplmsViewForum(['model' => $forumModel]);
                  $forumView->setModel($forumModel, true); 
                  $forumView->display();
              } catch (Exception $e) { echo '<div class="alert alert-danger">Erro ao carregar o fórum.</div>'; }
              ?>
              </div>
            </div>
        <?php endif; ?>

        <!-- Has teacher -->
        <?php if (!empty($this->teachers)) { ?>
            <div id="course-instructor" class="splms-course-teachers splms-section guideway-course-section">
                <h3><i class="fa fa-users guideway-icon" aria-hidden="true"></i><?php echo Text::_('COM_SPLMS_MEET_OUR_COURSE_TEACHER'); ?></h3>
                <div class="splms-row">
                    <?php foreach ($this->teachers as $teacher) { ?>
                        <div class="splms-course-teacher">
                            <a href="<?php echo $teacher->url; ?>"><img src="<?php echo $teacher->image; ?>" alt="<?php echo $teacher->title; ?>"></a>
                            <h4><a href="<?php echo $teacher->url; ?>"><?php echo $teacher->title; ?></a></h4>
                            <small><?php echo $teacher->specialist_in; ?></small>
                            <div class="splms-teacher-bio">
                                <?php echo $teacher->description; ?>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </div>
        <?php } ?>

        <?php if ($this->review) { ?>
            <div id="course-reviews" class="user-reviews splms-section guideway-course-section">
                <?php
                    // GUIDEWAY CUSTOM: layout-avaliacoes - calcular rating e distribuição de estrelas
                    if (isset($this->ratings) && $this->ratings->count) {
                        $rating = $this->ratings->total / $this->ratings->count;
                        $rating = number_format($rating, 1);
                    } else {
                        $rating = 0;
                    }

                    // Distribuição de estrelas
                    $starCounts    = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
                    $totalReviews  = count($this->reviews);
                    foreach ($this->reviews as $_revItem) {
                        $_s = (int) $_revItem->rating;
                        if (isset($starCounts[$_s])) { $starCounts[$_s]++; }
                    }

                    // Stars HTML para média
                    $_ratingInt  = (int) round((float) $rating);
                    $_avgStars   = '';
                    for ($_si = 1; $_si <= 5; $_si++) {
                        $_cls       = $_si <= $_ratingInt ? 'fa fa-star' : 'fa fa-star-o';
                        $_avgStars .= '<i class="' . $_cls . '"></i>';
                    }

                    // Carregar CSS isolado de avaliações
                    Factory::getDocument()->addStyleSheet(\Joomla\CMS\Uri\Uri::root() . 'templates/maestro/css/guideway-reviews.css');
                ?>
                <div id="reviewsAccordion">
                    <h3 data-toggle="collapse" data-target="#collapseReviews" data-bs-toggle="collapse" data-bs-target="#collapseReviews" aria-expanded="false" aria-controls="collapseReviews" style="cursor: pointer; display: flex; justify-content: space-between; align-items: center;">
                        <span>
                            <i class="fa fa-star guideway-icon" aria-hidden="true"></i>
                            <?php echo Text::_('COM_SPLMS_REVIEWS'); ?>
                            <?php if ($rating > 0) { ?>
                                <span style="font-size: 0.85em; font-weight: normal; margin-left: 5px;"> - ⭐ <?php echo $rating; ?> (<?php echo $this->ratings->count; ?>)</span>
                            <?php } ?>
                        </span>
                        <i class="fa fa-chevron-down"></i>
                    </h3>

                    <div id="collapseReviews" class="collapse" aria-labelledby="headingReviews" data-parent="#reviewsAccordion">
                        <div class="card card-body" style="border: none; padding-left: 0; padding-right: 0;">
                            <!-- GUIDEWAY CUSTOM: Topbar com botao editar/login -->
                            <div class="gw-reviews-topbar">
                                <?php if ($this->myReview) { ?>
                                    <a id="splms-my-review" class="btn btn-primary btn-sm" href="#">
                                        <i class="splms-icon-write"></i> <?php echo Text::_('COM_SPLMS_EDIT_REVIEW'); ?>
                                    </a>
                                <?php } ?>
                                <?php if ($user->guest) { ?>
                                    <a href="<?php echo Route::_('index.php?option=com_users&view=login&return=' . base64_encode('index.php?option=com_splms&view=course&id=' . $this->item->id . ':' . $this->item->alias . SplmsHelper::getItemid('courses'))); ?>" class="btn btn-primary btn-sm">
                                        <i class="fa fa-pencil-square-o"></i> <?php echo Text::_('COM_SPLMS_LOGIN_TO_REVIEW'); ?>
                                    </a>
                                <?php } ?>
                            </div>

                            <!-- GUIDEWAY CUSTOM: Painel resumo: nota media + barras de progresso -->
                            <div class="gw-reviews-summary">
                                <div class="gw-reviews-score">
                                    <span class="gw-score-number"><?php echo $rating; ?></span>
                                    <span class="gw-score-label">Avaliação Média</span>
                                    <div class="gw-stars-avg"><?php echo $_avgStars; ?></div>
                                </div>
                                <div class="gw-reviews-bars">
                                    <?php foreach ([5, 4, 3, 2, 1] as $_sv): ?>
                                        <?php
                                            $_cnt = $starCounts[$_sv];
                                            $_pct = $totalReviews > 0 ? round(($_cnt / $totalReviews) * 100) : 0;
                                        ?>
                                        <div class="gw-bar-row">
                                            <span class="gw-bar-label"><?php echo $_sv; ?> <?php echo $_sv == 1 ? 'star' : 'stars'; ?></span>
                                            <span class="gw-bar-dot"></span>
                                            <div class="gw-bar-track">
                                                <div class="gw-bar-fill" style="width: <?php echo $_pct; ?>%;"></div>
                                            </div>
                                            <span class="gw-bar-pct"><?php echo $_pct; ?>%</span>
                                        </div>
                                    <?php endforeach; ?>
                                    <p class="gw-reviews-count-note">Baseado em <?php echo $totalReviews; ?> Avaliação<?php echo $totalReviews != 1 ? 'ões' : ''; ?></p>
                                </div>
                            </div><!-- /.gw-reviews-summary -->

                            <!-- Formulario de avaliacao (nativo) -->
                            <?php echo LayoutHelper::render('review.form', array('review' => $this->myReview, 'item_id' => $this->item->id, 'url' => 'index.php?option=com_splms&view=course&id=' . $this->item->id . ':' . $this->item->alias . SplmsHelper::getItemid('courses'))); ?>

                            <!-- Lista de avaliacoes com novo layout -->
                            <div id="reviews" class="gw-reviews-list">
                                <?php foreach ($this->reviews as $key => $this->review) {
                                    echo LayoutHelper::render('review.review', array('review' => $this->review));
                                } ?>
                            </div>

                            <?php if ($this->showLoadMore) { ?>
                                <div class="gw-reviews-loadmore-wrap">
                                    <a id="splms-load-review" data-item_id="<?php echo $this->item->id; ?>" href="#">
                                        <i class="fa fa-chevron-down"></i> Ver todas as avaliações
                                    </a>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php } ?>
      </div> <!-- Close splms-col-md-8/12 -->

      <?php if (!$isEnrolled) { ?>
      <div class="splms-col-md-4">
        <div class="course-header clearfix">
          <div class="course-short-info">
            <div class="apply-now">
              <div class="price_info"><?php echo $this->coursePrice; ?></div>
              <?php if (($this->item->price != 0) && ($this->isAuthorised == '')) { ?>
                <a class="btn btn-primary" id="addtocart" data-course="<?php echo $this->item->id; ?>" data-user="<?php echo $this->user->id; ?>" data-price="<?php echo $this->item->price; ?>" href="#">
                  <i class="splms-icon"></i><?php echo Text::_('COM_SPLMS_BUY_NOW'); ?>
                </a>
              <?php } elseif ($this->isAuthorised != '') { ?>
                <a class="btn btn-primary" href="javascript:void(0);">
                  <i class="splms-icon"></i><?php echo Text::_('COM_SPLMS_PURCHASED'); ?>
                </a>
              <?php } ?>
            </div>

            <ul class="course-info">
              <?php if (!empty($this->teachers)) { ?>
                <li class="splms-course-teacher">
                  <?php if ($this->total_teachers == 1) { ?>
                    <i class="splms-icon-teacher"></i>
                    <?php foreach ($this->teachers as $teacher) { ?>
                      <a href="<?php echo $teacher->url; ?>"><?php echo $teacher->title; ?></a>
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
              <li class="total-hours"><i class="splms-icon-video-cam"></i><?php echo $this->item->duration; ?></li>
              <li class="total-duration"><i class="splms-icon-video-cam"></i><?php echo $this->item->courseDuration; ?></li>
              <?php if ($this->total_enrolled) { ?><li class="total-studnets"><i class="splms-icon-graduate"></i> <?php echo $this->total_enrolled; ?></li><?php } ?>
              <?php if ($this->item->lessonsCount) { ?><li class="total-lessons"><i class="splms-icon-book"></i> <?php echo $this->item->lessonsCount; ?> <?php echo Text::_('COM_SPLMS_COMMON_LESSONS'); ?></li><?php } ?>
              <?php if (isset($this->item->admission_deadline) && $this->item->admission_deadline != '0000-00-00 00:00:00') { ?>
                <li class="admission-deadline" title="<?php echo Text::_('COM_SPLMS_ADMISSION_DEADLINE'); ?>"><i class="splms-icon-calendar"></i> <?php echo HTMLHelper::_('date', $this->item->admission_deadline, 'DATE_FORMAT_LC3'); ?></li>
              <?php } ?>
              <li class="course-details-total-review">
                <div class="reviews-status">
                  <i class="fa fa-star"></i>
                  <?php if (isset($this->ratings) && $this->ratings->count) { $rating = $this->ratings->total / $this->ratings->count; $rating = number_format($rating, 1); } else { $rating = 0; } ?>
                  <span class="total"><?php echo $rating; ?></span>
                  <span class="title">(<?php echo $this->ratings->count; ?> <?php echo Text::_('COM_SPLMS_RATINGS'); ?>)</span>
                </div>
              </li>
            </ul>
          </div>
        </div>

        <div class="splms-course-introduction">
          <?php if ($this->params->get('course_social_share', 1) && $this->isAuthorised == '') { ?>
            <div class="splms-section splms-course-social-share">
              <h3 class="splms-section-title"><?php echo Text::_('COM_SPLMS_SOCIAL_SHARE'); ?></h3>
              <?php echo LayoutHelper::render('social_share', array('url' => $this->item->link, 'title' => $this->item->title)); ?>
            </div>
          <?php } ?>
        </div>
      </div>
      <?php } ?>
    </div>

    <?php if (isset($this->item->course_schedules) && $this->item->course_schedules && count($this->item->course_schedules)) { ?>
      <div class="splms-course-class-rotuines">
        <div class="splms-class-routines">
          <h3 class="splms-title"><?php echo Text::_('COM_SPLMS_CLASS_TIMES'); ?></h3>
          <table class="table table-bordered">
            <thead>
              <tr><?php foreach ($this->schedule_days_lang as $schedule_day) { ?><th><?php echo $schedule_day; ?></th><?php } ?></tr>
            </thead>
            <tbody>
              <tr>
                <?php foreach ($this->schedule_days as $schedule_day) { ?>
                  <td class="splms-class-routines-day-<?php echo $schedule_day; ?>">
                    <?php foreach ($this->item->course_schedules as $course_schedule) { ?>
                      <?php if ($schedule_day == $course_schedule['day']) { ?>
                        <div class="splms-class-routines-text has-schedule"><?php echo $course_schedule['text']; ?></div>
                      <?php } ?>
                    <?php } ?>
                  </td>
                <?php } ?>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    <?php } ?>

    <?php if ($this->show_related_courses && !$isEnrolled) {
      if (isset($this->related_courses) && is_array($this->related_courses)) { ?>
        <div class="splms-similar-courses">
          <h3 class="splms-title"><?php echo Text::_('COM_SPLMS_SIMILAR_CLASSES'); ?></h3>
          <?php if (count($this->related_courses) > 0) { ?>
            <div class="splms-courses-list splms">
              <div class="splms-row">
                <?php foreach ($this->related_courses as $related_course) { ?>
                  <div class="splms-col-sm-6 splms-col-md-4">
                    <div class="splms-course">
                      <a href="<?php echo $related_course->url; ?>"><img src="<?php echo $related_course->thumb; ?>" class="splms-course-img splms-img-responsive" /></a>
                      <div class="splms-content-wrap">
                        <h4 class="splms-course-title"><a href="<?php echo $related_course->url; ?>"><?php echo $related_course->title; ?></a></h4>
                        <div class="splms-course-cat"><?php echo $related_course->category_name; ?></div>
                        <div class="splms-course-time"><?php echo $related_course->course_time; ?></div>
                        <div class="splms-course-details-btn"><a href="<?php echo $related_course->url; ?>" class="btn btn-primary"><?php echo Text::_('COM_SPLMS_DETAILS'); ?></a></div>
                      </div>
                    </div>
                  </div>
                <?php } ?>
              </div>
            </div>
          <?php } ?>
        </div>
      <?php } ?>
    <?php } ?>
  </div>
</div>
