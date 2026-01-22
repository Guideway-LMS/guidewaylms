<?php
/**
 * @package     com_splms
 * @subpackage  administrator
 * @license     GNU General Public License
 */

// Acesso restrito
defined('_JEXEC') or die;

/**
 * View do formulário de Aviso (Announcement)
 * Formato Legado (Joomla 3)
 */
class SplmsViewAnnouncement extends JViewLegacy
{
    /**
     * @var  JForm  O objeto do formulário
     */
    protected $form;

    /**
     * @var  stdClass  O item (aviso) a ser editado
     */
    protected $item;

    /**
     * Método principal para exibir a view
     *
     * @param   string  $tpl  O sufixo do template (layout).
     *
     * @return  void
     */
    public function display($tpl = null)
    {
        // 1. [A CORREÇÃO] Carrega o Model (SplmsModelAnnouncement)
        // ESSA LINHA FORÇA O CARREGAMENTO DO MODEL J3
        $this->getModel();

        // 2. Carrega os dados
        $this->form = $this->get('Form');
        $this->item = $this->get('Item');

        // 3. Adiciona a Barra de Ferramentas (Toolbar)
        $this->addToolbar();

        // 4. Chama o display do pai para renderizar o layout (edit.php)
        parent::display($tpl);
    }

    /**
     * Método auxiliar para adicionar a barra de ferramentas (toolbar).
     *
     * @return  void
     */
    protected function addToolbar()
    {
        // Define o título da página
        $isNew = (empty($this->item) || empty($this->item->id));
        $title = $isNew ? JText::_('COM_SPLMS_ANNOUNCEMENT_NEW_TITLE') : JText::_('COM_SPLMS_ANNOUNCEMENT_EDIT_TITLE');
        
        JToolBarHelper::title($title);

        // Adiciona os botões padrão de um formulário
        JToolBarHelper::apply('announcement.apply'); // Salvar
        JToolBarHelper::save('announcement.save');   // Salvar e Fechar
        JToolBarHelper::cancel('announcement.cancel'); // Cancelar
    }
}