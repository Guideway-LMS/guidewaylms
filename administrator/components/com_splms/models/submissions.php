<?php
/**
 * @package     com_splms
 * @license     GNU/GPLv2 or later
 */

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\ListModel;

class SplmsModelSubmissions extends ListModel
{
    /**
     * Construtor: Define os campos que podem ser usados para filtrar ou ordenar
     */
    public function __construct($config = array())
    {
        if (empty($config['filter_fields']))
        {
            $config['filter_fields'] = array(
                'id', 'a.id',
                'student_name', 'u.name',
                'lesson_title', 'l.title',
                'course_title', 'c.title',
                'course_id', 'c.id',
                'submitted_at', 'a.submitted_at'
            );
        }

        parent::__construct($config);
    }

    /**
     * Popula o estado: Captura os dados do formulário de filtro (XML)
     */
    protected function populateState($ordering = null, $direction = null)
    {
        // Captura o filtro 'course_id' enviado pelo dropdown
        $courseId = $this->getUserStateFromRequest($this->context . '.filter.course_id', 'filter_course_id', '', 'string');
        $this->setState('filter.course_id', $courseId);

        // Define a ordenação padrão (Data de envio, Decrescente)
        parent::populateState('a.submitted_at', 'desc');
    }

    /**
     * Monta a consulta SQL para listar os trabalhos
     */
    protected function getListQuery()
    {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        // 1. Seleciona os dados da submissão
        $query->select(array('a.*', 'a.id AS submission_id'));
        $query->from($db->quoteName('#__splms_submissions', 'a'));

        // 2. Junta com a tabela de Usuários (para saber o nome do aluno)
        $query->select('u.name AS student_name, u.username');
        $query->join('LEFT', $db->quoteName('#__users', 'u') . ' ON u.id = a.user_id');

        // 3. Junta com a tabela de Lições (para saber qual é a aula)
        // Nota: Assumimos que a tabela de lições tem a coluna 'course_id'
        $query->select('l.title AS lesson_title, l.course_id');
        $query->join('LEFT', $db->quoteName('#__splms_lessons', 'l') . ' ON l.id = a.lesson_id');

        // 4. NOVO: Junta com a tabela de Cursos (para pegar o Nome do Curso e permitir filtro)
        $query->select('c.title AS course_title');
        $query->join('LEFT', $db->quoteName('#__splms_courses', 'c') . ' ON c.id = l.course_id');

        // --- APLICAÇÃO DO FILTRO ---
        $courseId = $this->getState('filter.course_id');
        
        if (is_numeric($courseId)) {
            // Filtra onde o ID do curso na tabela de lições é igual ao selecionado
            $query->where('l.course_id = ' . (int) $courseId);
        }

        // Ordenação
        $orderCol = $this->state->get('list.ordering', 'a.submitted_at');
        $orderDirn = $this->state->get('list.direction', 'DESC');
        
        // Proteção extra na ordenação
        if ($orderCol == 'a.ordering' || empty($orderCol)) {
            $orderCol = 'a.submitted_at';
        }
        
        $query->order($db->escape($orderCol . ' ' . $orderDirn));

        return $query;
    }
}