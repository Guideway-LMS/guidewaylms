<?php
/**
 * @package com_splms
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2024 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
 */
// Sem Acesso Direto
defined('_JEXEC') or die('Restricted Access');

use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

Factory::getDocument()->addStyleSheet(Uri::root() . 'templates/maestro/css/splms-progress.css');
$params = JComponentHelper::getParams('com_splms');
$percentualMinimoConclusao = (int) $params->get('percentual_minimo_conclusao', 90);

// --- CONFIGURAÇÃO DE TENTATIVAS ---
$maxAttempts = 3; // Limite Total

$doc = Factory::getDocument();
$doc->addScriptOptions('splmsConfig', [
    'percentualMinimoConclusao' => $percentualMinimoConclusao,
    'text_complete' => Text::_('COM_SPLMS_LESSON_COMPLETE'),
    'text_completed' => Text::_('COM_SPLMS_LESSON_COMPLETED')
]);

$user   = $this->user ?? Factory::getUser();
$userId = (int) $user->id;
$db = Factory::getDbo();

// === INJEÇÃO GUIDEWAY: BUSCA O ID DO PROFESSOR (DONO DO CURSO) ===
$queryTeacher = $db->getQuery(true)
    ->select($db->quoteName('created_by'))
    ->from($db->quoteName('#__splms_courses'))
    ->where($db->quoteName('id') . ' = ' . (int) ($this->item->course_id ?? 0));
    
$teacher_id = (int) $db->setQuery($queryTeacher)->loadResult();
// =================================================================

// =============================================================================
// 1. CONSULTA AO BANCO
// =============================================================================
$submission = null;
$submissionCount = 0;

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
        ->order('id DESC'); 
    $db->setQuery($query, 0, 1);
    $submission = $db->loadObject();

    $queryCount = $db->getQuery(true)
        ->select('COUNT(*)')
        ->from($db->quoteName('bak_lepgs_splms_submissions'))
        ->where($db->quoteName('user_id') . ' = ' . $userId)
        ->where($db->quoteName('lesson_id') . ' = ' . (int)$this->item->id);
    $db->setQuery($queryCount);
    $submissionCount = (int) $db->loadResult();
}

$attemptsLeft = max(0, $maxAttempts - $submissionCount);

$lessonStates = [];
if ($userId && !empty($this->item->course_id)) {
    BaseDatabaseModel::addIncludePath(JPATH_SITE . '/components/com_splms/models', 'SplmsModel');
    $courseModel = BaseDatabaseModel::getInstance('Course', 'SplmsModel');
    if ($courseModel) {
        $lessonStates = $courseModel->getLessonStatesByCourse((int) $this->item->course_id, $userId);
    }
}
$this->lessonStates = $lessonStates;

