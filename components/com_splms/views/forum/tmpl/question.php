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
$user = Factory::getUser();

// Basic CSS for voting
Factory::getDocument()->addStyleDeclaration('
    .vote-section .vote-btn { color: #ccc; }
    .vote-section .vote-btn:hover { color: #666; }
    .vote-section .vote-btn.upvote.active { color: #28a745; } /* Green */
    .vote-section .vote-btn.downvote.active { color: #dc3545; } /* Red */
    .vote-section .vote-count { display: block; text-align: center; }
');
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
            
            <?php if (!empty($this->item->tags)): ?>
                <div class="mt-2">
                    <?php foreach (explode(',', $this->item->tags) as $tag): ?>
                        <span class="badge bg-info text-dark me-1"><?php echo trim($this->escape($tag)); ?></span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
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
		<div class="card-body d-flex">
            <!-- Vote Section Question -->
            <div class="vote-section me-3 d-flex flex-column align-items-center" style="min-width: 40px;">
                <button type="button" class="btn btn-link p-0 text-decoration-none vote-btn upvote <?php echo ($this->item->user_vote == 1) ? 'active' : ''; ?>" 
                        onclick="splmsVote(<?php echo $this->item->id; ?>, 'question', 1, this)">
                    <i class="fa fa-thumbs-up fa-2x"></i>
                </button>
                <span class="vote-count-up fs-5 fw-bold text-success"><?php echo (int)$this->item->upvotes; ?></span>
                
                <button type="button" class="btn btn-link p-0 text-decoration-none vote-btn downvote mt-2 <?php echo ($this->item->user_vote == -1) ? 'active' : ''; ?>" 
                        onclick="splmsVote(<?php echo $this->item->id; ?>, 'question', -1, this)">
                    <i class="fa fa-thumbs-down fa-2x"></i>
                </button>
                <span class="vote-count-down fs-5 fw-bold text-danger"><?php echo (int)$this->item->downvotes; ?></span>
            </div>
            
            <div class="flex-grow-1">
			    <?php echo $this->item->body; // Cuidado com XSS se não for trusted, mas aqui assumimos conteúdo user-generated permitido ?>
            </div>
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
					
					<div class="d-flex">
                        <!-- Vote Section Answer -->
                        <div class="vote-section me-3 d-flex flex-column align-items-center" style="min-width: 40px;">
                            <button type="button" class="btn btn-link p-0 text-decoration-none vote-btn upvote <?php echo ($answer->user_vote == 1) ? 'active' : ''; ?>" 
                                    onclick="splmsVote(<?php echo $answer->id; ?>, 'answer', 1, this)">
                                <i class="fa fa-thumbs-up fa-2x"></i>
                            </button>
                            <span class="vote-count-up fs-5 fw-bold text-success"><?php echo (int)$answer->upvotes; ?></span>
                            
                            <button type="button" class="btn btn-link p-0 text-decoration-none vote-btn downvote mt-2 <?php echo ($answer->user_vote == -1) ? 'active' : ''; ?>" 
                                    onclick="splmsVote(<?php echo $answer->id; ?>, 'answer', -1, this)">
                                <i class="fa fa-thumbs-down fa-2x"></i>
                            </button>
                            <span class="vote-count-down fs-5 fw-bold text-danger"><?php echo (int)$answer->downvotes; ?></span>
                        </div>

                        <div class="answer-body flex-grow-1">
                            <?php echo $answer->body; ?>
                        </div>
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
						<?php echo \Joomla\CMS\Editor\Editor::getInstance(Factory::getConfig()->get('editor'))->display('body', '', '100%', '300', '60', '20', false); ?>
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

<script>
function splmsVote(itemId, itemType, value, btn) {
    const url = 'index.php?option=com_splms&task=forum.vote';
    const data = new FormData();
    data.append('item_id', itemId);
    data.append('item_type', itemType);
    data.append('value', value);
    
    fetch(url, {
        method: 'POST',
        body: data
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const container = btn.closest('.vote-section');
            if (container) {
                container.querySelector('.vote-count-up').textContent = data.upvotes;
                container.querySelector('.vote-count-down').textContent = data.downvotes;
                
                const upBtn = container.querySelector('.upvote');
                const downBtn = container.querySelector('.downvote');
                
                upBtn.classList.remove('active');
                downBtn.classList.remove('active');
                
                if (data.user_vote == 1) upBtn.classList.add('active');
                if (data.user_vote == -1) downBtn.classList.add('active');
            }
        } else {
            alert(data.message || 'Erro ao votar.');
        }
    })
    .catch(err => {
        console.error(err);
        alert('Erro de conexão.');
    });
}
</script>
