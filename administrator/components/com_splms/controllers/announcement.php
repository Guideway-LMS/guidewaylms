<?php
/**
 * @package     com_splms
 * @subpackage  administrator
 * @license     GNU General Public License
 */

// Acesso restrito
defined('_JEXEC') or die;

/**
 * Controller do FORMULÁRIO de Aviso (Announcement)
 * Formato Legado (Joomla 3)
 */
class SplmsControllerAnnouncement extends JControllerForm
{
    /**
     * Define o prefixo do model a ser usado (SplmsModel)
     */
    public function __construct($config = [])
    {
        $this->view_list = 'announcements'; // View para onde voltar ao salvar/cancelar
        parent::__construct($config);
    }

}