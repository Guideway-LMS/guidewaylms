<?php
/**
 * @package     com_splms
 * @license     GNU/GPLv2 or later
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Uri\Uri;

// Carrega comportamentos padrões do Joomla
HTMLHelper::_('behavior.core');
HTMLHelper::_('bootstrap.tooltip');
?>

<div id="j-main-container" class="span12">
    
    <div style="margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 10px;">
        <h1 style="margin: 0;">🎓 Gerenciamento de Trabalhos</h1>
        <small class="text-muted">Visualize os envios, baixe arquivos e atribua notas.</small>
    </div>
    
    <?php if (!empty($this->sidebar)) : ?>
        <div id="j-sidebar-container" class="span2">
            <?php echo $this->sidebar; ?>
        </div>
    <?php endif; ?>

    <form action="<?php echo Route::_('index.php?option=com_splms&view=submissions'); ?>" method="post" name="adminForm" id="adminForm">
        
        <table class="table table-striped table-hover" id="articleList">
            <thead>
                <tr>
                    <th width="1%" class="text-center">#</th>
                    <th width="15%">Data de Envio</th>
                    <th width="20%">Aluno</th>
                    <th width="25%">Lição / Aula</th>
                    <th width="10%" class="text-center">Arquivo</th>
                    <th width="10%" class="text-center">Nota</th>
                    <th width="10%" class="text-center">Status</th>
                    <th width="10%" class="text-center">Ação</th>
                </tr>
            </thead>
            <tbody>
            <?php if (!empty($this->items)) : ?>
                <?php foreach ($this->items as $i => $item) : ?>
                    <tr class="row<?php echo $i % 2; ?>">
                        <td class="text-center"><?php echo $item->submission_id; ?></td>
                        <td>
                            <?php echo date('d/m/Y', strtotime($item->submitted_at)); ?> <br>
                            <small class="text-muted"><?php echo date('H:i', strtotime($item->submitted_at)); ?></small>
                        </td>
                        <td>
                            <div style="font-weight:bold;"><?php echo $item->student_name; ?></div>
                            <div class="small text-muted">User: <?php echo $item->username; ?></div>
                        </td>
                        <td><?php echo $item->lesson_title; ?></td>
                        <td class="text-center">
                            <a href="<?php echo Uri::root() . $item->file_path; ?>" target="_blank" class="btn btn-sm btn-outline-secondary has-tooltip" title="Baixar Arquivo">
                                <span class="icon-download" aria-hidden="true"></span> Baixar
                            </a>
                        </td>
                        <td class="text-center">
                            <?php if($item->grade > 0): ?>
                                <span class="badge bg-success" style="font-size:1rem;"><?php echo number_format($item->grade, 1); ?></span>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <?php if($item->status == 1): ?>
                                <span class="badge bg-success">Corrigido</span>
                            <?php else: ?>
                                <span class="badge bg-warning text-dark">Pendente</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-primary btn-evaluate" 
                                    data-id="<?php echo $item->submission_id; ?>" 
                                    data-grade="<?php echo $item->grade; ?>" 
                                    data-feedback="<?php echo htmlspecialchars($item->feedback ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                <span class="icon-pencil" aria-hidden="true"></span> Avaliar
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="8" class="text-center alert alert-info">Nenhum trabalho encontrado.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
        
        <input type="hidden" name="task" value="" />
        <input type="hidden" name="boxchecked" value="0" />
        <?php echo HTMLHelper::_('form.token'); ?>
    </form>
</div>

<div class="modal" id="gradeModal" tabindex="-1" role="dialog" aria-hidden="true" style="display:none; background: rgba(0,0,0,0.5);">
    <div class="modal-dialog modal-dialog-centered" role="document" style="margin-top: 5%;">
        <div class="modal-content shadow-lg">
            
            <div class="modal-header bg-light">
                <h3 class="modal-title h5" style="margin:0;">📝 Avaliar Trabalho</h3>
                <button type="button" class="close btn-close-modal" aria-label="Close" style="border:none; background:none; font-size:1.5rem;">&times;</button>
            </div>
            
            <form action="<?php echo Route::_('index.php?option=com_splms&task=submissions.saveGrade'); ?>" method="post">
                <div class="modal-body p-4">
                    <input type="hidden" name="submission_id" id="modal_submission_id" value="">
                    
                    <div class="mb-3 control-group">
                        <label class="form-label control-label"><strong>Nota Final (0 a 100):</strong></label>
                        <div class="controls">
                            <input type="number" name="grade" id="modal_grade" class="form-control input-small" min="0" max="100" step="0.1" required style="width: 100px; font-weight: bold; color: #198754;">
                        </div>
                    </div>

                    <div class="mb-3 control-group">
                        <label class="form-label control-label"><strong>Parecer / Feedback:</strong></label>
                        <div class="controls">
                            <textarea name="feedback" id="modal_feedback" rows="5" class="form-control" style="width: 100%;" placeholder="Escreva aqui o feedback para o aluno..."></textarea>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-close-modal">Cancelar</button>
                    <button type="submit" class="btn btn-success"><span class="icon-save"></span> Salvar Avaliação</button>
                </div>
                
                <?php echo HTMLHelper::_('form.token'); ?>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // Elementos do Modal
    var modal = document.getElementById('gradeModal');
    var inputId = document.getElementById('modal_submission_id');
    var inputGrade = document.getElementById('modal_grade');
    var inputFeedback = document.getElementById('modal_feedback');

    // 1. ABRIR MODAL
    // Adiciona evento de clique em todos os botões "Avaliar"
    var buttons = document.querySelectorAll('.btn-evaluate');
    buttons.forEach(function(btn) {
        btn.addEventListener('click', function() {
            // Pega dados seguros do botão
            var id = this.getAttribute('data-id');
            var grade = this.getAttribute('data-grade');
            var feedback = this.getAttribute('data-feedback');

            // Preenche o formulário
            inputId.value = id;
            inputGrade.value = (grade > 0) ? grade : '';
            inputFeedback.value = feedback;

            // Mostra o modal (CSS direto para evitar conflito de versão do Bootstrap)
            modal.style.display = 'block';
            modal.classList.add('show');
        });
    });

    // 2. FECHAR MODAL
    // Botões de fechar e cancelar
    var closeButtons = document.querySelectorAll('.btn-close-modal');
    closeButtons.forEach(function(btn) {
        btn.addEventListener('click', function() {
            modal.style.display = 'none';
            modal.classList.remove('show');
        });
    });

    // Fechar ao clicar fora (no fundo escuro)
    window.addEventListener('click', function(event) {
        if (event.target == modal) {
            modal.style.display = 'none';
            modal.classList.remove('show');
        }
    });
});
</script>