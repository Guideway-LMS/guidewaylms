<?php
/**
 * @package     com_splms
 * @subpackage  administrator
 * @license     GNU General Public License
 */

// Acesso restrito
defined('_JEXEC') or die;

/**
 * Controller da LISTA de Avisos (Announcements)
 * Formato Legado (Joomla 3)
 */
class SplmsControllerAnnouncements extends JControllerAdmin
{
    /**
     * Define o model a ser usado (SplmsModelAnnouncements)
     *
     * @param   string  $name    Nome do Model
     * @param   string  $prefix  Prefixo da classe
     * @param   array   $config  Configurações
     *
     * @return  JModelLegacy
     */
    public function getModel($name = 'Announcements', $prefix = 'SplmsModel', $config = ['ignore_request' => true])
    {
        return parent::getModel($name, $prefix, $config);
    }
}