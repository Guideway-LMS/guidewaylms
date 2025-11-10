<?php
/**
 * @package     JoomShaper\Component\Splms
 * @subpackage  Administrator.Model
 * @license     GNU General Public License
 */

// Define o Namespace (Prática moderna do J5)
namespace JoomShaper\Component\Splms\Administrator\Model;

// Importa as classes modernas que vamos usar
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Database\DatabaseDriver;

/**
 * Model da lista de Avisos (Announcements)
 *
 * @since  4.1.3
 */
class AnnouncementsModel extends ListModel  // [Checklist] Herdar de Joomla\CMS\MVC\Model\ListModel
{
    /**
     * Construtor.
     *
     * @param   array  $config  Um array de configuração (opcional).
     */
    public function __construct($config = [])
    {
        // O Joomla 5 recomenda definir os campos de filtro aqui
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
     * @return  string  A consulta SQL.
     */
    protected function getListQuery() // [Checklist] Implementar o método getListQuery()
    {
        $db    = $this->getDbo();
        $query = $db->getQuery(true);

        // [Checklist] (Opcional) Adicionar JOINs
        
        // Seleciona os campos principais da nossa tabela (aliás 'a')
        $query->select(
            $db->quoteName([
                'a.id', 'a.title', 'a.published', 'a.created_by', 'a.created_on', 'a.course_id'
            ])
        );

        // Seleciona o nome do autor (JOIN com #__users)
        $query->select(
            $db->quoteName('u.name', 'author_name')
        );

        // Seleciona o nome do curso (JOIN com #__splms_courses)
        $query->select(
            $db->quoteName('c.title', 'course_title')
        );

        // Define a tabela principal
        $query->from($db->quoteName('#__splms_course_announcements', 'a'));

        // Faz o JOIN com a tabela de usuários
        $query->join(
            'LEFT',
            $db->quoteName('#__users', 'u') . ' ON ' . $db->quoteName('u.id') . ' = ' . $db->quoteName('a.created_by')
        );

        // Faz o JOIN com a tabela de cursos do SP LMS
        $query->join(
            'LEFT',
            $db->quoteName('#__splms_courses', 'c') . ' ON ' . $db->quoteName('c.id') . ' = ' . $db->quoteName('a.course_id')
        );
        
        // TODO: Adicionar filtros (busca por texto, filtro de publicado, etc.)
        
        // TODO: Adicionar ordenação
        $query->order($db->escape('a.id DESC'));

        return $query;
    }
}