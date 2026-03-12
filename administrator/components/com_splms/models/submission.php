<?php
defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\AdminModel;

class SplmsModelSubmission extends AdminModel
{
    /**
     * Retorna o formulário XML para validar os dados
     */
    public function getForm($data = array(), $loadData = true)
    {
        // Carrega o XML que vamos criar no Passo 2
        $form = $this->loadForm('com_splms.submission', 'submission', array('control' => 'jform', 'load_data' => $loadData));
        return $form;
    }

    /**
     * Diz ao Joomla qual tabela usar (A que você criou com a lógica de e-mail!)
     */
    public function getTable($type = 'Submission', $prefix = 'SplmsTable', $config = array())
    {
        return parent::getTable($type, $prefix, $config);
    }
}