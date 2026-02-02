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
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

Factory::getDocument()->addStyleSheet(Uri::root() . 'templates/maestro/css/splms-progress.css');
$params = JComponentHelper::getParams('com_splms');
$percentualMinimoConclusao = (int) $params->get('percentual_minimo_conclusao', 90);

$doc = Factory::getDocument();
$doc->addScriptOptions('splmsConfig', [
    'percentualMinimoConclusao' => $percentualMinimoConclusao
]);

$user   = $this->user ?? Factory::getUser();
$userId = (int) $user->id;

// =============================================================================
// 1. CONSULTA AO BANCO (VERSÃO LIMPA)
// =============================================================================
$submission = null;
$db = Factory::getDbo();

// VERIFICAÇÃO ROBUSTA: Aceita tanto lesson_type 2 quanto o formato 'assignment'
$isAssignment = (
    ((int)$this->item->lesson_type === 2) || 
    (isset($this->item->lesson_format) && ($this->item->lesson_format === 'assignment' || $this->item->lesson_format === 'trabalho'))
);

if ($isAssignment) {
    $query = $db->getQuery(true)
        ->select('*')
        ->from($db->quoteName('bak_lepgs_splms_submissions'))
        ->where($db->quoteName('user_id') . ' = ' . $userId)
        ->where($db->quoteName('lesson_id') . ' = ' . (int)$this->item->id)
        ->order('id DESC'); // Garante que pega o envio mais recente
    
    $db->setQuery($query, 0, 1);
    $submission = $db->loadObject();
}
// =============================================================================

$lessonStates = [];

if ($userId && !empty($this->item->course_id)) {
    BaseDatabaseModel::addIncludePath(
        JPATH_SITE . '/components/com_splms/models',
        'SplmsModel'
    );

    $courseModel = BaseDatabaseModel::getInstance('Course', 'SplmsModel');
    if ($courseModel) {
        $lessonStates = $courseModel->getLessonStatesByCourse(
            (int) $this->item->course_id,
            $userId
        );
    }
}

$this->lessonStates = $lessonStates;
$doc->addScript(Uri::root() . 'components/com_splms/assets/js/course-progress.js');
$doc->addScript(Uri::root() . 'media/gw-progress-alert/js/alerta-conclusao.js');
$doc->addStyleSheet(Uri::root() . 'media/gw-progress-alert/css/alerta-conclusao.css');
$doc->addScript(Uri::root() . 'components/com_splms/assets/js/lesson-complete-handler.js');

