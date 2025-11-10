<?php
/**
 * @package     JoomShaper\Component\Splms
 * @subpackage  Administrator.Model
 * @license     GNU General Public License
 */

// Define o Namespace (Prática moderna J5)
namespace JoomShaper\Component\Splms\Administrator\Model;

// Importa as classes modernas que vamos usar
use Joomla\CMS\MVC\Model\AdministratorModel;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Factory;
use Joomla\CMS\Application\ApplicationInterface;

/**
 * Model do formulário de Aviso (Announcement)
 *
 * @since  4.1.3
 */
class AnnouncementModel extends AdministratorModel // [Checklist] Herdar de AdministratorModel
{
    /**
     * Método para carregar a classe Table correta.
     * Este é o teste do autoloading (Tarefa 1.1.2)
     *
     * @param   string  $name    O nome da Tabela (ex: 'Announcement')
     * @param   string  $prefix  O prefixo do namespace (vamos usar o nosso)
     * @param   array   $options Array de configuração
     *
     * @return  Table
     */
    public function getTable($name = 'Announcement', $prefix = 'Table', $options = []): Table
    {
        // [Checklist] Implementar getTable()
        // Força o Joomla a usar a nossa classe com namespace moderno
        return Table::getInstance($name, 'JoomShaper\Component\Splms\Administrator\Table\\', $options);
    }

    /**
     * Método para carregar o arquivo XML do formulário.
     *
     * @param   array    $data      Dados para o formulário
     * @param   boolean  $loadData  Verdadeiro para carregar dados por padrão
     *
     * @return  Form|false  Objeto Form ou false em caso de erro
     */
    public function getForm($data = [], $loadData = true)
    {
        // [Checklist] Implementar getForm()
        // Carrega o formulário XML que definirá os campos
        // O Joomla procurará por: administrator/components/com_splms/forms/announcement.xml
        $form = $this->loadForm(
            'com_splms.announcement', // Nome único do formulário
            'announcement',          // Nome do arquivo XML (announcement.xml)
            ['control' => 'jform', 'load_data' => $loadData]
        );

        if (empty($form)) {
            return false;
        }

        return $form;
    }

    /**
     * Método para carregar os dados no formulário.
     *
     * @return  mixed  Os dados do item ou um array vazio.
     */
    protected function loadFormData()
    {
        // [Checklist] Implementar loadFormData()
        
        // Tenta carregar os dados de um item existente (edição)
        $data = $this->getItem();

        // Se for um item NOVO (pré-população)
        if (empty($data->id)) {
            $app = Factory::getApplication();
            
            // [Checklist] Pré-popular course_id (se vier da URL, por ex.)
            // O usuário pode ter vindo de um link dentro de um curso específico
            $data = $this->getTable(); // Pega um objeto Table vazio
            $data->course_id = $app->input->getInt('course_id', 0);
        }

        return $data;
    }

    /**
     * [Checklist] Implementar save() e delete()
     *
     * Não precisamos implementar save() ou delete() aqui.
     * Os métodos da classe pai (AdministratorModel) já fazem todo o trabalho:
     * 1. Eles chamam o $this->getTable() (que nós sobrescrevemos).
     * 2. Eles chamam o $table->check() (que nós criámos na Tarefa 1.1.2).
     * 3. Eles salvam ou deletam os dados.
     *
     * O 'pai' é suficiente.
     */
}