<?php
/**
 * @package     JoomShaper\Component\Splms
 * @subpackage  Administrator.View
 * @license     GNU General Public License
 */

// Define o Namespace (Prática moderna J5)
namespace JoomShaper\Component\Splms\Administrator\View\Announcement;

// Importa as classes modernas que vamos usar
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use stdClass; // Usado para $this->item

/**
 * View do formulário de Aviso (Announcement)
 *
 * @since  4.1.3
 */
class View extends BaseHtmlView // [Checklist] Herda da classe de View base
{
    /**
     * @var  Form  O objeto do formulário
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
    public function display($tpl = null): void // [Checklist] Implementar o método display()
    {
        // 1. Carrega os dados do Model (Tarefa 1.3.1)
        // O get('Form') chama o método 'getForm' do nosso AnnouncementModel
        $this->form = $this->get('Form');

        // O get('Item') chama o método 'loadFormData' e 'getItem' do nosso AnnouncementModel
        $this->item = $this->get('Item');

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
    protected function addToolbar(): void // [Checklist] Adicionar os botões da barra de ferramentas
    {
        // Define o título da página
        $isNew = ($this->item->id == 0);
        $title = $isNew ? Text::_('COM_SPLMS_ANNOUNCEMENT_NEW_TITLE') : Text::_('COM_SPLMS_ANNOUNCEMENT_EDIT_TITLE');
        
        ToolbarHelper::title($title); // TODO: Criar estas strings de idioma

        // Pega as permissões (vamos assumir que o admin pode tudo)
        // $canDo = ...

        // Adiciona os botões padrão de um formulário
        ToolbarHelper::apply('announcement.apply'); // Salvar
        ToolbarHelper::save('announcement.save');   // Salvar e Fechar
        ToolbarHelper::cancel('announcement.cancel'); // Cancelar
    }
}