// CSS GERAL
$doc->addStyleDeclaration('
    .upload-card { background: #ffffff; border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); padding: 40px; text-align: center; transition: transform 0.2s ease; border: 1px solid #f0f0f0; }
    .upload-zone { border: 2px dashed #e0e7ff; border-radius: 12px; padding: 30px; background: #fafbff; transition: all 0.3s ease; margin-bottom: 15px; position: relative; }
    .upload-zone:hover { border-color: #4CAF50; background: #f0fff4; }
    .status-icon { font-size: 48px; margin-bottom: 15px; display: block; }
    .status-graded { color: #22c55e; }
    .status-pending { color: #f59e0b; }
    .status-title { font-size: 22px; font-weight: 700; margin-bottom: 10px; }
    .grade-display { font-size: 3rem; font-weight: 800; color: #333; margin: 15px 0; }
    .upload-title { font-size: 22px; font-weight: 600; color: #1e293b; margin-bottom: 10px; }
    .btn-upload-custom { background: #3b82f6; color: white; padding: 12px 24px; border-radius: 8px; font-weight: 500; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; transition: background 0.2s; border: none; margin-top: 15px; }
    .btn-upload-custom:hover { background: #2563eb; color: white; }
    .file-name-display { margin-top: 15px; font-weight: 600; color: #4CAF50; font-size: 14px; min-height: 20px; }
    .file-info-text { font-size: 12px; color: #94a3b8; margin-top: 5px; }
    .comment-wrapper { text-align: left; margin-top: 20px; margin-bottom: 20px; }
    .comment-label { font-size: 14px; font-weight: 600; color: #475569; display: block; margin-bottom: 8px; }
    .comment-textarea { width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 8px; resize: vertical; font-family: inherit; min-height: 80px; background: #f8fafc; }
    .comment-textarea:focus { outline: none; border-color: #4CAF50; background: #fff; }
    .btn-send { width: 100%; background: #4CAF50; border: none; padding: 15px; border-radius: 8px; font-size: 16px; font-weight: bold; color: white; margin-top: 10px; cursor: pointer; transition: background 0.3s; }
    .btn-send:hover { background: #45a049; }
');

$doc->addScriptDeclaration('
document.addEventListener("DOMContentLoaded", function() {
    var fileInput = document.getElementById("file-upload-input");
    var fileNameDisplay = document.getElementById("file-name-text");
    var zone = document.querySelector(".upload-zone");
    if(fileInput) {
        fileInput.addEventListener("change", function() {
            if (this.files && this.files.length > 0) {
                fileNameDisplay.innerHTML = "<i class=\'fa fa-check-circle\'></i> " + this.files[0].name;
                zone.style.borderColor = "#4CAF50";
                zone.style.background = "#e8f5e9";
            }
        });
    }
});
');
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

  <div class="row">
    <div class="col-md-7">
      <div class="splms-lesson-video-wrapper">

        <?php 
        // USANDO A VARIÁVEL ROBUSTA CRIADA NO INÍCIO
        if ($isAssignment) : 
        ?>

            <div style="margin-bottom: 25px;">
                <h2 style="font-weight: 700; color: #1e293b; margin: 0; font-size: 28px;">
                    <i class="fa fa-cloud-upload" style="color: #4CAF50; margin-right: 10px;"></i> Envio de Trabalho
                </h2>
                <p style="color: #64748b; font-size: 16px; margin-top: 5px;">Esta etapa é obrigatória para a conclusão do módulo.</p>
            </div>

            <div class="upload-card">
                <?php 
                // ===========================================================
                // CENÁRIO 1: APROVADO (Status 1) -> MOSTRA SUCESSO E TRAVA
                // ===========================================================
                if ($submission && $submission->status == 1) : 
                ?>
                    <i class="fa fa-check-circle status-icon status-graded"></i>
                    <h3 class="status-title status-graded">Trabalho Aprovado!</h3>
                    <div class="grade-display">
                        <?php echo number_format($submission->grade, 1); ?> <span style="font-size: 1rem; color: #999;">/ 100</span>
                    </div>
                    <?php if (!empty($submission->feedback)) : ?>
                        <div style="background: #f1f8e9; padding: 15px; border-radius: 8px; text-align: left; border: 1px solid #c8e6c9; margin-top: 15px;">
                            <strong style="color: #2e7d32;">Feedback do Professor:</strong>
                            <p style="margin: 5px 0 0 0; color: #333;"><?php echo $submission->feedback; ?></p>
                        </div>
                    <?php endif; ?>

                <?php 
                // ===========================================================
                // CENÁRIO 2: PENDENTE (Status 0) -> MOSTRA AGUARDANDO E TRAVA
                // ===========================================================
                elseif ($submission && $submission->status == 0) : 
                ?>
                    <div class="upload-zone" style="border-color: #f59e0b; background: #fffbf0;">
                        <i class="fa fa-clock-o status-icon status-pending"></i>
                        <h3 class="status-title status-pending">Aguardando Correção</h3>
                        <p style="color: #666;">Arquivo enviado com sucesso:</p>
                        <div style="background: white; padding: 8px 15px; border-radius: 20px; display: inline-block; box-shadow: 0 2px 5px rgba(0,0,0,0.05); margin-top: 10px; font-family: monospace;">
                            <i class="fa fa-file-text-o"></i> <?php echo basename($submission->file_path); ?>
                        </div>
                        <p style="font-size: 12px; color: #999; margin-top: 15px;">Enviado em: <?php echo date('d/m/Y H:i', strtotime($submission->submitted_at)); ?></p>
                    </div>
                    <div style="color: #64748b; font-size: 14px;">Você será notificado assim que sua nota for lançada.</div>

                <?php 
                // ===========================================================
                // CENÁRIO 3: REPROVADO (Status 2) OU NENHUM ENVIO -> MOSTRA FORMULÁRIO
                // ===========================================================
                else : 
                ?>
                    
                    <?php if ($submission && $submission->status == 2) : ?>
                        <div style="background: #fee2e2; border: 1px solid #ef4444; border-radius: 12px; padding: 20px; margin-bottom: 25px;">
                            <h3 style="color: #b91c1c; margin-top: 0; font-size: 20px; font-weight: bold;"><i class="fa fa-times-circle"></i> Trabalho Reprovado</h3>
                            
                            <div style="display:flex; justify-content:center; align-items:center; gap:10px; margin: 10px 0;">
                                <span style="font-size: 14px; color: #7f1d1d;">Sua nota:</span>
                                <strong style="font-size: 24px; color: #b91c1c;"><?php echo number_format($submission->grade, 1); ?></strong>
                            </div>

                            <?php if (!empty($submission->feedback)) : ?>
                                <div style="background: white; padding: 12px; border-radius: 6px; border: 1px solid #fca5a5; text-align: left;">
                                    <strong style="color: #991b1b;">O que melhorar:</strong>
                                    <p style="margin: 5px 0 0 0; color: #450a0a; font-size: 14px;"><?php echo $submission->feedback; ?></p>
                                </div>
                            <?php endif; ?>
                            
                            <div style="margin-top:15px; font-weight:bold; color: #b91c1c; font-size: 14px;">👇 Envie uma nova versão abaixo:</div>
                        </div>
                    <?php endif; ?>

                    <form action="<?php echo JRoute::_('index.php?option=com_splms&task=lesson.uploadAssignment'); ?>" method="post" enctype="multipart/form-data">
                        <div class="upload-zone">
                            <div style="font-size: 32px; color: #cbd5e1; margin-bottom: 10px;"><i class="fa fa-file-text-o"></i></div>
                            <h3 class="upload-title" style="font-size: 18px;">Área de Transferência</h3>
                            <input type="file" name="uploaded_file" id="file-upload-input" style="display: none;" required>
                            <label for="file-upload-input" class="btn-upload-custom"><i class="fa fa-folder-open-o"></i> Escolher Arquivo no Computador</label>
                            <div id="file-name-text" class="file-name-display">Nenhum arquivo selecionado</div>
                            <div class="file-info-text">Formatos: PDF, ZIP, MP4 (Max: 10MB)</div>
                        </div>
                        <div class="comment-wrapper">
                            <label for="student_comment" class="comment-label">Comentário (Opcional):</label>
                            <textarea name="student_comment" id="student_comment" class="comment-textarea" placeholder="Escreva uma mensagem para o professor..."></textarea>
                        </div>
                        <input type="hidden" name="course_id" value="<?php echo $this->item->course_id; ?>" />
                        <input type="hidden" name="lesson_id" value="<?php echo $this->item->id; ?>" />
                        <?php echo JHtml::_('form.token'); ?>
                        <button type="submit" class="btn-send">ENVIAR TRABALHO <i class="fa fa-paper-plane"></i></button>
                    </form>

                <?php endif; ?>
            </div>

            <div style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #eee;">
                <h5 style="font-weight: 700; color: #555; font-size: 16px; margin-bottom: 10px; text-transform: uppercase; letter-spacing: 0.5px;">Sobre esta atividade:</h5>
                <div class="splms-lesson-description" style="color: #666; font-size: 14px; line-height: 1.6;">
                    <?php echo $this->item->description; ?>
                </div>
            </div>

        <?php else : ?>
            <?php if (!empty($this->item->video_url)) { ?>
              <div class="lesson-video"><?php echo LayoutHelper::render('player', array('video' => $this->item->video_url, 'thumbnail' => $this->item->vdo_thumb)); ?></div>
            <?php } elseif ($this->item->vdo_thumb) { ?>
              <div class="lesson-thumbnail"><img class="splms-img-responsive" src="<?php echo $this->item->vdo_thumb; ?>" alt="<?php echo $this->item->title; ?>"></div>
            <?php } ?>
            <div class="splms-lesson-description item-content" style="margin-top: 20px;">
              <h2><?php echo $this->item->title; ?></h2>
              <div class="splms-lesson-description"><?php echo $this->item->description; ?></div>
            </div>
            <?php if (isset($this->item->attachment) && $this->item->attachment) { ?>
              <div class="item-content splms-lesson-attachment-wrapper"><a class="btn btn-default attachment-button" target="_blank" href="<?php echo Uri::root() . $this->item->attachment; ?>"><?php echo Text::_('COM_SPLMS_LESSON_DOWNLOAD_ATTACHMENT') ?></a></div>
            <?php } ?>
        <?php endif; ?>

      </div>
    </div>
    
    <div class="col-md-5">
      <?php if (!empty($this->lessons) && count($this->lessons) && $this->lessons) { ?>
        <div class="course-lessons">
          <h3><?php echo Text::_('COM_SPLMS_LESOSNS_LIST'); ?></h3>
          <ul class="lessons list-unstyled">
            <?php foreach ($this->lessons as $lesson) { ?>
            
              <?php
              //SELECIONA ESTADO DE CADA LICAO
                    $active_lesson = ($this->item->id == $lesson->id) ? ' active' : '';

                    $lessonStates = $this->lessonStates ?? [];
                    $state = $lessonStates[$lesson->id] ?? 0;

                    $isCompleted = ($state === 1);
                    $isPending   = ($state === 2);
                    ?>
              <?php if ($lesson->lesson_type == 0 || $this->isAuthorised != '' || $this->courese->price == 0) : ?>
                <li class="lesson<?php echo $active_lesson; ?>
                      <?php echo $isCompleted ? ' lesson-completed' : ''; ?>
                      <?php echo $isPending ? ' lesson-pending' : ''; ?>"
                      data-lesson-id="<?php echo (int) $lesson->id; ?>">
                 
                  <?php if (!empty($lesson->video_url)) : ?>
                  <span>
                    <a href="<?php echo $lesson->lesson_url; ?>">
                      <span class="lesson-title">
                        <?php echo $lesson->title; ?>

                        <?php if ($isCompleted) : ?>
                          <span class="lesson-completed-icon"> ✅</span>
                        <?php elseif ($isPending) : ?>
                          <span class="lesson-pending-icon"> ⏳</span>
                        <?php endif; ?>

                      </span>
                    </a>
                  </span>

                  <span class="pull-right lesson-duration">
                    <span><?php echo Text::_('COM_SPLMS_COMMON_DURATION') . Text::_(': '); ?></span>
                    <?php echo $lesson->video_duration; ?>
                  </span>

                <?php else : ?>

                  <a href="<?php echo $lesson->lesson_url; ?>">
                    <span class="lesson-title">
                      <?php echo $lesson->title; ?>

                      <?php if ($isCompleted) : ?>
                        <span class="lesson-completed-icon"> ✅</span>
                      <?php elseif ($isPending) : ?>
                        <span class="lesson-pending-icon"> ⏳</span>
                      <?php endif; ?>

                    </span>
                  </a>

                <?php endif; ?>
                </li>
              <?php else : ?>
                <li class="lesson splms-lesson-unauthorised"><span><i class="splms-icon-book"></i><i class="splms-icon-lock"></i><?php echo $lesson->title; ?></span><span class="pull-right lesson-duration"><span><?php echo Text::_('COM_SPLMS_COMMON_DURATION') . Text::_(': '); ?></span><?php echo $lesson->video_duration; ?></span></li>
              <?php endif; ?>
            <?php } ?>
          </ul>
        </div>
      <?php } ?>
    </div>
  </div>


  <div class="splms-lesson-completed-lesson-wrapper" 
    <?php if (isset($this->item->course_id)) : ?> data-course-id="<?php echo (int) $this->item->course_id; ?>" <?php endif; ?> >
    
    <?php 
    // USANDO A MESMA VARIÁVEL ROBUSTA PARA ESCONDER O BOTÃO "CONCLUIR" NOS TRABALHOS
    if (!$isAssignment) : 
    ?>
    
        <?php if ($this->user->guest) { 
          $link =  base64_encode(Uri::getInstance()->toString()); 
          $login_link = Route::_('index.php?option=com_users&view=login' . SplmsHelper::getItemid('login') . '&return=' . $link); 
        ?>
          <a class="btn btn-primary" href="<?php echo $login_link; ?>"><?php echo Text::_('COM_SPLMS_LOGIN_TO_COMPLETE'); ?></a>
        <?php } elseif (!$this->has_complete_lesson) { ?>
          <form id="splms-completed-item-form"><input type="hidden" name="user_id" value="<?php echo $this->user->id; ?>"><input type="hidden" name="item_id" value="<?php echo $this->item->id; ?>"><input type="hidden" name="item_type" value="lesson"><input type="hidden" name="course_id" value="<?php echo isset($this->item->course_id) ? (int) $this->item->course_id : ''; ?>"><a class="btn btn-primary" id="splms-completed-item" href="#"><?php echo Text::_('COM_SPLMS_LESSON_COMPLETE'); ?></a></form>
        <?php } else { ?>
          <a class="btn btn-primary" id="splms-completed-item" href="#"><?php echo Text::_('COM_SPLMS_LESSON_COMPLETED'); ?></a>
        <?php } ?>

    <?php endif; ?>
  </div>

</div>