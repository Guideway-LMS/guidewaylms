<?php
/**
 * @package     com_splms
 * @subpackage  site
 * @license     GNU General Public License
 */

// Acesso restrito
defined('_JEXEC') or die;

/**
 * View da lista de Avisos (Announcements) para o frontend
 * Formato Legado (Joomla 3)
 */
class SplmsViewAnnouncements extends JViewLegacy
{
    /**
     * @var  object[]  Itens da lista (os avisos)
     */
    protected $items;

    /**
     * @var  Joomla\CMS\Pagination\Pagination  Objeto de paginação
     */
    protected $pagination;

    /**
     * Método principal para exibir a view
     *
     * @param   string  $tpl  O sufixo do template (layout).
     *
     * @return  void
     */
    public function display($tpl = null)
    {
        // 1. [A CORREÇÃO] Carrega o Model (SplmsModelAnnouncements)
        // Força o carregamento do Model antes de o usarmos
        $this->getModel();

        // 2. Carrega os dados (Agora $this->get() vai funcionar)
        $this->items         = $this->get('Items');
        $this->pagination    = $this->get('Pagination');

        // 3. Chama o display do pai para renderizar o layout (default.php)
        parent::display($tpl);
    }
}