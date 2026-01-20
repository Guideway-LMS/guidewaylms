<?php
/**
 * @package     SP LMS
 * @subpackage  Components
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Router\Route;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;

$user = Factory::getUser();
?>

<div class="splms-forum-container">
	<div class="mb-3">
		<a href="javascript:history.back()" class="btn btn-secondary btn-sm">&larr; Voltar</a>
	</div>

	<!-- Pergunta Principal -->
	<div class="card mb-4 question-detail">
		<div class="card-header">
			<h2><?php echo $this->escape($this->item->title); ?></h2>
			<small class="text-muted">
				Perguntado por <strong><?php echo $this->item->author_name; ?></strong> 
				em <?php echo HTMLHelper::_('date', $this->item->created_on, 'd/m/Y H:i'); ?>
			</small>
            
            <?php if (($user->id == $this->item->user_id) || $user->authorise('core.admin')): ?>
                <div class="float-end" style="float: right;">
                    <a href="<?php echo Route::_('index.php?option=com_splms&view=forum&layout=edit&id=' . $this->item->id); ?>" 
                       class="btn btn-warning btn-sm me-1" title="Editar">
                        <i class="fa fa-pencil"></i>
                    </a>
                    <a href="<?php echo Route::_('index.php?option=com_splms&task=forum.deleteQuestion&id=' . $this->item->id . '&course_id=' . $this->item->course_id . '&' . \Joomla\CMS\Session\Session::getFormToken() . '=1'); ?>" 
                       class="btn btn-danger btn-sm" 
                       onclick="return confirm('Tem certeza? Isso apagará a pergunta e todas as respostas.');">
                        <i class="fa fa-trash"></i>
                    </a>
                </div>
            <?php endif; ?>
		</div>
		<div class="card-body">
			<?php echo $this->item->body; // Cuidado com XSS se não for trusted, mas aqui assumimos conteúdo user-generated permitido ?>
		</div>
	</div>

	<!-- Lista de Respostas -->
	<h3 class="mb-3"><?php echo count($this->answers); ?> Respostas</h3>
	
	<?php if (!empty($this->answers)) : ?>
		<?php foreach ($this->answers as $answer) : ?>
			<div class="card mb-3 answer-card <?php echo ($answer->is_accepted) ? 'border-success is-accepted' : ''; ?>">
				<div class="card-body">
					<?php if ($answer->is_accepted) : ?>
						<div class="accepted-badge text-success mb-2">
							<i class="fa fa-check"></i> Solução Aceita
						</div>
					<?php endif; ?>
					
					<div class="answer-body">
						<?php echo $answer->body; ?>
					</div>
					
					<div class="text-end text-muted small mt-2 d-flex justify-content-between align-items-center">
                        <div>
                            Respondido por <strong><?php echo $answer->author_name; ?></strong>
                            em <?php echo HTMLHelper::_('date', $answer->created_on, 'd/m/Y H:i'); ?>
                        </div>
                        
                        <!-- Acoes da Resposta -->
                        <div class="answer-actions">
                            <?php 
                            $isQuestionOwner = ($user->id == $this->item->user_id);
                            $isAnswerOwner = ($user->id == $answer->user_id);
                            $isAdmin = $user->authorise('core.admin');
                            ?>

                            <?php if (($isQuestionOwner || $isAdmin) && !$answer->is_accepted): ?>
                                <a href="<?php echo Route::_('index.php?option=com_splms&task=forum.acceptAnswer&answer_id=' . $answer->id . '&question_id=' . $this->item->id . '&' . \Joomla\CMS\Session\Session::getFormToken() . '=1'); ?>" class="btn btn-sm btn-outline-success" title="Marcar como Solução">
                                    <i class="fa fa-check"></i> Aceitar
                                </a>
                            <?php endif; ?>

                            <?php if ($isAnswerOwner || $isAdmin): ?>
                                <a href="<?php echo Route::_('index.php?option=com_splms&task=forum.deleteAnswer&id=' . $answer->id . '&question_id=' . $this->item->id . '&' . \Joomla\CMS\Session\Session::getFormToken() . '=1'); ?>" 
                                   class="btn btn-sm btn-outline-danger" 
                                   onclick="return confirm('Tem certeza que deseja excluir esta resposta?');" 
                                   title="Excluir Resposta">
                                    <i class="fa fa-trash"></i>
                                </a>
                            <?php endif; ?>
                        </div>
					</div>
                </div>
			</div>
		<?php endforeach; ?>
	<?php endif; ?>

	<!-- Formulário de Resposta -->
	<?php if ($user->id > 0) : ?>
		<div class="card mt-4">
			<div class="card-header">Sua Resposta</div>
			<div class="card-body">
				<form action="<?php echo Route::_('index.php?option=com_splms'); ?>" method="post">
					<div class="mb-3">
						<textarea class="form-control" name="body" rows="6" required placeholder="Escreva sua solução aqui details..."></textarea>
					</div>
					<input type="hidden" name="task" value="forum.saveAnswer" />
					<input type="hidden" name="question_id" value="<?php echo $this->item->id; ?>" />
					<?php echo HTMLHelper::_('form.token'); ?>
					<button type="submit" class="btn btn-primary">Enviar Resposta</button>
				</form>
			</div>
		</div>
	<?php else : ?>
		<div class="alert alert-warning mt-4">Faça login para responder.</div>
	<?php endif; ?>
</div>
