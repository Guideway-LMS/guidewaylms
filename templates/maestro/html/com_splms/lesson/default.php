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

// acessa os parametros do templete
$params = JComponentHelper::getParams('com_splms');
$percentualMinimoConclusao = (int) $params->get('percentual_minimo_conclusao', 90);

//define a variavel do percentual 
$doc = Factory::getDocument();
$doc->addScriptOptions('splmsConfig', [
    'percentualMinimoConclusao' => $percentualMinimoConclusao
]);

//Erick 21-12 parametros para verificar se aulas estao concluidas
// Usuário (padrão já usado na view)


$user   = $this->user ?? Factory::getUser();
$userId = (int) $user->id;

$completedLessons = [];

if ($userId && !empty($this->item->course_id)) {

    BaseDatabaseModel::addIncludePath(
        JPATH_SITE . '/components/com_splms/models',
        'SplmsModel'
    );

    /** @var \SplmsModelCourse $courseModel */
    $courseModel = BaseDatabaseModel::getInstance('Course', 'SplmsModel');

    if ($courseModel) {
        $completedLessons = $courseModel->getCompletedLessonsByCourse(
            (int) $this->item->course_id,
            $userId
        );
    }
}

$this->completedLessons = $completedLessons;
echo '<pre>';
var_dump($completedLessons);
echo '</pre>';


// Carrega JS e CSS da notificação e progresso
$doc->addScript(Uri::root() . 'components/com_splms/assets/js/course-progress.js');
$doc->addScript(Uri::root() . 'media/gw-progress-alert/js/alerta-conclusao.js');
$doc->addStyleSheet(Uri::root() . 'media/gw-progress-alert/css/alerta-conclusao.css');
$doc->addScript(Uri::root() . 'components/com_splms/assets/js/lesson-complete-handler.js');

// CSS INLINE PARA O UPLOAD AESTHETIC + CORES PADRONIZADAS
$doc->addStyleDeclaration('
    .upload-card {
        background: #ffffff;
        border-radius: 16px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        padding: 40px;
        text-align: center;
        transition: transform 0.2s ease;
        border: 1px solid #f0f0f0;
    }
    .upload-zone {
        border: 2px dashed #e0e7ff;
        border-radius: 12px;
        padding: 30px;
        background: #fafbff;
        transition: all 0.3s ease;
        margin-bottom: 15px; /* Reduzi a margem para caber as infos */
        position: relative;
    }
    .upload-zone:hover {
        border-color: #4CAF50; /* Verde ao passar o mouse */
        background: #f0fff4;
    }
    .upload-icon {
        font-size: 48px;
        color: #4CAF50; /* Verde */
        margin-bottom: 15px;
    }
    .upload-title {
        font-size: 22px;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 10px;
    }
    .upload-desc {
        color: #64748b;
        font-size: 14px;
        margin-bottom: 20px;
    }
    .btn-upload-custom {
        background: #3b82f6; /* Azul mantido para diferenciar ação de escolha */
        color: white;
        padding: 12px 24px;
        border-radius: 8px;
        font-weight: 500;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: background 0.2s;
        border: none;
        margin-top: 15px;
    }
    .btn-upload-custom:hover {
        background: #2563eb;
        color: white;
    }
    .file-name-display {
        margin-top: 15px;
        font-weight: 600;
        color: #4CAF50;
        font-size: 14px;
        min-height: 20px;
    }
    .file-info-text {
        font-size: 12px;
        color: #94a3b8;
        margin-top: 5px;
    }
    
    /* ESTILOS DO CAMPO DE COMENTÁRIO */
    .comment-wrapper {
        text-align: left;
        margin-top: 20px;
        margin-bottom: 20px;
    }
    .comment-label {
        font-size: 14px;
        font-weight: 600;
        color: #475569;
        display: block;
        margin-bottom: 8px;
    }
    .comment-textarea {
        width: 100%;
        padding: 12px;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        resize: vertical;
        font-family: inherit;
        min-height: 80px;
        background: #f8fafc;
        transition: border 0.3s;
    }
    .comment-textarea:focus {
        outline: none;
        border-color: #4CAF50;
        background: #fff;
        box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.1);
    }

    /* BOTÃO DE ENVIAR - MESMO VERDE DO PROGRESSO */
    .btn-send {
        width: 100%;
        background: #4CAF50; /* Verde do Progresso */
        border: none;
        padding: 15px;
        border-radius: 8px;
        font-size: 16px;
        font-weight: bold;
        color: white;
        margin-top: 10px;
        cursor: pointer;
        transition: background 0.3s;
    }
    .btn-send:hover {
        background: #45a049; /* Verde levemente mais escuro no hover */
    }
');

