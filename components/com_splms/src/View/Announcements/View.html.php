<?php
/**
 * @package     JoomShaper\Component\Splms
 * @subpackage  Site.View
 * @license     GNU General Public License
 */

// Define o Namespace (Prática moderna J5 para o 'site')
namespace JoomShaper\Component\Splms\Site\View\Announcements;

// Importa as classes modernas que vamos usar
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

/**
 * View da lista de Avisos (Announcements) para o frontend
 *
 * @since  4.1.3
 */
class View extends BaseHtmlView
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
     * Método principal para exibir a view
     *
     * @param   string  $tpl  O sufixo do template (layout).
     *
     * @return  void
     */
    public function display($tpl = null): void // [Checklist] Implementar display()
    {
        // 1. Carrega os dados do Model (Tarefa 1.5.1 / 1.5.2)
        // O get('Items') chama o método 'getListQuery' do nosso AnnouncementsModel (site)
        $this->items         = $this->get('Items'); // [Checklist] carregar o Model (getItems())
        $this->pagination    = $this->get('Pagination');

        // 2. Chama o display do pai para renderizar o layout (default.php)
        parent::display($tpl);
    }
}