$doc->addScript(Uri::root() . 'components/com_splms/assets/js/course-progress.js');
$doc->addScript(Uri::root() . 'components/com_splms/assets/js/lesson-complete-handler.js');
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
    .status-rejected { color: #ef4444; }
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
    .attempts-box { background: #f1f5f9; border-bottom: 2px solid #e2e8f0; border-radius: 8px 8px 0 0; padding: 12px 20px; margin: -40px -40px 30px -40px; display: flex; justify-content: space-between; align-items: center; font-size: 14px; color: #475569; font-weight: 600; }
    .badge-attempts { background: #3b82f6; color: white; padding: 4px 12px; border-radius: 20px; font-weight: bold; font-size: 12px; text-transform: uppercase; }
    .badge-warning-custom { background: #f59e0b; }
    .badge-danger-custom { background: #ef4444; }
    .gw-quiz-gabarito { color: inherit; }
    .gw-quiz-gabarito div { background: transparent !important; }
');

// CSS GERAL E BOTÃO DE CERTIFICADO

$doc->addStyleDeclaration('

    .upload-card { background: #ffffff; border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); padding: 40px; text-align: center; transition: transform 0.2s ease; border: 1px solid #f0f0f0; }
    .upload-zone { border: 2px dashed #e0e7ff; border-radius: 12px; padding: 30px; background: #fafbff; transition: all 0.3s ease; margin-bottom: 15px; position: relative; }
    .status-icon { font-size: 48px; margin-bottom: 15px; display: block; }
    .status-graded { color: #22c55e; }
    .grade-display { font-size: 3rem; font-weight: 800; color: #333; margin: 15px 0; }
    .btn-gw-cert { background: #1a73e8; color: white !important; padding: 15px 30px; border-radius: 8px; font-weight: bold; text-transform: uppercase; display: inline-flex; align-items: center; gap: 10px; text-decoration: none; margin-top: 20px; box-shadow: 0 4px 15px rgba(26,115,232,0.3); transition: 0.3s; border:none; }
    .btn-gw-cert:hover { background: #1557b0; transform: translateY(-2px); }
');

// --- JAVASCRIPT: A POLÍCIA DO ARQUIVO ---
$doc->addScriptDeclaration('
document.addEventListener("DOMContentLoaded", function() {
    
    var fileInput = document.getElementById("file-upload-input");
    var fileNameDisplay = document.getElementById("file-name-text");
    var zone = document.querySelector(".upload-zone");
    
    // LISTA DE PERMITIDOS (SEM ZIP/RAR)
    var allowedExts = ["pdf", "doc", "docx", "txt", "jpg", "jpeg", "png", "mp4"];

    if(fileInput) {
        fileInput.addEventListener("change", function() {
            if (this.files && this.files.length > 0) {
                var f = this.files[0];
                var ext = f.name.split(".").pop().toLowerCase();
                
                // 1. CHECAGEM DE EXTENSÃO
                if (!allowedExts.includes(ext)) {
                    this.value = ""; 
                    alert("🚫 EXTENSÃO PROIBIDA! (." + ext + ")\n\nArquivos compactados (ZIP/RAR) não são aceitos.\nEnvie apenas: PDF, Documentos, Imagens ou Vídeo.");
                    
                    fileNameDisplay.innerHTML = "<span style=\'color:red; font-weight:800;\'><i class=\'fa fa-ban\'></i> ARQUIVO INVÁLIDO (." + ext + ")</span>";
                    zone.style.borderColor = "#ef4444";
                    zone.style.background = "#fee2e2";
                    return; 
                }

                // 2. CHECAGEM DE TAMANHO
                if(f.size > 10 * 1024 * 1024) {
                    this.value = ""; 
                    alert("🚫 ARQUIVO GIGANTE!\n\nSeu arquivo tem " + (f.size/1024/1024).toFixed(1) + "MB.\nO máximo permitido é 10MB.");
                    
                    fileNameDisplay.innerHTML = "<span style=\'color:red; font-weight:800;\'><i class=\'fa fa-times\'></i> Arquivo muito grande (" + (f.size/1024/1024).toFixed(1) + "MB)</span>";
                    zone.style.borderColor = "#ef4444";
                    zone.style.background = "#fee2e2";
                    return;
                }

                // 3. SUCESSO
                fileNameDisplay.innerHTML = "<i class=\'fa fa-check-circle\'></i> " + f.name;
                zone.style.borderColor = "#4CAF50";
                zone.style.background = "#e8f5e9";
            }
        });
    }

    // Trava Extra no Botão
    var forms = document.querySelectorAll("form[enctype=\'multipart/form-data\']");
    forms.forEach(function(form) {
        form.addEventListener("submit", function(event) {
            var input = form.querySelector("input[type=file]");
            if (!input || !input.files || input.files.length === 0) return; 

            var file = input.files[0];
            var ext = file.name.split(".").pop().toLowerCase();

            if (file.size > 10 * 1024 * 1024) {
                alert("Erro: Arquivo maior que 10MB.");
                event.preventDefault();
            }
            if (!allowedExts.includes(ext)) {
                alert("Erro: Extensão ." + ext + " proibida.");
                event.preventDefault();
            }
        });
    });

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
        <div id="course-progress-bar" class="course-progress-fill" style="width: 0%; transition: width 0.6s ease;"></div>
        <div id="course-progress-text" class="course-progress-text">0%</div>
    </div>
    <div id="progress-message" class="course-progress-message">Carregando...</div>
  </div>

  <div class="row">
    <div class="col-md-7">
      <div class="splms-lesson-video-wrapper" id="splms-lesson-dynamic-area">

        <?php 
        // USANDO A VARIÁVEL ROBUSTA
        if ($isAssignment) : 
        ?>

            <div style="margin-bottom: 25px;">
                <h2 style="font-weight: 700; color: #1e293b; margin: 0; font-size: 28px;">
                    <i class="fa fa-cloud-upload" style="color: #4CAF50; margin-right: 10px;"></i> Envio de Trabalho
                </h2>
                <p style="color: #64748b; font-size: 16px; margin-top: 5px;">Esta etapa é obrigatória para a conclusão do módulo.</p>
            </div>

            <div class="upload-card">
                
                <div class="attempts-box">
                    <span><i class="fa fa-history"></i> Tentativa atual: <strong><?php echo $submissionCount; ?> de <?php echo $maxAttempts; ?></strong></span>
                    <?php if ($attemptsLeft > 0): ?>
                        <span class="badge-attempts badge-warning-custom">Restam: <?php echo $attemptsLeft; ?></span>
                    <?php else: ?>
                        <span class="badge-attempts badge-danger-custom">Esgotado</span>
                    <?php endif; ?>
                </div>

                <?php if ($submission && $submission->status == 1) : ?>
                    <i class="fa fa-check-circle status-icon status-graded"></i>
                    <h3 class="status-title status-graded">Trabalho Aprovado!</h3>

                    <div class="gw-certificate-box">
                        <h4>📜 Certificado Disponível</h4>
                        <a href="index.php?option=com_splms&task=certificate.generate&submission_id=<?php echo $submission->id; ?>" class="btn-gw-cert" target="_blank">
                            <i class="fa fa-graduation-cap"></i> BAIXAR MEU CERTIFICADO
                        </a>
                    </div>

                    <div class="grade-display"><?php echo number_format($submission->grade, 1); ?> <span style="font-size: 1rem; color: #999;">/ 100</span></div>
                    <?php if (!empty($submission->feedback)) : ?>
                        <div style="background: #f1f8e9; padding: 15px; border-radius: 8px; text-align: left; border: 1px solid #c8e6c9; margin-top: 15px;">
                            <strong style="color: #2e7d32;">Feedback do Professor:</strong>
                            <p style="margin: 5px 0 0 0; color: #333;"><?php echo $submission->feedback; ?></p>
                        </div>
                    <?php endif; ?>

                <?php elseif ($submission && $submission->status == 2) : ?>
                    <div style="border: 1px solid #fecaca; background: #fef2f2; padding: 20px; border-radius: 12px; margin-bottom: 20px;">
                        <i class="fa fa-times-circle status-icon status-rejected"></i>
                        <h3 class="status-title status-rejected">Trabalho Reprovado</h3>
                        <div class="grade-display" style="color: #ef4444;"><?php echo number_format($submission->grade, 1); ?></div>
                        <?php if (!empty($submission->feedback)) : ?>
                            <div style="background: #fff; padding: 15px; border-radius: 8px; text-align: left; border: 1px solid #fee2e2; margin-top: 15px;">
                                <strong style="color: #b91c1c;">O que melhorar:</strong>
                                <p style="margin: 5px 0 0 0; color: #333;"><?php echo $submission->feedback; ?></p>
                            </div>
                        <?php endif; ?>
                        <div style="margin-top: 15px; font-weight: bold; color: #b91c1c;">👇 Envie uma nova versão abaixo:</div>
                    </div>

                    <?php if ($attemptsLeft > 0) : ?>
                         <form id="upload-form-trabalho" action="<?php echo JRoute::_('index.php?option=com_splms&task=lesson.uploadAssignment'); ?>" method="post" enctype="multipart/form-data">
                            <div class="upload-zone">
                                <div style="font-size: 32px; color: #cbd5e1; margin-bottom: 10px;"><i class="fa fa-file-text-o"></i></div>
                                <h3 class="upload-title" style="font-size: 18px;">Enviar Correção</h3>
                                <input type="file" name="uploaded_file" id="file-upload-input" style="display: none;" required>
                                <label for="file-upload-input" class="btn-upload-custom"><i class="fa fa-folder-open-o"></i> Selecionar Novo Arquivo</label>
                                <div id="file-name-text" class="file-name-display">Nenhum arquivo selecionado</div>
                            </div>
                            <input type="hidden" name="teacher_id" value="<?php echo $teacher_id; ?>" />
                            <input type="hidden" name="course_id" value="<?php echo $this->item->course_id; ?>" />
                            <input type="hidden" name="lesson_id" value="<?php echo $this->item->id; ?>" />
                            <?php echo JHtml::_('form.token'); ?>
                            <button type="submit" class="btn-send">REENVIAR TRABALHO <i class="fa fa-refresh"></i></button>
                        </form>
                    <?php else: ?>
                        <div class="alert alert-danger"><strong>🚫 Tentativas Esgotadas.</strong> Contate o suporte.</div>
                    <?php endif; ?>

                <?php elseif ($submission && ($submission->status == 0 || is_null($submission->status))) : ?>
                    <div class="upload-zone" style="border-color: #f59e0b; background: #fffbf0;">
                        <i class="fa fa-clock-o status-icon status-pending"></i>
                        <h3 class="status-title status-pending">Aguardando Correção</h3>
                        <p style="color: #666;">Arquivo enviado com sucesso:</p>
                        <div style="background: white; padding: 8px 15px; border-radius: 20px; display: inline-block; box-shadow: 0 2px 5px rgba(0,0,0,0.05); margin-top: 10px; font-family: monospace;">
                            <i class="fa fa-file-text-o"></i> <?php echo basename($submission->file_path); ?>
                        </div>
                        <p style="font-size: 12px; color: #999; margin-top: 15px;">Enviado em: <?php echo date('d/m/Y H:i', strtotime($submission->submitted_at)); ?></p>
                    </div>

                <?php else : ?>
                    <form id="upload-form-trabalho" action="<?php echo JRoute::_('index.php?option=com_splms&task=lesson.uploadAssignment'); ?>" method="post" enctype="multipart/form-data">
                        <div class="upload-zone">
                            <div style="font-size: 32px; color: #cbd5e1; margin-bottom: 10px;"><i class="fa fa-file-text-o"></i></div>
                            <h3 class="upload-title" style="font-size: 18px;">Área de Transferência</h3>
                            <input type="file" name="uploaded_file" id="file-upload-input" style="display: none;" required>
                            <label for="file-upload-input" class="btn-upload-custom"><i class="fa fa-folder-open-o"></i> Escolher Arquivo no Computador</label>
                            <div id="file-name-text" class="file-name-display">Nenhum arquivo selecionado</div>
                            <div class="file-info-text">Formatos: PDF, Word, Imagem, MP4 (Max: 10MB)</div>
                        </div>
                        <div class="comment-wrapper">
                            <label for="student_comment" class="comment-label">Comentário (Opcional):</label>
                            <textarea name="student_comment" id="student_comment" class="comment-textarea" placeholder="Escreva uma mensagem para o professor..."></textarea>
                        </div>
                        <input type="hidden" name="teacher_id" value="<?php echo $teacher_id; ?>" />
                        <input type="hidden" name="course_id" value="<?php echo $this->item->course_id; ?>" />
                        <input type="hidden" name="lesson_id" value="<?php echo $this->item->id; ?>" />
                        <?php echo JHtml::_('form.token'); ?>
                        <button type="submit" class="btn-send">ENVIAR TRABALHO <i class="fa fa-paper-plane"></i></button>
                    </form>
                <?php endif; ?>
            </div>

            <div style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #eee;">
                <h5 style="font-weight: 700; font-size: 16px; margin-bottom: 10px; text-transform: uppercase; letter-spacing: 0.5px;">Sobre esta atividade:</h5>
                <div class="splms-lesson-description" style="font-size: 14px; line-height: 1.6;"><?php echo $this->item->description; ?></div>
            </div>

        <?php else : ?>
            <?php 
            // GUIDEWAY CUSTOM: Verificar se é uma lição do tipo Quiz
            $isQuizLesson = (isset($this->item->lesson_format) && $this->item->lesson_format === 'quiz' && !empty($this->item->quiz_id));
            ?>
            <?php if ($isQuizLesson) : ?>
                <?php 
                // Construir URL do Quiz
                $quizUrl = Route::_('index.php?option=com_splms&view=quizquestion&id=' . (int)$this->item->quiz_id . '&course_id=' . (int)$this->item->course_id . '&lesson_id=' . (int)$this->item->id . SplmsHelper::getItemid('courses'));
                
                // GUIDEWAY CUSTOM: Buscar resultado real do quiz no BD
                $quizResult = null;
                if ($userId > 0) {
                    $qrQuery = $db->getQuery(true)
                        ->select('point, total_marks')
                        ->from('#__splms_quizresults')
                        ->where('user_id = ' . $userId)
                        ->where('quizquestion_id = ' . (int)$this->item->quiz_id)
                        ->where('published = 1')
                        ->order('id DESC');
                    $db->setQuery($qrQuery, 0, 1);
                    $quizResult = $db->loadObject();
                }
                
                $hasTakenQuiz = !empty($quizResult);
                $quizScore = $hasTakenQuiz ? (int)$quizResult->point : 0;
                $quizTotal = $hasTakenQuiz ? (int)$quizResult->total_marks : 0;
                $quizPercent = ($quizTotal > 0) ? round(($quizScore / $quizTotal) * 100) : 0;
                $passingScore = !empty($this->item->passing_score) ? (int)$this->item->passing_score : 70;
                $quizPassed = $hasTakenQuiz && ($quizPercent >= $passingScore);
                ?>
                <div style="margin-bottom: 25px;">
                    <h2 class="quiz-section-title" style="font-weight: 700; margin: 0; font-size: 28px;">
                        <i class="fa fa-question-circle" style="color: #3b82f6; margin-right: 10px;"></i> Quiz
                    </h2>
                    <p class="quiz-section-desc" style="font-size: 16px; margin-top: 5px;">Responda o quiz para concluir esta etapa.</p>
                </div>

                <div class="upload-card" style="text-align: center;">
                    <?php if ($hasTakenQuiz && $quizPassed) : ?>
                        <i class="fa fa-check-circle" style="font-size: 48px; color: #22c55e; display: block; margin-bottom: 15px;"></i>
                        <h3 style="font-size: 22px; font-weight: 700; color: #22c55e; margin-bottom: 10px;">Quiz Concluído!</h3>
                        <div style="background: #eff6ff; padding: 20px; border: 2px solid #3b82f6; border-radius: 12px; margin-top: 20px;">
                            <h4 style="color: #1e40af;">🎓 Parabéns! Certificado Liberado.</h4>
                            <a href="index.php?option=com_splms&task=certificate.generate&submission_id=<?php echo $submission->id ?? 14; ?>" class="btn-gw-cert" target="_blank">
                                <i class="fa fa-certificate"></i> GERAR CERTIFICADO DE TESTE
                            </a>
                        </div>
                        <div style="font-size: 3rem; font-weight: 800; color: #22c55e; margin: 15px 0;">
                            <?php echo $quizPercent; ?>%
                        </div>
                        <p style="color: #64748b;">Acertos: <strong><?php echo $quizScore; ?></strong> de <strong><?php echo $quizTotal; ?></strong></p>
                        <a href="<?php echo $quizUrl; ?>" class="btn btn-primary" style="margin-top: 15px; padding: 12px 30px; border-radius: 8px; font-weight: 600;">
                            <i class="fa fa-refresh"></i> Refazer Quiz
                        </a>

                    <?php elseif ($hasTakenQuiz && !$quizPassed) : ?>
                        <i class="fa fa-times-circle" style="font-size: 48px; color: #ef4444; display: block; margin-bottom: 15px;"></i>
                        <h3 style="font-size: 22px; font-weight: 700; color: #ef4444; margin-bottom: 10px;">Nota Insuficiente</h3>
                        <div style="font-size: 3rem; font-weight: 800; color: #ef4444; margin: 15px 0;">
                            <?php echo $quizPercent; ?>%
                        </div>
                        <p style="color: #64748b;">Acertos: <strong><?php echo $quizScore; ?></strong> de <strong><?php echo $quizTotal; ?></strong></p>
                        <?php if ($passingScore > 0) : ?>
                            <p style="color: #f59e0b; font-weight: 600;">Nota mínima: <?php echo $passingScore; ?>%</p>
                        <?php endif; ?>
                        <a href="<?php echo $quizUrl; ?>" class="btn btn-primary" style="margin-top: 15px; padding: 15px 40px; border-radius: 8px; font-size: 18px; font-weight: 700; background: #f59e0b; border: none; display: inline-flex; align-items: center; gap: 10px;">
                            <i class="fa fa-refresh"></i> Tentar Novamente
                        </a>

                    <?php else : ?>
                        <i class="fa fa-pencil-square-o" style="font-size: 48px; color: #3b82f6; display: block; margin-bottom: 15px;"></i>
                        <h3 style="font-size: 22px; font-weight: 700; color: #1e293b; margin-bottom: 10px;">Pronto para o Quiz?</h3>
                        <?php if ($passingScore > 0) : ?>
                            <p style="color: #64748b; margin-bottom: 5px;">
                                Nota mínima para aprovação: <strong style="color: #f59e0b;"><?php echo $passingScore; ?>%</strong>
                            </p>
                        <?php endif; ?>
                        <a href="<?php echo $quizUrl; ?>" class="btn btn-primary" style="margin-top: 20px; padding: 15px 40px; border-radius: 8px; font-size: 18px; font-weight: 700; background: #3b82f6; border: none; display: inline-flex; align-items: center; gap: 10px;">
                            <i class="fa fa-play-circle"></i> Iniciar Quiz
                        </a>
                    <?php endif; ?>
                </div>

                <?php if (!empty($this->item->description)) : ?>
                <div style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #eee;">
                    <h5 style="font-weight: 700; color: #555; font-size: 16px; margin-bottom: 10px; text-transform: uppercase; letter-spacing: 0.5px;">Sobre este Quiz:</h5>
                    <div class="splms-lesson-description" style="color: #666; font-size: 14px; line-height: 1.6;"><?php echo $this->item->description; ?></div>
                </div>
                <?php endif; ?>

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

        <?php endif; ?>

      </div>
    </div>
    
    <div class="col-md-5">
      <?php if (!empty($this->lessons) && count($this->lessons) && $this->lessons) { ?>
        <div class="course-lessons">
          <h3><?php echo Text::_('COM_SPLMS_LESOSNS_LIST'); ?></h3>
          <ul class="lessons list-unstyled">
            <?php foreach ($this->lessons as $lesson) { 
                $active_lesson = ($this->item->id == $lesson->id) ? ' active' : '';
                $state = $this->lessonStates[$lesson->id] ?? 0;
                $isCompleted = ($state === 1);
                $isPending   = ($state === 2);
            ?>
              <?php if ($lesson->lesson_type == 0 || $this->isAuthorised != '' || $this->courese->price == 0) : ?>
                <li class="lesson<?php echo $active_lesson; ?><?php echo $isCompleted ? ' lesson-completed' : ''; ?><?php echo $isPending ? ' lesson-pending' : ''; ?>" data-lesson-id="<?php echo (int) $lesson->id; ?>">
                  <a href="<?php echo $lesson->lesson_url; ?>">
                    <span class="lesson-title">
                      <?php echo $lesson->title; ?>
                      <?php if ($isCompleted) : ?><span class="lesson-completed-icon"> ✅</span>
                      <?php elseif ($isPending) : ?><span class="lesson-pending-icon"> ⏳</span><?php endif; ?>
                    </span>
                  </a>
                </li>
              <?php else : ?>
                <li class="lesson splms-lesson-unauthorised"><span><i class="splms-icon-book"></i><i class="splms-icon-lock"></i><?php echo $lesson->title; ?></span></li>
              <?php endif; ?>
            <?php } ?>
          </ul>
        </div>
      <?php } ?>
    </div>
  </div>

  <div class="splms-lesson-completed-lesson-wrapper" <?php if (isset($this->item->course_id)) : ?> data-course-id="<?php echo (int) $this->item->course_id; ?>" <?php endif; ?> >
    <?php 
    // GUIDEWAY CUSTOM: Ocultar botão de conclusão manual para lições de quiz e atividades
    $isQuizLessonBottom = (isset($this->item->lesson_format) && $this->item->lesson_format === 'quiz' && !empty($this->item->quiz_id));
    if (!$isAssignment && !$isQuizLessonBottom) : 
    ?>
        <?php if ($this->user->guest) { $link =  base64_encode(Uri::getInstance()->toString()); $login_link = Route::_('index.php?option=com_users&view=login' . SplmsHelper::getItemid('login') . '&return=' . $link); ?>
          <a class="btn btn-primary" href="<?php echo $login_link; ?>"><?php echo Text::_('COM_SPLMS_LOGIN_TO_COMPLETE'); ?></a>
        <?php } elseif (!$this->has_complete_lesson) { ?>
          <form id="splms-completed-item-form"><input type="hidden" name="user_id" value="<?php echo $this->user->id; ?>"><input type="hidden" name="item_id" value="<?php echo $this->item->id; ?>"><input type="hidden" name="item_type" value="lesson"><input type="hidden" name="course_id" value="<?php echo isset($this->item->course_id) ? (int) $this->item->course_id : ''; ?>"><a class="btn btn-primary" id="splms-completed-item" href="#"><?php echo Text::_('COM_SPLMS_LESSON_COMPLETE'); ?></a></form>
        <?php } else { ?>
          <a class="btn btn-primary" id="splms-completed-item" href="#"><?php echo Text::_('COM_SPLMS_LESSON_COMPLETED'); ?></a>
        <?php } ?>
    <?php endif; ?>
  </div>

</div>

<script>
jQuery(function($) {
    "use strict";

    window.atualizarInterfaceCurso = function() {
        var url = window.location.href.split('#')[0];
        url += (url.indexOf('?') !== -1 ? '&' : '?') + 'nocache=' + new Date().getTime();

        $.get(url, function(html) {
            var doc = $(html);
            
            // 1. Substitui APENAS a Lista Lateral (Ela trará o ✅ do banco de dados)
            var novaLista = doc.find('.course-lessons');
            if (novaLista.length > 0) {
                $('.course-lessons').replaceWith(novaLista);
            }

            // 2. Substitui EXCLUSIVAMENTE o card de upload.
            // Se for um vídeo, o vídeo não tem a classe .upload-card, então ele fica intocado na tela!
            var novoUploadCard = doc.find('.upload-card');
            if (novoUploadCard.length > 0 && $('.upload-card').length > 0) {
                $('.upload-card').replaceWith(novoUploadCard);
            }

            // 3. MATEMÁTICA PURA (Corrigida com decimais e preservando o subtexto)
            var total = $('.course-lessons .lesson').length;
            var concluidas = $('.course-lessons .lesson-completed').length;
            
            if (total > 0) {
                var percentReal = (concluidas / total) * 100;
                
                // Formata os decimais corretamente com vírgula (Ex: 33,33)
                var percentFormatado = (percentReal % 1 === 0) 
                    ? percentReal 
                    : percentReal.toFixed(2).replace('.', ',');
                
                // Injeta APENAS a porcentagem na barra mantendo a DIV original para o CSS agir
                $('#course-progress-bar').css('width', percentReal + '%');
                $('#course-progress-text').text(percentFormatado + '%');
                
                // Nota: O $('#progress-message') que troca a frase foi removido.
                
                if (percentReal === 100) {
                    $('#progress-emoji').text('🏆');
                }
            }
        });
    };

    // Escuta a notificação global do vídeo ou do botão manual (quando termina a aula)
    $(document).ajaxSuccess(function(event, xhr, settings) {
        if (settings.url && settings.url.indexOf('task=lesson.completeditem') !== -1) {
            window.atualizarInterfaceCurso();
        }
    });

    // Escuta específica para o formulário de envio de trabalho
    $('body').on('submit', '#upload-form-trabalho', function(e) {
        e.preventDefault(); 
        
        var form = $(this);
        var btn = form.find('.btn-send');
        var originalText = btn.html();
        
        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Enviando Trabalho...');
        
        var formData = new FormData(this);
        // CORREÇÃO: 'this' é o formulário puro! Impede erro invisível e quebra de tela.
        var formData = new FormData(this);
        
        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: formData,
            processData: false, 
            contentType: false, 
            success: function(response) {
                btn.removeClass('btn-send').css({'background-color': '#22c55e', 'color': 'white'})
                   .html('<i class="fa fa-check"></i> Enviado com Sucesso!');
                
                window.atualizarInterfaceCurso();
                
                if (typeof mostrarAlertaConclusao === "function") {
                    mostrarAlertaConclusao();
                }
            },
            error: function() {
                btn.prop('disabled', false).html(originalText);
                alert("Ocorreu um erro ao enviar o trabalho. Verifique o tamanho do arquivo ou atualize a página.");
            }
        });
    });
});
</script>
