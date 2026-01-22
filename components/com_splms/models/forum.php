<?php
/**
 * @package     SP LMS
 * @subpackage  Components
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\ItemModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;

class SplmsModelForum extends ItemModel
{
	/**
	 * Salva uma nova pergunta.
	 *
	 * @param   array  $data  Dados do formulário.
	 *
	 * @return  boolean
	 */
	public function saveQuestion($data)
	{
		// Validacao basica de permissao para edicao
		$user = Factory::getUser();
		$db = Factory::getDbo();

		$id = (int) $data['id'];
		
		if ($id > 0) {
			// UPDATE
			// 1. Verificar dono
			$query = $db->getQuery(true)->select('user_id')->from('#__splms_forum_questions')->where('id=' . $id);
			$db->setQuery($query);
			$ownerId = $db->loadResult();
			
			// Restringir edicao: Apenas Dono ou Super Admin (Grupo 8)
			// (Professores/Admins Grupo 7 nao podem editar posts alheios)
			$userGroups = \Joomla\CMS\Access\Access::getGroupsByUser($user->id);
			$isSuperAdmin = in_array(8, $userGroups);

			if ($ownerId != $user->id && !$isSuperAdmin) {
				return false;
			}
			
			// 2. Atualizar
			$query = $db->getQuery(true)
				->update($db->quoteName('#__splms_forum_questions'))
				->set($db->quoteName('title') . ' = ' . $db->quote($data['title']))
				->set($db->quoteName('body') . ' = ' . $db->quote($data['body']))
				->where($db->quoteName('id') . ' = ' . $id);
				
			$db->setQuery($query);
			return $db->execute();
			
		} else {
			// INSERT
			$query = $db->getQuery(true);
			
			$columns = array('user_id', 'course_id', 'title', 'body', 'created_on');
			$values = array(
				(int) $data['user_id'],
				(int) $data['course_id'],
				$db->quote($data['title']),
				$db->quote($data['body']),
				$db->quote(Factory::getDate()->toSql())
			);
			
			$query
				->insert($db->quoteName('#__splms_forum_questions'))
				->columns($db->quoteName($columns))
				->values(implode(',', $values));
				
			$db->setQuery($query);
			return $db->execute();
		}
	}

	/**
	 * Salva uma nova resposta.
	 *
	 * @param   array  $data  Dados da resposta.
	 *
	 * @return  boolean
	 */
	public function saveAnswer($data)
	{
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		
		$columns = array('user_id', 'question_id', 'body', 'created_on');
		$values = array(
			(int) $data['user_id'],
			(int) $data['question_id'],
			$db->quote($data['body']),
			$db->quote(Factory::getDate()->toSql())
		);
		
		$query
			->insert($db->quoteName('#__splms_forum_answers'))
			->columns($db->quoteName($columns))
			->values(implode(',', $values));
			
		$db->setQuery($query);
		
		return $db->execute();
	}

	/**
	 * Lista perguntas de um curso.
	 *
	 * @param   int  $courseId  ID do curso.
	 *
	 * @return  array
	 */
	public function getQuestions($courseId, $limit = 0, $limitStart = 0, $search = '', $filter = '')
	{
		$db = Factory::getDbo();
		$query = $db->getQuery(true);

		$query->select('q.*, u.name as author_name, COUNT(a.id) as total_answers')
			->from($db->quoteName('#__splms_forum_questions', 'q'))
			->join('LEFT', $db->quoteName('#__users', 'u') . ' ON q.user_id = u.id')
			->join('LEFT', $db->quoteName('#__splms_forum_answers', 'a') . ' ON q.id = a.question_id')
			->where('q.course_id = ' . (int) $courseId);

		// Filtro de Busca
		if (!empty($search)) {
			$search = $db->quote('%' . $db->escape($search, true) . '%');
			$query->where('(q.title LIKE ' . $search . ' OR q.body LIKE ' . $search . ')');
		}

		// Filtro de Status
		if ($filter === 'solved') {
			$query->where('q.solved = 1');
		} elseif ($filter === 'unsolved') {
			$query->where('q.solved = 0');
		} elseif ($filter === 'mine') {
			$user = Factory::getUser();
			$query->where('q.user_id = ' . (int) $user->id);
		}

		$query->group('q.id')
			->order('q.created_on DESC');

		$db->setQuery($query, $limitStart, $limit);
		return $db->loadObjectList();
	}

	public function getTotalQuestions($courseId, $search = '', $filter = '')
	{
		$db = Factory::getDbo();
		$query = $db->getQuery(true);

		$query->select('COUNT(DISTINCT q.id)')
			->from($db->quoteName('#__splms_forum_questions', 'q'));
			
		$query->where('q.course_id = ' . (int) $courseId);

		// Busca
		if (!empty($search)) {
			$search = $db->quote('%' . $db->escape($search, true) . '%');
			$query->where('(q.title LIKE ' . $search . ' OR q.body LIKE ' . $search . ')');
		}

		// Filtro
		if ($filter === 'solved') {
			$query->where('q.solved = 1');
		} elseif ($filter === 'unsolved') {
			$query->where('q.solved = 0');
		} elseif ($filter === 'mine') {
			$user = Factory::getUser();
			$query->where('q.user_id = ' . (int) $user->id);
		}

		$db->setQuery($query);
		return (int) $db->loadResult();
	}

	/**
	 * Retorna uma pergunta específica.
	 *
	 * @param   int  $id  ID da pergunta.
	 *
	 * @return  object
	 */
	public function getQuestion($id = null)
	{
		$id = (!empty($id)) ? $id : $this->getState('item.id');
		$db = Factory::getDbo();
		$query = $db->getQuery(true);

		$query->select('q.*, u.name as author_name')
			->from($db->quoteName('#__splms_forum_questions', 'q'))
			->join('LEFT', $db->quoteName('#__users', 'u') . ' ON q.user_id = u.id')
			->where('q.id = ' . (int) $id);

		$db->setQuery($query);
		return $db->loadObject();
	}

    /**
     * Method to get the item.
     * Required by ItemModelInterface.
     *
     * @param   int  $pk  The id of the item.
     *
     * @return  mixed  Item data object or boolean false.
     */
    public function getItem($pk = null)
    {
        return $this->getQuestion($pk);
    }

	/**
	 * Retorna respostas de uma pergunta.
	 *
	 * @param   int  $questionId  ID da pergunta.
	 *
	 * @return  array
	 */
	public function getAnswers($questionId)
	{
		$db = Factory::getDbo();
		$query = $db->getQuery(true);

		$query->select('a.*, u.name as author_name')
			->from($db->quoteName('#__splms_forum_answers', 'a'))
			->join('LEFT', $db->quoteName('#__users', 'u') . ' ON a.user_id = u.id')
			->where('a.question_id = ' . (int) $questionId)
			->order('a.is_accepted DESC, a.created_on ASC'); // Respondidas primeiro, depois por data

		$db->setQuery($query);
		return $db->loadObjectList();
    }

	/**
	 * Marca uma resposta como aceita e a pergunta como resolvida.
	 */
	public function markAnswerAsAccepted($answerId, $questionId, $userId)
	{
		$db = Factory::getDbo();
		
		// 1. Verificar se usuario eh dono da pergunta (ou admin)
		$query = $db->getQuery(true)
			->select('user_id')
			->from($db->quoteName('#__splms_forum_questions'))
			->where('id = ' . (int) $questionId);
		$db->setQuery($query);
		$ownerId = $db->loadResult();
		
		$user = Factory::getUser($userId);
		$is_admin = $user->authorise('core.admin');
		
		if ($ownerId != $userId && !$is_admin) {
			return false; // Nao autorizado
		}

		// 2. Desmarcar todas as respostas dessa pergunta
		$query = $db->getQuery(true)
			->update($db->quoteName('#__splms_forum_answers'))
			->set('is_accepted = 0')
			->where('question_id = ' . (int) $questionId);
		$db->setQuery($query);
		$db->execute();

		// 3. Marcar a resposta escolhida
		$query = $db->getQuery(true)
			->update($db->quoteName('#__splms_forum_answers'))
			->set('is_accepted = 1')
			->where('id = ' . (int) $answerId);
		$db->setQuery($query);
		$db->execute();

		// 4. Marcar pergunta como resolvida
		$query = $db->getQuery(true)
			->update($db->quoteName('#__splms_forum_questions'))
			->set('solved = 1')
			->where('id = ' . (int) $questionId);
		$db->setQuery($query);
		$db->execute();
		
		return true;
    }

	public function deleteQuestion($id, $userId)
	{
		$db = Factory::getDbo();
		$user = Factory::getUser($userId);
		$is_admin = $user->authorise('core.admin');

		// Check owner
		$query = $db->getQuery(true)->select('user_id')->from('#__splms_forum_questions')->where('id='.(int)$id);
		$db->setQuery($query);
		$owner = $db->loadResult();

		// Permitir se for Dono, Super Admin (8) ou Professor/Admin (7)
		$userGroups = \Joomla\CMS\Access\Access::getGroupsByUser($userId);
		$canDelete = ($owner == $userId) || in_array(8, $userGroups) || in_array(7, $userGroups) || $is_admin;

		if (!$canDelete) return false;

		// Delete answers and question
		$db->setQuery("DELETE FROM #__splms_forum_answers WHERE question_id=".(int)$id);
		$db->execute();
		$db->setQuery("DELETE FROM #__splms_forum_questions WHERE id=".(int)$id);
		$db->execute();

		return true;
	}

	public function deleteAnswer($id, $userId)
	{
		$db = Factory::getDbo();
		$user = Factory::getUser($userId);
		$is_admin = $user->authorise('core.admin');

		// Check owner
		$query = $db->getQuery(true)->select('user_id')->from('#__splms_forum_answers')->where('id='.(int)$id);
		$db->setQuery($query);
		$owner = $db->loadResult();

		// Permitir se for Dono, Super Admin (8) ou Professor/Admin (7)
		$userGroups = \Joomla\CMS\Access\Access::getGroupsByUser($userId);
		$canDelete = ($owner == $userId) || in_array(8, $userGroups) || in_array(7, $userGroups) || $is_admin;

		if (!$canDelete) return false;

		$db->setQuery("DELETE FROM #__splms_forum_answers WHERE id=".(int)$id);
		return $db->execute();
	}
}
