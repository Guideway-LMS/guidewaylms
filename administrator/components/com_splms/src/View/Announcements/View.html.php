<?php
/**
 * @package     JoomShaper\Component\Splms
 * @subpackage  Administrator.View
 * @license     GNU General Public License
 */

// Define o Namespace (Prática moderna J5)
namespace JoomShaper\Component\Splms\Administrator\View\Announcements;

// Importa as classes modernas que vamos usar
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

/**
 * View da lista de Avisos (Announcements)
 *
 * @since  4.1.3
 */
class View extends BaseHtmlView // [Checklist] Herda da classe de View base
{
    /**
     * @var  object[]  Itens da lista (os avisos)
     */
    protected $items;

    /**
     * @var  \Joomla\CMS\Pagination\Pagination  Objeto de paginação
     */
    protected $pagination;

    /**
     * @var  \Joomla\CMS\Filter\FilterForm     Formulário de filtros
     */
    protected $filterForm;

    /**
     * @var  array  Filtros ativos
     */
    protected $activeFilters;

    /**
     * Método principal para exibir a view
     *
     * @param   string  $tpl  O sufixo do template (layout).
     *
     * @return  void
     */
    public function display($tpl = null): void // [Checklist] Implementar o método display()
    {
        // 1. Carrega os dados do Model
        $this->items         = $this->get('Items');
        $this->pagination    = $this->get('Pagination');
        $this->filterForm    = $this->get('FilterForm');
        $this->activeFilters = $this->get('ActiveFilters');

        // 2. Adiciona a Barra de Ferramentas (Toolbar)
        $this->addToolbar();

        // 3. Chama o display do pai para renderizar o layout (default.php)
        parent::display($tpl);
    }

    /**
     * Método auxiliar para adicionar a barra de ferramentas (toolbar).
     *
     * @return  void
     */
    protected function addToolbar(): void // [Checklist] Chamar a classe Helper (ToolbarHelper)
    {
        // Define o título da página
        ToolbarHelper::title(Text::_('COM_SPLMS_ANNOUNCEMENTS'));

        // Pega as permissões (vamos assumir que o admin pode tudo)
        // $canDo = ...

        // Adiciona os botões padrão de uma lista
        ToolbarHelper::addNew('announcement.add'); // Aponta para a view 'announcement' (singular) e task 'add'
        ToolbarHelper::editList('announcement.edit');
        ToolbarHelper::deleteList(Text::_('JGLOBAL_CONFIRM_DELETE'), 'announcements.delete');
        
        // Botão de Opções (Configurações globais do componente)
        ToolbarHelper::preferences('com_splms');
    }
}