<?php
/**
 * @package     com_splms
 * @subpackage  administrator
 * @license     GNU General Public License
 */

// Acesso restrito
defined('_JEXEC') or die;

/**
 * Model da lista de Avisos (Announcements)
 * Formato Legado (Joomla 3)
 */
class SplmsModelAnnouncements extends JModelList
{
    /**
     * Construtor.
     *
     * @param   array  $config  Um array de configuração (opcional).
     */
    public function __construct($config = [])
    {
        // Define os campos que podem ser usados para filtrar a lista
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = [
                'a.id', 'a.title', 'a.published',
                'course_title', 'author_name'
            ];
        }

        parent::__construct($config);
    }

    /**
     * Método para construir a consulta SQL para buscar a lista de avisos.
     *
     * @return  JDatabaseQuery  A consulta SQL.
     */
    protected function getListQuery()
    {
        $db    = $this->getDbo();
        $query = $db->getQuery(true);
        
        // Seleciona os campos principais (aliás 'a')
        $query->select(
            $db->quoteName([
                'a.id', 'a.title', 'a.published', 'a.created_by', 'a.created_on', 'a.course_id'
            ])
        );

        // Seleciona o nome do autor (JOIN com #__users)
        $query->select($db->quoteName('u.name', 'author_name'));

        // Seleciona o nome do curso (JOIN com #__splms_courses)
        $query->select($db->quoteName('c.title', 'course_title'));

        // Tabela principal
        $query->from($db->quoteName('#__splms_course_announcements', 'a'));

        // JOIN com a tabela de usuários
        $query->join(
            'LEFT',
            $db->quoteName('#__users', 'u') . ' ON ' . ( $db->quoteName('u.id') . ' = ' . $db->quoteName('a.created_by') )
        );

        // JOIN com a tabela de cursos
        $query->join(
            'LEFT',
            $db->quoteName('#__splms_courses', 'c') . ' ON ' . ( $db->quoteName('c.id') . ' = ' . $db->quoteName('a.course_id') )
        );
        
        // Adiciona a ordenação baseada no 'populateState'
        $query->order($db->escape($this->getState('list.ordering', 'a.id')) . ' ' . $db->escape($this->getState('list.direction', 'DESC')));

        return $query;
    }

    /**
     * Inicializa o estado (ordenamento, filtros, paginação) do Model.
     * (Este método é o que corrige o erro "getListFooter() on null")
     *
     * @param   string  $ordering   O campo de ordenação
     * @param   string  $direction  A direção da ordenação
     *
     * @return  void
     */
    protected function populateState($ordering = null, $direction = null)
    {
        // Define a ordenação padrão para que a Paginação funcione
        parent::populateState('a.id', 'DESC');
    }
}