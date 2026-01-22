<?php
/**
 * @package     SP LMS
 * @subpackage  Components
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Router\Route;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;

// CSS
$doc = \Joomla\CMS\Factory::getDocument();
$doc->addStyleSheet(Uri::root() . 'components/com_splms/assets/css/forum.css');
?>

<div class="splms-forum-container">
	<div class="mb-3">
		<a href="javascript:history.back()" class="btn btn-secondary btn-sm">&larr; Cancelar</a>
	</div>

	<div class="card mb-4">
		<div class="card-header bg-primary text-white">
			<strong>Editar Pergunta</strong>
		</div>
		<div class="card-body">
			<form action="<?php echo Route::_('index.php?option=com_splms'); ?>" method="post">
				<div class="mb-3">
					<label for="title" class="form-label">Título</label>
					<input type="text" class="form-control" id="title" name="title" required value="<?php echo $this->escape($this->item->title); ?>">
				</div>
				<div class="mb-3">
					<label for="body" class="form-label">Detalhes</label>
					<textarea class="form-control" id="body" name="body" rows="6" required><?php echo $this->escape($this->item->body); ?></textarea>
				</div>
				
				<input type="hidden" name="task" value="forum.save" />
				<input type="hidden" name="course_id" value="<?php echo $this->item->course_id; ?>" />
				<input type="hidden" name="id" value="<?php echo $this->item->id; ?>" />
				<?php echo HTMLHelper::_('form.token'); ?>
				
				<div class="d-flex justify-content-between">
					<button type="submit" class="btn btn-success">Salvar Alterações</button>
				</div>
			</form>
		</div>
	</div>
</div>
