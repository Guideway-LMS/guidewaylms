<?php
/**
 * @package     JoomShaper\Component\Splms
 * @subpackage  Administrator.Table
 * @license     GNU General Public License
 */

// Define o Namespace (Prática moderna do J5, conforme wiki) [cite: 204]
namespace JoomShaper\Component\Splms\Administrator\Table;

// Importa as classes modernas que vamos usar [cite: 206]
use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseDriver;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

/**
 * Classe Table para o Mural de Avisos (Announcements)
 *
 * @since  4.1.3 (Sua nova versão)
 */
class AnnouncementTable extends Table  // [Checklist] Herdar de Joomla\CMS\Table\Table
{
    /**
     * Construtor da classe
     *
     * @param DatabaseDriver $db Objeto do driver de banco de dados.
     */
    public function __construct(DatabaseDriver $db)
    {
        // [Checklist] Aponta para a tabela e a chave primária
        parent::__construct('#__splms_course_announcements', 'id', $db);
    }

    /**
     * Método para validar os dados da tabela antes de salvar.
     *
     * @return  bool  Verdadeiro se os dados forem válidos, Falso se não.
     */
    public function check(): bool
    {
        // --- [Checklist] Implementar o método check() ---

        // 1. Validar o 'title' (Título)
        if (trim($this->title) === '') {
            $this->setError(Text::_('COM_SPLMS_ERROR_VALIDATION_TITLE_REQUIRED'));
            return false;
        }

        // 2. Validar o 'description' (que é o 'message' na sua especificação) [cite: 806]
        if (trim($this->description) === '') {
            $this->setError(Text::_('COM_SPLMS_ERROR_VALIDATION_DESCRIPTION_REQUIRED'));
            return false;
        }

        // 3. Preencher dados automáticos (created_by e created_on) [cite: 806]
        // Se for um novo item (sem ID), preenche quem criou e quando.
        if (empty($this->id)) {
            if (empty($this->created_by)) {
                $this->created_by = Factory::getUser()->id;
            }
            if (empty($this->created_on)) {
                $this->created_on = Factory::getDate()->toSql();
            }
        }

        return parent::check();
    }
}