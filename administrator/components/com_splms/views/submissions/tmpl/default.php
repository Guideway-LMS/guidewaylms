<?php
/**
 * @package     com_splms
 * @license     GNU/GPLv2 or later
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

HTMLHelper::_('behavior.core');
HTMLHelper::_('bootstrap.tooltip');
?>

<div class="row-fluid">
    <div id="j-main-container" class="span12">
        
        <div style="margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 10px;">
            <h1 style="margin: 0;">🎓 Gerenciamento de Trabalhos</h1>
        </div>

        <form action="<?php echo Route::_('index.php?option=com_splms&view=submissions'); ?>" method="post" name="adminForm" id="adminForm">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th width="1%" class="text-center">#</th>
                        <th>Data</th>
                        <th>Aluno</th>
                        <th>Curso</th>
                        <th>Lição</th>
                        <th class="text-center">Arquivo</th>
                        <th class="text-center">Nota</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Ação</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!empty($this->items)) : ?>
                    <?php foreach ($this->items as $item) : ?>
                        <tr>
                            <td class="text-center"><?php echo $item->submission_id; ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($item->submitted_at)); ?></td>
                            <td><?php echo $item->student_name; ?></td>
                            <td><small><?php echo $item->course_title; ?></small></td>
                            <td><?php echo $item->lesson_title; ?></td>
                            <td class="text-center">
                                <a href="<?php echo Uri::root() . $item->file_path; ?>" target="_blank" class="btn btn-mini btn-info"><span class="icon-download"></span></a>
                            </td>
                            <td class="text-center">
                                <?php echo ($item->grade > 0) ? number_format($item->grade, 1) : '-'; ?>
                            </td>
                            <td class="text-center">
                                <?php echo ($item->status == 1) ? '<span class="label label-success">Corrigido</span>' : '<span class="label label-warning">Pendente</span>'; ?>
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-mini btn-primary btn-evaluate" 
                                        data-id="<?php echo $item->submission_id; ?>" 
                                        data-grade="<?php echo $item->grade; ?>" 
                                        data-feedback="<?php echo htmlspecialchars($item->feedback ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                    Avaliar
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
            <input type="hidden" name="task" value="" />
            <?php echo HTMLHelper::_('form.token'); ?>
        </form>
    </div>
</div>

<div id="gradeModal" style="display:none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 10000;">
    
    <div id="gradeModalContent" style="background: #fff; width: 500px; margin: 100px auto; padding: 20px; border-radius: 5px; box-shadow: 0 0 15px rgba(0,0,0,0.5); position: relative;" onmousedown="event.stopPropagation()">
        
        <div style="border-bottom: 1px solid #eee; margin-bottom: 15px; padding-bottom: 10px;">
            <button type="button" class="close btn-close-modal" style="float: right;">&times;</button>
            <h3 style="margin:0;">📝 Avaliar Trabalho</h3>
        </div>
        
        <form action="<?php echo Route::_('index.php?option=com_splms&task=submissions.saveGrade'); ?>" method="post">
            <input type="hidden" name="submission_id" id="modal_submission_id">
            
            <div style="margin-bottom: 15px;">
                <label>Nota (0-100):</label>
                <input type="number" name="grade" id="modal_grade" class="input-small" step="0.1" required style="font-weight:bold;">
            </div>

            <div style="margin-bottom: 15px;">
                <label>Feedback:</label>
                <textarea name="feedback" id="modal_feedback" rows="5" style="width: 95%;"></textarea>
            </div>
            
            <div style="text-align: right; border-top: 1px solid #eee; padding-top: 10px;">
                <button type="button" class="btn btn-close-modal">Cancelar</button>
                <button type="submit" class="btn btn-success">Salvar</button>
            </div>
            <?php echo HTMLHelper::_('form.token'); ?>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var modal = document.getElementById('gradeModal');

    // 1. ABRIR
    document.querySelectorAll('.btn-evaluate').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            document.getElementById('modal_submission_id').value = this.dataset.id;
            document.getElementById('modal_grade').value = this.dataset.grade > 0 ? this.dataset.grade : '';
            document.getElementById('modal_feedback').value = this.dataset.feedback;
            modal.style.display = 'block';
        });
    });

    // 2. FECHAR (Botões)
    document.querySelectorAll('.btn-close-modal').forEach(function(btn) {
        btn.addEventListener('click', function() { modal.style.display = 'none'; });
    });

    // 3. FECHAR AO CLICAR NO FUNDO
    modal.addEventListener('mousedown', function() {
        modal.style.display = 'none';
    });
});
</script>