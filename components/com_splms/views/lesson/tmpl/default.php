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
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\HTML\HTMLHelper;

// 1. INICIALIZAÇÃO E CAPTURA DE DADOS VITAIS
$user = Factory::getUser();
$app  = Factory::getApplication();
$currentItemId = $app->input->getInt('Itemid', 0); // Captura o ID do Menu para não perder a rota

// 2. VERIFICAÇÃO DIRETA NO BANCO DE DADOS (Self-Contained Logic)
// Consultamos aqui mesmo se existe um envio, sem depender da View Class.
$submission = null;
if (!$user->guest) {
    $db = Factory::getDbo();
    $query = $db->getQuery(true)
        ->select('*')
        ->from($db->quoteName('#__splms_submissions'))
        ->where($db->quoteName('user_id') . ' = ' . (int)$user->id)
        ->where($db->quoteName('lesson_id') . ' = ' . (int)$this->item->id);
    $db->setQuery($query);
    $submission = $db->loadObject();
}

// Carregar script de progresso
$document = Factory::getDocument();
$document->addScript(Uri::root() . 'components/com_splms/assets/js/lesson-progress.js');
$document->addScript(Uri::root() . 'components/com_splms/assets/js/course-progress.js');
?>

<script>
window.SPLMS_CONTEXT = {
  itemId: <?php echo (int) $this->item->id; ?>,
  itemType: "lesson",
  userId: <?php echo (int) $this->user->id; ?>,
  courseId: <?php echo (int) ($this->item->course_id ?? 0); ?>,
  isCompleted: <?php echo $this->has_complete_lesson ? 'true' : 'false'; ?>
};
</script>

<div id="splms" class="splms splms-lessons splms-lesson-details"> 
  
  <div class="course-progress-container">
    <h3 class="course-progress-title">📚 Seu Progresso no Curso</h3>
    <div id="progress-emoji" class="course-progress-emoji">📋</div>
    <div class="course-progress-track">
        <div id="course-progress-bar" class="course-progress-fill" style="width: 0%;"></div>
        <div id="course-progress-text" class="course-progress-text">0%</div>
    </div>
    <div id="progress-message" class="course-progress-message">Carregando...</div>
  </div>

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
  
  <div class="item-content splms-assignment-wrapper" style="margin-top: 30px; padding: 20px; border: 1px solid #eee; border-radius: 5px; background: #fafafa;">
      
      <?php if ($submission) : ?>
          <h3 style="margin-top:0; color: #28a745;">✅ Trabalho Enviado</h3>
          
          <div class="submission-status-card p-3" style="background: #fff; border: 1px solid #ddd; border-left: 5px solid #28a745;">
              <p><strong>Status:</strong> 
                  <?php 
                  if ($submission->status == 1) {
                      echo '<span class="badge badge-success" style="background:green; color:white; padding:3px 8px;">Aprovado</span>';
                  } elseif ($submission->status == 2) {
                      echo '<span class="badge badge-danger" style="background:red; color:white; padding:3px 8px;">Rejeitado</span>';
                  } else {
                      echo '<span class="badge badge-warning" style="background:orange; color:white; padding:3px 8px;">Em Correção</span>';
                  }
                  ?>
              </p>
              <p><strong>Data de Envio:</strong> <?php echo date('d/m/Y H:i', strtotime($submission->submitted_at)); ?></p>
              
              <?php if (!empty($submission->teacher_comment)) : ?>
                  <hr>
                  <p><strong>Comentário do Professor:</strong></p>
                  <div class="alert alert-info">
                      <?php echo nl2br($submission->teacher_comment); ?>
                  </div>
              <?php else: ?>
                  <p class="text-muted"><small>O professor ainda não comentou seu trabalho.</small></p>
              <?php endif; ?>
          </div>

      <?php else : ?>
          <h3 style="margin-top:0;">📤 Enviar Trabalho / Atividade</h3>
          
          <?php if ($user->guest) : 
              $returnUrl = base64_encode(Uri::getInstance()->toString());
              $loginUrl  = Route::_('index.php?option=com_users&view=login&return=' . $returnUrl);
          ?>
              <div class="alert alert-info text-center p-4">
                  <h4>🔒 Acesso Restrito</h4>
                  <p>Você precisa estar identificado para enviar seu trabalho.</p>
                  <a href="<?php echo $loginUrl; ?>" class="btn btn-primary btn-lg" style="margin-top:10px;">
                      <span class="icon-user"></span> Fazer Login para Enviar
                  </a>
              </div>

          <?php else : ?>

              <form action="<?php echo Route::_('index.php?option=com_splms&task=lesson.uploadAssignment'); ?>" 
                    method="post" enctype="multipart/form-data">
                  
                  <input type="hidden" name="lesson_id" value="<?php echo $this->item->id; ?>" />
                  <input type="hidden" name="Itemid" value="<?php echo $currentItemId; ?>" />
                  
                  <div class="control-group">
                      <label><strong>Selecione seu arquivo:</strong> <small class="text-muted">(PDF, ZIP, Imagem - Máx 10MB)</small></label>
                      <input type="file" name="uploaded_file" class="form-control" required style="margin-bottom: 10px;" />
                  </div>
                  
                  <button type="submit" class="btn btn-success">
                      <span class="icon-upload"></span> Enviar Arquivo
                  </button>
                  
                  <?php echo HTMLHelper::_('form.token'); ?>
              </form>

          <?php endif; ?>

      <?php endif; ?>
  </div>
  <?php if (!empty($this->item->topics) && count($this->item->topics) && $this->item->topics) {?>
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
             </ul>
        </div>
      </div>
    </div>
  <?php } ?>
  
  <div class="splms-lesson-completed-lesson-wrapper" data-course-id="<?php echo $this->item->course_id ?? 0; ?>">
    <?php if($user->guest) { 
      $link =  base64_encode(Uri::getInstance()->toString());
      $login_link = Route::_('index.php?option=com_users&view=login'. SplmsHelper::getItemid('login') .'&return=' . $link);
    ?>
      <a class="btn btn-primary" href="<?php echo $login_link; ?>">
        <?php echo Text::_('COM_SPLMS_LOGIN_TO_COMPLETE'); ?>
      </a>
    <?php } elseif(!$this->has_complete_lesson) {?>
      <form id="splms-completed-item-form">
        <input type="hidden" name="user_id" value="<?php echo $user->id; ?>">
        <input type="hidden" name="item_id" value="<?php echo $this->item->id; ?>">
        <input type="hidden" name="course_id" value="<?php echo $this->item->course_id ?? 0; ?>">
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

</div>