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
            <ul>
              <li><a href="#course-about"><?php echo Text::_('COM_SPLMS_COURSE_ABOUT'); ?></a></li>
              <li><a href="#course-lessons"><?php echo Text::_('COM_SPLMS_COURSE_LESSONS'); ?></a></li>
              <li><a href="#course-instructor"><?php echo Text::_('COM_SPLMS_COURSE_INSTRUCTOR'); ?></a></li>
              <li><a href="#course-reviews"><?php echo Text::_('COM_SPLMS_COURSE_REVIEWS'); ?></a></li>
              
              <li>
                <?php 
                    $certDownloadUrl = Route::_('index.php?option=com_splms&task=certificate.generate&submission_id=' . $submission_id . '&course_id=' . (int)$this->item->id);
                ?>
                <a href="<?php echo $certDownloadUrl; ?>" id="certificate-btn" class="certificate-btn enabled" target="_blank" style="color: #28a745; font-weight: bold;">
                  🎓 Certificado
                </a>
              </li>
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
        <?php } ?>

        <?php if ($isEnrolled) : ?>
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

      </div>
    </div>
  </div>
</div>