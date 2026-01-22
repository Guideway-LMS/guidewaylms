<?php
/**
 * @package     com_splms
 * @subpackage  administrator
 * @license     GNU General Public License
 */

// Acesso restrito
defined('_JEXEC') or die;

/**
 * Model do formulário de Aviso (Announcement)
 * O NOME CORRETO É SplmsModelAnnouncement
 */
class SplmsModelAnnouncement extends JModelAdmin 
{
    /**
     * Método para carregar a classe Table correta.
     */
    public function getTable($type = 'Announcement', $prefix = 'SplmsTable', $config = [])
    {
        return JTable::getInstance($type, $prefix, $config);
    }

    /**
     * Método para carregar o arquivo XML do formulário.
     */
    public function getForm($data = [], $loadData = true)
    { 
        $form = $this->loadForm(
            'com_splms.announcement',
            'announcement',
            ['control' => 'jform', 'load_data' => $loadData]
        );

        if (empty($form)) {
            return false;
        }


        return $form;
    }

    /**
     * Método para carregar os dados no formulário.
     */
    protected function loadFormData()
    {
        $data = $this->getItem();

        if (empty($data->id)) {
            $app = JFactory::getApplication();
            $data = $this->getTable();
            $data->course_id = $app->input->getInt('course_id', 0);
        }

        return $data;
    }
}