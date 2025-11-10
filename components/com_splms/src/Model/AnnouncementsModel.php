<?php
/**
 * @package     JoomShaper\Component\Splms
 * @subpackage  Site.Model
 * @license     GNU General Public License
 */

// Define o Namespace (Prática moderna J5 para o 'site')
namespace JoomShaper\Component\Splms\Site\Model;

// Importa as classes modernas que vamos usar
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Factory;

/**
 * Model da lista de Avisos (Announcements) para o frontend
 *
 * @since  4.1.3
 */
class AnnouncementsModel extends ListModel
{
    /**
     * Método para construir a consulta SQL para buscar a lista de avisos.
     *
     * @return  string|bool  A consulta SQL ou false se o usuário não tiver permissão.
     */
    protected function getListQuery()
    {
        $db    = $this->getDbo();
        $query = $db->getQuery(true);
        
        // --- [Checklist] Pegar IDs ---
        $app      = Factory::getApplication();
        $user     = Factory::getUser();
        $courseId = $app->input->getInt('id', 0); 
        $userId   = (int) $user->id; // [Checklist] Pegar o ID do usuário logado

        // [Checklist] Testar se a consulta retorna vazio se não estiver logado
        // Se o usuário for um convidado (não logado), não há matrícula. Retorna nulo.
        if ($userId === 0) {
            return false;
        }

        // --- Seleção de Campos ---
        // Seleciona os campos necessários para o aluno
        $query->select(
            $db->quoteName([
                'a.id', 'a.title', 'a.description', 'a.created_on'
            ])
        );
        // Pega o nome do autor (professor)
        $query->select(
            $db->quoteName('u.name', 'author_name')
        );

        $query->from($db->quoteName('#__splms_course_announcements', 'a'));
        
        // Join para pegar o nome do autor
        $query->join(
            'LEFT',
            $db->quoteName('#__users', 'u') . ' ON ' . $db->quoteName('u.id') . ' = ' . $db->quoteName('a.created_by')
        );

        // --- Filtros Essenciais ---
        
        // 1. Apenas avisos publicados
        $query->where($db->quoteName('a.published') . ' = 1');
        
        // 2. Apenas para este curso
        $query->where($db->quoteName('a.course_id') . ' = ' . (int) $courseId);

        // 3. [Checklist] Modificar getListQuery() com WHERE EXISTS
        //    E APENAS SE o usuário estiver matriculado neste curso.
        
        // [Checklist] Localizar o nome da tabela de matrículas (assumindo #__splms_students)
        $subQuery = $db->getQuery(true)
            ->select('1')
            ->from($db->quoteName('#__splms_students', 's')) // <-- ATENÇÃO AQUI
            ->where($db->quoteName('s.course_id') . ' = ' . (int) $courseId)
            ->where($db->quoteName('s.user_id') . ' = ' . (int) $userId);
            // NOTA: Talvez seja necessário adicionar um status, ex:
            // ->where($db->quoteName('s.status') . ' = ' . $db->quote('enrolled'));

        $query->where('EXISTS (' . $subQuery . ')');
        
        // Ordena pelo mais recente primeiro
        $query->order($db->escape('a.created_on DESC'));

        return $query;
    }
}