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
        
        // 1. SELEÇÃO: Selecionar o ID Primário (a.id) é CRÍTICO para JModelList.
        $query->select(
            $db->quoteName([
                'a.id', 'a.title', 'a.created_by', 'a.created_at', 'a.course_id' // Campos básicos
            ])
        );

        // 2. JOINS: (Mantidos, mas garantindo que o JOIN não esteja impedindo a busca principal)
        $query->select($db->quoteName('u.name', 'author_name'));
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
        
        // 3. WHERE (Adicione esta linha se houver um campo 'published' na sua tabela)
        // Isso garante que apenas avisos publicados sejam buscados.
        // Se a coluna 'a.published' não existir, remova esta linha.
        // $query->where($db->quoteName('a.published') . ' = 1'); 

        // Adiciona a ordenação
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
    
    /**
     * IMPLEMENTAÇÃO OBRIGATÓRIA: Remove um ou mais avisos do banco de dados.
     * Esta função corrige o erro "Call to undefined method SplmsModelAnnouncements::delete()".
     *
     * @param   array  $pks  Um array de IDs (chaves primárias) a serem removidas.
     *
     * @return  boolean  True em caso de sucesso, false em caso de falha.
     */
    public function delete($pks)
    {
        $db = $this->getDbo();
        $query = $db->getQuery(true);
        $pks = (array) $pks; // Garante que seja um array

        // 1. Sanitiza e valida os IDs
        $cleanPks = array_map('intval', $pks);
        $cleanPks = array_filter($cleanPks, function ($id) { return $id > 0; });

        if (empty($cleanPks)) {
            $this->setError(JText::_('JERROR_NO_ITEMS_SELECTED'));
            return false;
        }

        // 2. Constrói a query DELETE
        $query->delete($db->quoteName('#__splms_course_announcements'))
              ->where($db->quoteName('id') . ' IN (' . implode(',', $cleanPks) . ')');

        // 3. Executa a query
        $db->setQuery($query);

        try {
            $db->execute();
        } catch (\Exception $e) {
            $this->setError($e->getMessage());
            return false;
        }

        // 4. Limpa o cache do sistema (obrigatório após alteração no DB)
        $this->cleanCache();
        
        return true;
    }
}