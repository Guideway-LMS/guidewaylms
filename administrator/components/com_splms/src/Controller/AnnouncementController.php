<?php
/**
 * @package     JoomShaper\Component\Splms
 * @subpackage  Administrator.Controller
 * @license     GNU General Public License
 */

// Define o Namespace (Prática moderna J5)
namespace JoomShaper\Component\Splms\Administrator\Controller;

// Importa a classe moderna que vamos usar
use Joomla\CMS\MVC\Controller\FormController;

/**
 * Controller do formulário de Aviso (Announcement)
 *
 * @since  4.1.3
 */
class AnnouncementController extends FormController // [Checklist] Herdar de FormController
{
    /**
     * NOTA: Não precisamos de adicionar NENHUM código aqui.
     *
     * A classe 'pai' (FormController) já contém
     * toda a lógica para as tarefas 'apply', 'save', 'cancel', 'edit' e 'add'.
     *
     * Ela funciona automaticamente porque:
     * 1. A view chama-se 'Announcement'.
     * 2. O Joomla procura um controller chamado 'AnnouncementController'.
     * 3. O FormController procura um model chamado 'AnnouncementModel' (que nós criámos).
     * 4. O 'AnnouncementModel' carrega a 'AnnouncementTable' (que nós criámos).
     *
     * Esta classe só precisa de existir para que o Joomla a possa encontrar.
     */
}