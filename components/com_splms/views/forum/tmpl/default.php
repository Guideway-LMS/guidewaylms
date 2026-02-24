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

<?php
use Joomla\CMS\Uri\Uri;
?>
<link rel="stylesheet" href="<?php echo Uri::root(); ?>components/com_splms/assets/css/forum.css?v=<?php echo rand(); ?>">

<div class="splms-forum-container">
	<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">

		
		<form action="<?php echo Route::_('index.php?option=com_splms&view=forum&course_id=' . $this->courseId); ?>" method="get" class="d-flex mt-2 mt-md-0">
            <input type="hidden" name="option" value="com_splms" />
            <input type="hidden" name="view" value="forum" />
            <input type="hidden" name="course_id" value="<?php echo $this->courseId; ?>" />
            
            <select name="filter" class="form-select form-select-sm me-2" style="width: auto;" onchange="this.form.submit()">
                <option value="">Filtro: Todos</option>
                <option value="solved" <?php echo ($this->activeFilter == 'solved') ? 'selected' : ''; ?>>Resolvidos</option>
                <option value="unsolved" <?php echo ($this->activeFilter == 'unsolved') ? 'selected' : ''; ?>>Em Aberto</option>
                <option value="mine" <?php echo ($this->activeFilter == 'mine') ? 'selected' : ''; ?>>Minhas Perguntas</option>
            </select>
            
            <div class="input-group input-group-sm">
                <input type="text" name="q" class="form-control" placeholder="Buscar..." value="<?php echo $this->escape($this->searchTerm); ?>">
                <button class="btn btn-outline-secondary" type="submit"><i class="fa fa-search"></i></button>
            </div>
        </form>
	</div>

	<!-- Formulário de Nova Pergunta (Sempre visível para debug/usabilidade) -->
    <?php if ($user->id > 0) : ?>
        <div class="card mb-4" id="newQuestionForm">
            <div class="card-header bg-light">
                <strong>Nova Pergunta</strong>
            </div>
            <div class="card-body">
                <form action="<?php echo Route::_('index.php?option=com_splms'); ?>" method="post">
                    <div class="mb-3">
                        <label for="title" class="form-label">Título</label>
                        <input type="text" class="form-control" id="title" name="title" required placeholder="Qual é sua dúvida?">
                    </div>
                    <div class="mb-3">
                        <label for="body" class="form-label">Descrição</label>
                        <?php echo \Joomla\CMS\Editor\Editor::getInstance(Factory::getConfig()->get('editor'))->display('body', '', '100%', '300', '60', '20', false); ?>
                    </div>
                    <div class="mb-3">
                        <label for="tags" class="form-label">Tags</label>
                        <input type="text" class="form-control" id="tags" name="tags" placeholder="Ex: PHP, SQL, Design (separados por vírgula)">
                    </div>
                    <input type="hidden" name="task" value="forum.save" />
                    <input type="hidden" name="course_id" value="<?php echo $this->courseId; ?>" />
                    <?php echo HTMLHelper::_('form.token'); ?>
                    <button type="submit" class="btn btn-success">Publicar Pergunta</button>
                </form>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-warning">Você precisa estar logado para fazer perguntas.</div>
    <?php endif; ?>

	<!-- Lista de Perguntas -->
	<div class="splms-questions-list">
		<?php if (!empty($this->items)) : ?>
			<?php foreach ($this->items as $item) : ?>
				<div class="card mb-3 question-card">
					<div class="card-body d-flex">
						<!-- Estatísticas -->
						<div class="stats-col text-center me-3">
							<div class="stat-box votes">
								<span class="count"><?php echo (int) $item->votes; ?></span>
								<span class="label">votos</span>
							</div>
							<div class="stat-box answers <?php echo ($item->total_answers > 0) ? 'has-answers' : ''; ?>">
								<span class="count"><?php echo (int) $item->total_answers; ?></span>
								<span class="label">respostas</span>
							</div>
						</div>
						
						<!-- Conteúdo -->
						<div class="content-col flex-grow-1">
							<h4 class="card-title d-flex justify-content-between">
                                <span>
                                    <a href="<?php echo Route::_('index.php?option=com_splms&view=forum&layout=question&id=' . (int) $item->id); ?>">
                                        <?php echo $this->escape((string) $item->title); ?>
                                    </a>
                                    <?php if ($item->solved): ?>
                                        <span class="badge bg-success ms-2" style="font-size: 0.6em; vertical-align: middle;">Resolvido</span>
                                    <?php endif; ?>
                                </span>
                                
                                <?php if (($user->id == $item->user_id) || $user->authorise('core.admin')): ?>
                                    <div class="btn-group">
                                        <a href="<?php echo Route::_('index.php?option=com_splms&view=forum&layout=edit&id=' . $item->id); ?>" 
                                           class="btn btn-sm text-warning" title="Editar">
                                            <i class="fa fa-pencil"></i>
                                        </a>
                                        <a href="<?php echo Route::_('index.php?option=com_splms&task=forum.deleteQuestion&id=' . $item->id . '&course_id=' . $this->courseId . '&' . \Joomla\CMS\Session\Session::getFormToken() . '=1'); ?>" 
                                           class="btn btn-sm text-danger" 
                                           onclick="return confirm('Apagar pergunta?');"
                                           title="Excluir">
                                            <i class="fa fa-trash"></i>
                                        </a>
                                    </div>
                                <?php endif; ?>
							</h4>
                            
                            <?php if (!empty($item->tags)): ?>
                                <div class="mb-1">
                                    <?php foreach (explode(',', $item->tags) as $tag): ?>
                                        <span class="badge bg-info text-dark me-1" style="font-size: 0.7em;"><?php echo trim($this->escape($tag)); ?></span>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

							<p class="card-text text-muted small">
								Por <?php echo $this->escape((string) $item->author_name); ?> 
                                em <?php echo HTMLHelper::_('date', (string) $item->created_on, 'd/m/Y H:i'); ?>
							</p>

                            <!-- Accordion Trigger -->
                            <?php if ($item->total_answers > 0): ?>
                                <button class="btn btn-sm btn-outline-primary mt-2 toggle-answers view-answers-btn" 
                                        type="button" 
                                        data-question-id="<?php echo $item->id; ?>"
                                        onclick="toggleAnswers(<?php echo $item->id; ?>, this)">
                                    <i class="fa fa-comments-o"></i> Ver <?php echo $item->total_answers; ?> Respostas
                                </button>
                                
                                <div id="answers-container-<?php echo $item->id; ?>" class="answers-accordion mt-3" style="display: none;">
                                    <div class="answers-content">
                                        <!-- Loading Spinner -->
                                        <div class="text-center p-3 loading-spinner">
                                            <i class="fa fa-spinner fa-spin fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            <?php else: ?>
                                <span class="text-muted small mt-2 d-inline-block">Sem respostas ainda.</span>
                            <?php endif; ?>
						</div>
					</div>
				</div>
			<?php endforeach; ?>
		<?php endif; ?>
	</div>

	<!-- Paginação -->
	<div class="d-flex justify-content-center mt-4">
	    <?php echo $this->pagination->getPagesLinks(); ?>
	</div>
