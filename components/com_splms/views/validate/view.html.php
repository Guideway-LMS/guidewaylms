<?php
/**
 * GUIDEWAY CUSTOM -  * View de validação pública de certificados
 */
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

class SplmsViewValidate extends HtmlView
{
    protected $certificado;
    protected $hash;
    protected $valido;

    public function display($tpl = null)
    {
        $input = Factory::getApplication()->input;
        $this->hash = $input->getString('hash', '');

        if (!empty($this->hash)) {
            // Carrega o model e busca o certificado
            BaseDatabaseModel::addIncludePath(JPATH_SITE . '/components/com_splms/models');
            $model = BaseDatabaseModel::getInstance('Validate', 'SplmsModel');

            $this->certificado = $model->getCertificadoPorHash($this->hash);
            $this->valido      = !empty($this->certificado);
        } else {
            $this->valido      = false;
            $this->certificado = null;
        }

        parent::display($tpl);
    }
}
