<?php
/**
 * @package     com_splms
 * @subpackage  administrator
 * @license     GNU General Public License
 */

// Acesso restrito
defined('_JEXEC') or die;

/**
 * Classe Table para o Mural de Avisos (Announcements)
 * Formato Legado (Joomla 3)
 */
class SplmsTableAnnouncement extends JTable
{
    /**
     * Construtor da classe
     *
     * @param   JDatabaseDriver  &$db  Driver do banco de dados (passado por referência)
     */
    public function __construct(&$db)
    {
        // Aponta para a tabela (#__splms_course_announcements) e a chave primária (id)
        parent::__construct('#__splms_course_announcements', 'id', $db);
    }

    /**
     * Método para validar os dados da tabela antes de salvar.
     * (A lógica aqui dentro é quase idêntica à versão moderna)
     *
     * @return  bool  Verdadeiro se os dados forem válidos, Falso se não.
     */
    public function check()
    {
        // 1. Validar o 'title' (Título)
        if (trim($this->title) === '') {
            $this->setError(JText::_('COM_SPLMS_ERROR_VALIDATION_TITLE_REQUIRED'));
            return false;
        }

        // 2. Validar o 'description' (que é o 'message')
        if (trim($this->message) === '') {
            $this->setError(JText::_('COM_SPLMS_ERROR_VALIDATION_MESSAGE_REQUIRED'));
            return false;
        }

        // 3. Preencher dados automáticos (created_by e created_on)
        if (empty($this->id)) {
            if (empty($this->created_by)) {
                $this->created_by = JFactory::getUser()->id;
            }
            if (empty($this->created_at)) {
                $this->created_at = JFactory::getDate()->toSql();
            }
        }

        return parent::check();
    }
}