</div>

<script>
function toggleAnswers(questionId, btn) {
    const container = document.getElementById('answers-container-' + questionId);
    if (!container) return;

    // Get count from data attribute or extract from text if not set
    let count = btn.getAttribute('data-count');
    if (!count) {
        count = btn.innerText.replace(/[^0-9]/g, '');
        if (count) {
            btn.setAttribute('data-count', count);
        }
    }
    // Default to empty if still no count, but usually it should represent a number
    const countText = count ? ' ' + count : '';

    // Toggle visibility
    if (container.style.display === 'none') {
        container.style.display = 'block';
        btn.innerHTML = `<i class="fa fa-chevron-up"></i> Ocultar${countText} Respostas`;
        
        // Load content if empty or just spinner
        const contentDiv = container.querySelector('.answers-content');
        if (contentDiv.querySelector('.loading-spinner') || contentDiv.innerHTML.trim() === '') {
            loadAnswers(questionId, contentDiv);
        }
    } else {
        container.style.display = 'none';
        btn.innerHTML = `<i class="fa fa-comments-o"></i> Ver${countText} Respostas`;
    }
}

function loadAnswers(questionId, container) {
    const url = '<?php echo \Joomla\CMS\Uri\Uri::root(); ?>index.php?option=com_splms&task=forum.getAnswersAjax&question_id=' + questionId;
    
    fetch(url)
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (data.answers && data.answers.length > 0) {
                let html = '<div class="list-group list-group-flush">';
                data.answers.forEach(answer => {
                    const isAccepted = answer.is_accepted == 1 ? 'border-success' : '';
                    const acceptedBadge = answer.is_accepted == 1 ? '<span class="badge bg-success mb-2"><i class="fa fa-check"></i> Solução</span>' : '';
                    const bgStyle = 'background-color: rgba(0,0,0,0.02);';
                    const upvoteClass = answer.user_vote == 1 ? 'text-success' : 'text-muted';
                    const downvoteClass = answer.user_vote == -1 ? 'text-danger' : 'text-muted';

                    html += `
                        <div class="list-group-item ${isAccepted}" style="${bgStyle}">
                            ${acceptedBadge}
                            <div class="d-flex w-100 justify-content-between align-items-start">
                                <small class="text-muted">
                                    <strong>${answer.author_name}</strong> em ${answer.created_on_formatted}
                                </small>
                                <div class="course-vote-section d-flex align-items-center gap-2 ms-2">
                                    <button type="button"
                                        class="btn btn-sm btn-link p-0 text-decoration-none course-vote-btn ${upvoteClass}"
                                        onclick="splmsCourseVote(${answer.id}, 'answer', 1, this)"
                                        title="Gostei">
                                        <i class="fa fa-thumbs-up fa-lg"></i>
                                        <span class="course-vote-count-up">${answer.upvotes}</span>
                                    </button>
                                    <button type="button"
                                        class="btn btn-sm btn-link p-0 text-decoration-none course-vote-btn ${downvoteClass}"
                                        onclick="splmsCourseVote(${answer.id}, 'answer', -1, this)"
                                        title="Não gostei">
                                        <i class="fa fa-thumbs-down fa-lg"></i>
                                        <span class="course-vote-count-down">${answer.downvotes}</span>
                                    </button>
                                </div>
                            </div>
                            <div class="mt-2 mb-1">${answer.body}</div>
                        </div>
                    `;
                });
                html += '</div>';
                container.innerHTML = html;
            } else {
                container.innerHTML = '<p class="text-muted p-3">Nenhuma resposta encontrada.</p>';
            }
        } else {
            container.innerHTML = '<p class="text-danger p-3">Erro ao carregar respostas.</p>';
        }
    })
    .catch(err => {
        console.error(err);
        container.innerHTML = '<p class="text-danger p-3">Erro de conexão.</p>';
    });
}

function splmsCourseVote(itemId, itemType, value, btn) {
    const url = '<?php echo \Joomla\CMS\Uri\Uri::root(); ?>index.php?option=com_splms&task=forum.vote';
    const formData = new FormData();
    formData.append('item_id', itemId);
    formData.append('item_type', itemType);
    formData.append('value', value);

    fetch(url, { method: 'POST', body: formData })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const section = btn.closest('.course-vote-section');
            if (section) {
                section.querySelector('.course-vote-count-up').textContent = data.upvotes;
                section.querySelector('.course-vote-count-down').textContent = data.downvotes;

                // Reset active state on all buttons in this section
                section.querySelectorAll('.course-vote-btn').forEach(b => {
                    b.classList.remove('text-success', 'text-danger');
                    b.classList.add('text-muted');
                });

                // Apply active state
                if (data.user_vote == 1) {
                    btn.classList.remove('text-muted');
                    btn.classList.add('text-success');
                } else if (data.user_vote == -1) {
                    btn.classList.remove('text-muted');
                    btn.classList.add('text-danger');
                }
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
