<?php
/**
 * @package     SP LMS
 * @subpackage  Components
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;

class SplmsControllerForum extends BaseController
{
	/**
	 * Salva uma nova pergunta.
	 *
	 * @return  void
	 */
	public function save()
	{
		// Verifica token de sessão para segurança (CSRF)
		$this->checkToken();

		$app = Factory::getApplication();
		$input = $app->input;
		$user = Factory::getUser();

		// Dados do formulário
		$data = [
			'id'        => $input->getInt('id', 0),
			'course_id' => $input->getInt('course_id'),
			'user_id' => $user->id,
			'title'   => $input->getString('title'),
			'body'    => $input->get('body', '', 'raw'), // Permite HTML básico se configurado, ou use filter depois
		];

		// Validar dados básicos
		if (empty($data['title']) || empty($data['body']) || empty($data['course_id']))
		{
			$app->enqueueMessage('Por favor, preencha todos os campos.', 'error');
			$this->setRedirect(Route::_('index.php?option=com_splms&view=forum&course_id=' . $data['course_id'], false));
			return;
		}

		// Chama o model
		$model = $this->getModel('Forum', 'SplmsModel');
		if ($model->saveQuestion($data))
		{
			$msg = ($data['id'] > 0) ? 'Pergunta atualizada!' : 'Pergunta publicada com sucesso!';
			$app->enqueueMessage($msg);
		}
		else
		{
			$app->enqueueMessage('Erro ao publicar pergunta.', 'error');
		}

		// Redireciona para a página do curso, onde o fórum está embutido
		$this->setRedirect(Route::_('index.php?option=com_splms&view=course&id=' . $data['course_id'], false));
	}

	/**
	 * Salva uma nova resposta.
	 *
	 * @return  void
	 */
	public function saveAnswer()
	{
		// Verifica token
		$this->checkToken();

		$app = Factory::getApplication();
		$input = $app->input;
		$user = Factory::getUser();

		$questionId = $input->getInt('question_id');
		$data = [
			'question_id' => $questionId,
			'user_id'     => $user->id,
			'body'        => $input->get('body', '', 'raw'),
		];

		if (empty($data['body']))
		{
			$app->enqueueMessage('A resposta não pode ser vazia.', 'error');
			$this->setRedirect(Route::_('index.php?option=com_splms&view=forum&layout=question&id=' . $questionId, false));
			return;
		}

		$model = $this->getModel('Forum', 'SplmsModel');
		if ($model->saveAnswer($data))
		{
			$app->enqueueMessage('Resposta enviada!');
		}
		else
		{
			$app->enqueueMessage('Erro ao enviar resposta.', 'error');
		}

		// Redireciona para a própria pergunta
		$this->setRedirect(Route::_('index.php?option=com_splms&view=forum&layout=question&id=' . $questionId, false));
	}
	public function acceptAnswer()
	{
		$this->checkToken('request');

		$app = Factory::getApplication();
		$input = $app->input;
		$user = Factory::getUser();

		$answerId = $input->getInt('answer_id');
		$questionId = $input->getInt('question_id');

		if (!$user->id)
		{
			$this->setRedirect(Route::_('index.php?option=com_users&view=login', false), 'Faça login primeiro.', 'error');
			return;
		}

		$model = $this->getModel('Forum', 'SplmsModel');
		
		// Verificacao de permissao seria ideal aqui (se usuario eh dono da pergunta), 
		// mas faremos simplificado no Model ou aqui mesmo.
		// Vamos deixar o Model validar se pode marcar.
		
		if ($model->markAnswerAsAccepted($answerId, $questionId, $user->id))
		{
			$app->enqueueMessage('Resposta marcada como solução!', 'success');
		}
		else
		{
			$app->enqueueMessage('Erro ao marcar resposta (ou você não tem permissão).', 'error');
		}

		$this->setRedirect(Route::_('index.php?option=com_splms&view=forum&layout=question&id=' . $questionId, false));
	}
	public function deleteQuestion()
	{
		$this->checkToken('request');

		$app = Factory::getApplication();
		$input = $app->input;
		$user = Factory::getUser();
		$id = $input->getInt('id');
		$courseId = $input->getInt('course_id');

		if (!$user->id) {
			$this->setRedirect(Route::_('index.php?option=com_users&view=login', false), 'Login necessário.', 'error');
			return;
		}

		$model = $this->getModel('Forum', 'SplmsModel');
		if ($model->deleteQuestion($id, $user->id)) {
			$app->enqueueMessage('Pergunta excluída.', 'message');
		} else {
			$app->enqueueMessage('Erro ao excluir (permissão negada/incorreta?).', 'error');
		}

		// Redirect to Course View
		$this->setRedirect(Route::_('index.php?option=com_splms&view=course&id=' . $courseId, false));
	}

	public function deleteAnswer()
	{
		$this->checkToken('request');

		$app = Factory::getApplication();
		$input = $app->input;
		$user = Factory::getUser();
		$id = $input->getInt('id');
		$questionId = $input->getInt('question_id');

		if (!$user->id) {
			$this->setRedirect(Route::_('index.php?option=com_users&view=login', false), 'Login necessário.', 'error');
			return;
		}

		$model = $this->getModel('Forum', 'SplmsModel');
		if ($model->deleteAnswer($id, $user->id)) {
			$app->enqueueMessage('Resposta excluída.', 'message');
		} else {
			$app->enqueueMessage('Erro ao excluir (permissão negada?).', 'error');
		}

		$this->setRedirect(Route::_('index.php?option=com_splms&view=forum&layout=question&id=' . $questionId, false));
	}
}
