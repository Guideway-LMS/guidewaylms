<?php
/**
 * @package     com_splms
 * @subpackage  site
 * @license     GNU General Public License
 */

// Acesso restrito
defined('_JEXEC') or die;

/**
 * Model da lista de Avisos (Announcements) para o frontend
 * Formato Legado (Joomla 3)
 */
class SplmsModelAnnouncements extends JModelList
{
    /**
     * Método para construir a consulta SQL para buscar a lista de avisos.
     *
     * @return  JDatabaseQuery|boolean  A consulta SQL ou false se não tiver permissão.
     */
    protected function getListQuery()
    {
        $db    = $this->getDbo();
        $query = $db->getQuery(true);

        // Pega o ID do curso da URL
        $app = JFactory::getApplication();
        $courseId = $app->input->getInt('id', 0);

        // Pega o ID do utilizador logado (Tarefa 1.6)
        $user = JFactory::getUser();
        $userId = (int) $user->id;

        // [SEGURANÇA] Se for convidado, não retorna nada
        if ($userId === 0) {
            return false;
        }

        // Seleciona os campos necessários para o aluno
        $query->select(
            $db->quoteName([
                'a.id', 'a.title', 'a.message', 'a.created_at'
            ])
        );

        // Pega o nome do autor (professor)
        $query->select($db->quoteName('u.name', 'author_name'));

        $query->from($db->quoteName('#__splms_course_announcements', 'a'));
        
        // Join para pegar o nome do autor
        $query->join(
            'LEFT',
            $db->quoteName('#__users', 'u') . ' ON ' . ( $db->quoteName('u.id') . ' = ' . $db->quoteName('a.created_by') )
        );

        // --- Filtros Essenciais ---
        
        // 1. Apenas para este curso
        $query->where($db->quoteName('a.course_id') . ' = ' . (int) $courseId);
        
        // 2. [SEGURANÇA] Apenas se o aluno estiver matriculado (Tarefa 1.5.2)
        $subQuery = $db->getQuery(true)
            ->select('1')
            ->from($db->quoteName('#__splms_orders', 'o'))
            ->where($db->quoteName('o.course_id') . ' = ' . (int) $courseId)
            ->where($db->quoteName('o.order_user_id') . ' = ' . (int) $userId)
            ->where($db->quoteName('o.published') . ' = 1');

        $query->where('EXISTS (' . $subQuery . ')');
        
        // Ordena pelo mais recente primeiro
        $query->order($db->escape('a.created_at DESC'));

        return $query;
    }

    /**
     * Inicializa o estado (ordenamento, filtros, paginação) do Model.
     *
     * @param   string  $ordering   O campo de ordenação
     * @param   string  $direction  A direção da ordenação
     *
     * @return  void
     */
    protected function populateState($ordering = null, $direction = null)
    {
        // Define a ordenação padrão para que a Paginação funcione
        parent::populateState('a.created_at', 'DESC');
    }
}