// JS para o nome do arquivo aparecer bonito
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
<!-- variaveis essenciais -->
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
  
  <div class="course-progress-container" style="margin: 20px auto; padding: 25px; background: white; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); max-width: 800px;">
    <h3 style="margin: 0 0 20px 0; color: #333; font-size: 20px;">📚 Seu Progresso no Curso</h3>
    <div style="text-align: center; font-size: 50px; margin: 20px 0;" id="progress-emoji">📝</div>
    <div style="width: 100%; height: 35px; background: #e0e0e0; border-radius: 20px; position: relative; overflow: hidden; margin: 20px 0;">
      <div id="course-progress-bar" style="height: 100%; background: linear-gradient(90deg, #4CAF50, #45a049); border-radius: 20px; width: 0%; transition: width 0.8s ease;"></div>
      <div style="position: absolute; width: 100%; text-align: center; line-height: 35px; font-weight: bold; color: #333; top: 0; font-size: 14px;" id="course-progress-text">Carregando...</div>
    </div>
    <div style="text-align: center; color: #666; margin-top: 15px; font-size: 16px;" id="progress-message">Buscando...</div>
  </div>

  <div class="row">
    <div class="col-md-7">
      <div class="splms-lesson-video-wrapper">

        <?php 
        // LÓGICA DE DECISÃO: É TRABALHO OU VÍDEO?
        if (isset($this->item->lesson_format) && $this->item->lesson_format === 'trabalho') : 
        ?>
            <div style="margin-bottom: 25px;">
                <h2 style="font-weight: 700; color: #1e293b; margin: 0; font-size: 28px;">
                    <i class="fa fa-cloud-upload" style="color: #4CAF50; margin-right: 10px;"></i> 
                    Envio de Trabalho
                </h2>
                <p style="color: #64748b; font-size: 16px; margin-top: 5px;">
                    Esta etapa é obrigatória para a conclusão do módulo.
                </p>
            </div>

            <div class="upload-card">
                <form action="<?php echo JRoute::_('index.php?option=com_splms&task=lesson.submit'); ?>" method="post" enctype="multipart/form-data">
                    
                    <div class="upload-zone">
                        <div style="font-size: 32px; color: #cbd5e1; margin-bottom: 10px;">
                            <i class="fa fa-file-text-o"></i>
                        </div>
                        
                        <h3 class="upload-title" style="font-size: 18px;">Área de Transferência</h3>
                        
                        <input type="file" name="uploaded_file" id="file-upload-input" style="display: none;" required>
                        
                        <label for="file-upload-input" class="btn-upload-custom">
                            <i class="fa fa-folder-open-o"></i> Escolher Arquivo no Computador
                        </label>

                        <div id="file-name-text" class="file-name-display">
                            Nenhum arquivo selecionado
                        </div>
                        <div class="file-info-text">Formatos: PDF, ZIP, MP4 (Max: 10MB)</div>
                    </div>

                    <div class="comment-wrapper">
                        <label for="student_comment" class="comment-label">Comentário (Opcional):</label>
                        <textarea name="student_comment" id="student_comment" class="comment-textarea" placeholder="Escreva uma mensagem para o professor..."></textarea>
                    </div>

                    <input type="hidden" name="course_id" value="<?php echo $this->item->course_id; ?>" />
                    <input type="hidden" name="lesson_id" value="<?php echo $this->item->id; ?>" />
                    <?php echo JHtml::_('form.token'); ?>

                    <button type="submit" class="btn-send">
                        ENVIAR TRABALHO <i class="fa fa-paper-plane"></i>
                    </button>
                </form>
            </div>

            <div style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #eee;">
                <h5 style="font-weight: 700; color: #555; font-size: 16px; margin-bottom: 10px; text-transform: uppercase; letter-spacing: 0.5px;">Sobre esta atividade:</h5>
                <div class="splms-lesson-description" style="color: #666; font-size: 14px; line-height: 1.6;">
                    <?php echo $this->item->description; ?>
                </div>
            </div>

        <?php else : ?>
            
            <?php if (!empty($this->item->video_url)) { ?>
              <div class="lesson-video">
                <?php echo LayoutHelper::render('player', array('video' => $this->item->video_url, 'thumbnail' => $this->item->vdo_thumb)); ?>
              </div>
            <?php } elseif ($this->item->vdo_thumb) { ?>
              <div class="lesson-thumbnail">
                <img class="splms-img-responsive" src="<?php echo $this->item->vdo_thumb; ?>" alt="<?php echo $this->item->title; ?>">
              </div>
            <?php } ?>

            <div class="splms-lesson-description item-content" style="margin-top: 20px;">
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
                $active_lesson = ($this->item->id == $lesson->id) ? ' active' : '';
                $isCompleted  = !empty($this->completedLessons[$lesson->id]);
                ?>
              <?php if ($lesson->lesson_type == 0 || $this->isAuthorised != '' || $this->courese->price == 0) : ?>
                <li class="lesson<?php echo $active_lesson; ?><?php echo $isCompleted ? ' lesson-completed' : ''; ?>"
    data-lesson-id="<?php echo (int) $lesson->id; ?>">
                  
                  <?php if (!empty($lesson->video_url)) : ?>
                    <span>
                      <a href="<?php echo $lesson->lesson_url; ?>">
                        <!-- <?php //echo $lesson->title; ?> -->
                        <!-- novo trecho Erick 21-12 -->
                         <span class="lesson-title">
                          <?php echo $lesson->title; ?>
                          <?php if ($isCompleted) : ?>
                            <span class="lesson-completed-icon"> ✅</span>
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
                      <?php endif; ?>
                    </span>
                      
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
              <?php endif; ?>
            <?php } ?>
          </ul>
        </div>
      <?php } ?>
    </div>
  </div>

  <div class="splms-lesson-completed-lesson-wrapper"
    <?php if (isset($this->item->course_id)) : ?>
          data-course-id="<?php echo (int) $this->item->course_id; ?>"
     <?php endif; ?> 
    >
    
    <?php 
    if (!isset($this->item->lesson_format) || $this->item->lesson_format !== 'trabalho') : 
    ?>
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
            <input type="hidden" name="course_id" value="<?php echo isset($this->item->course_id) ? (int) $this->item->course_id : ''; ?>">
            <a class="btn btn-primary" id="splms-completed-item" href="#">
              <?php echo Text::_('COM_SPLMS_LESSON_COMPLETE'); ?>
            </a>
          </form>
        <?php } else { ?>
          <a class="btn btn-primary" id="splms-completed-item" href="#">
            <?php echo Text::_('COM_SPLMS_LESSON_COMPLETED'); ?>
          </a>
        <?php } ?>
    <?php endif; ?>
    
  </div>

</div>