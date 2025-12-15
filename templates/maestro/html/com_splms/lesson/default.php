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

// acessa os parametros do templete
$params = JComponentHelper::getParams('com_splms');
$percentualMinimoConclusao = (int) $params->get('percentual_minimo_conclusao', 90);

//define a variavel do percentual 
$doc = Factory::getDocument();
$doc->addScriptOptions('splmsConfig', [
    'percentualMinimoConclusao' => $percentualMinimoConclusao
]);

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
        margin-bottom: 25px;
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
                        
                        <label for="file-upload-input" class="btn-upload-custom" style="margin-top: 15px;">
                            <i class="fa fa-folder-open-o"></i> Escolher Arquivo no Computador
                        </label>

                        <div id="file-name-text" class="file-name-display"></div>
                    </div>

                    <input type="hidden" name="course_id" value="<?php echo $this->item->course_id; ?>" />
                    <input type="hidden" name="lesson_id" value="<?php echo $this->item->id; ?>" />
                    <?php echo JHtml::_('form.token'); ?>

                    <button type="submit" class="btn-send">
                        ENVIAR PARA AVALIAÇÃO <i class="fa fa-paper-plane"></i>
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

</div> ```