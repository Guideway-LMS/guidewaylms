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

// acessa os parametros do do templete
$params = JComponentHelper::getParams('com_splms');
$percentualMinimoConclusao = (int) $params->get('percentual_minimo_conclusao', 90);

//define a variavel do percentual 
$doc = Factory::getDocument();
$doc->addScriptOptions('splmsConfig', [
    'percentualMinimoConclusao' => $percentualMinimoConclusao
]);

// Carrega JS e CSS da notificação (Sprint 4 - Iris)
$doc = Factory::getDocument();

$doc->addScript(Uri::root() . 'components/com_splms/assets/js/course-progress.js');

$doc->addScript(Uri::root() . 'media/gw-progress-alert/js/alerta-conclusao.js');
$doc->addStyleSheet(Uri::root() . 'media/gw-progress-alert/css/alerta-conclusao.css');

// GUIDEWAY CUSTOM - Joshua - Carregar handler de conclusão de aula
$doc->addScript(Uri::root() . 'components/com_splms/assets/js/lesson-complete-handler.js');

$doc->addStyleSheet(Uri::root() . 'media/gw-progress-alert/css/alerta-conclusao.css');

?>

<div id="splms" class="splms splms-lessons splms-lesson-details">
	<!-- GUIDEWAY CUSTOM - 2025-11-12 - Joshua - Barra de Progresso Visual -->
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

				<?php // INICIO DO BLOCO TRABALHO ?>
   				 <?php 
// 1. Define o formato (se não tiver nada salvo, assume que é vídeo)
$formato = isset($this->item->lesson_format) ? $this->item->lesson_format : 'video';

// 2. Verifica se é Trabalho
if ($formato == 'trabalho') : ?>

<div class="splms-upload-container" style="background: #fff; border: 1px solid #e0e0e0; border-radius: 8px; padding: 40px; text-align: center; max-width: 800px; margin: 0 auto;">
    
    <div style="font-size: 48px; color: #333; margin-bottom: 20px;">
        <i class="fa fa-cloud-upload"></i> </div>
    
    <h2 style="font-size: 24px; color: #333; font-weight: bold; margin-bottom: 10px;">
        <?php echo Text::_('Envio de Trabalho'); ?>
    </h2>
    
    <p style="color: #666; font-size: 16px; margin-bottom: 30px;">
        <?php echo Text::_('Esta lição requer o envio de um arquivo para avaliação.'); ?>
    </p>

    <div class="upload-box" style="background: #e3f2fd; border: 2px dashed #2196f3; border-radius: 8px; padding: 40px; margin-bottom: 30px;">
        
        <form action="<?php echo Route::_('index.php?option=com_splms&task=lesson.submit'); ?>" 
              method="post" 
              enctype="multipart/form-data">
            
            <label for="student_file" class="btn btn-primary btn-lg" style="cursor: pointer; padding: 15px 30px; font-size: 16px;">
                <i class="fa fa-folder-open"></i> Selecionar Arquivo
            </label>
            <input type="file" name="student_file" id="student_file" style="display: none;" required onchange="document.getElementById('file-name').textContent = this.files[0].name;">
            
            <p id="file-name" style="margin-top: 15px; font-weight: bold; color: #2196f3;">Nenhum arquivo selecionado</p>
            <p style="font-size: 12px; color: #888; margin-top: 5px;">Formatos: PDF, ZIP, MP4 (Máx: 10MB)</p>

            <div style="margin-top: 20px; text-align: left;">
                <label style="font-weight: bold; color: #555;">Comentário (Opcional):</label>
                <textarea name="student_comment" class="form-control" rows="3" placeholder="Escreva uma mensagem para o professor..."></textarea>
            </div>

            <input type="hidden" name="course_id" value="<?php echo $this->item->course_id; ?>" />
            <input type="hidden" name="lesson_id" value="<?php echo $this->item->id; ?>" />
            <?php echo JHtml::_('form.token'); ?>

            <button type="submit" class="btn btn-success btn-block btn-lg" style="margin-top: 20px; width: 100%;">
                ENVIAR TRABALHO
            </button>

        </form>
    </div>
</div>

<?php else : ?>

    <div class="splms-lesson-content">
        <?php if (!empty($this->item->video_url)) : ?>
            <div class="lesson-video">
                <?php echo LayoutHelper::render('player', array('video' => $this->item->video_url, 'thumbnail' => $this->item->vdo_thumb)); ?>
            </div>
        <?php endif; ?>

        <div class="lesson-description">
            <?php echo $this->item->description; ?>
        </div>
        
        <?php if (!empty($this->item->attachment)) : ?>
            <div class="lesson-attachment">
                <a href="<?php echo $this->item->attachment; ?>" target="_blank" class="btn btn-secondary">
                    <i class="fa fa-file-pdf-o"></i> Baixar Material de Apoio
                </a>
            </div>
        <?php endif; ?>
    </div>

<?php endif; ?>
    <?php // FIM DO BLOCO TRABALHO ?>

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
					<!-- <h2><?php echo $this->item->title; ?></h2> -->
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

	<!-- <div class="splms-lesson-completed-lesson-wrapper">
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
	</div> -->

	<?php 
// SÓ MOSTRA O BOTÃO SE NÃO FOR TRABALHO
// (Porque no trabalho, o envio conta como conclusão)
if ($formato != 'trabalho') : ?>

    <div class="splms-lesson-completed-lesson-wrapper">
        <?php if ($this->user->guest) {
            // ... código original do botão ...
        } else { ?>
            <a class="btn btn-primary" id="splms-completed-item" href="#">
                <?php echo Text::_('COM_SPLMS_LESSON_COMPLETED'); ?>
            </a>
        <?php } ?>
    </div>

<?php endif; ?>

</div> <!-- /#splms -->
