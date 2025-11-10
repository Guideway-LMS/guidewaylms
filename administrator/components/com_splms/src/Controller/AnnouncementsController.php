<?php
/**
 * @package     JoomShaper\Component\Splms
 * @subpackage  Administrator.Controller
 * @license     GNU General Public License
 */

// Define o Namespace (Prática moderna J5)
namespace JoomShaper\Component\Splms\Administrator\Controller;

// Importa a classe moderna que vamos usar
use Joomla\CMS\MVC\Controller\BaseDatabaseController;

/**
 * Controller da lista de Avisos (Announcements)
 *
 * @since  4.1.3
 */
class AnnouncementsController extends BaseDatabaseController // [Checklist] Herdar de BaseDatabaseController
{
    /**
     * NOTA: Também não precisamos de código aqui.
     *
     * A classe 'pai' (BaseDatabaseController) já contém
     * toda a lógica para a tarefa 'delete'.
     *
     * Ela funciona automaticamente porque:
     * 1. A view chama-se 'Announcements'.
     * 2. O Joomla procura um controller chamado 'AnnouncementsController'.
     * 3. O BaseDatabaseController procura um model chamado 'AnnouncementsModel' (plural).
     * 4. O 'AnnouncementsModel' diz-lhe o nome da tabela.
     */
}