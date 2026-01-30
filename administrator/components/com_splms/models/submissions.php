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

        // 3. Junta com a tabela de Lições (para saber o título da aula)
        $query->select('l.title AS lesson_title');
        $query->join('LEFT', $db->quoteName('#__splms_lessons', 'l') . ' ON l.id = a.lesson_id');

        // 4. [ADICIONADO] Junta com a tabela de Cursos (para saber o nome do curso)
        $query->select('c.title AS course_title');
        $query->join('LEFT', $db->quoteName('#__splms_courses', 'c') . ' ON c.id = l.course_id');

        // 5. Ordena pelos mais recentes
        $query->order('a.submitted_at DESC');

        return $query;
    }
}