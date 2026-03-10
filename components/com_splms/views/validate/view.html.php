<?php
/**
 * GUIDEWAY CUSTOM - 09/03/2026 - Joshua - View de validacao publica de certificados
 */
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView;

class SplmsViewValidate extends HtmlView
{
    protected $certificado;
    protected $hash;
    protected $valido;

    public function display($tpl = null)
    {
        $input      = Factory::getApplication()->input;
        $this->hash = $input->getString('hash', '');

        if (!empty($this->hash)) {
            $db    = Factory::getDbo();
            $query = $db->getQuery(true);

            $query->select('c.id, c.certificate_no, c.issue_date, c.instructor')
                  ->select('u.name AS aluno')
                  ->select('co.title AS curso')
                  ->from($db->quoteName('#__splms_certificates', 'c'))
                  ->join('LEFT', $db->quoteName('#__users', 'u') . ' ON u.id = c.userid')
                  ->join('LEFT', $db->quoteName('#__splms_courses', 'co') . ' ON co.id = c.course_id')
                  ->where($db->quoteName('c.certificate_no') . ' = ' . $db->quote($this->hash))
                  ->where($db->quoteName('c.published') . ' = 1');

            $db->setQuery($query);
            $this->certificado = $db->loadObject();
            $this->valido      = !empty($this->certificado);
        } else {
            $this->valido      = false;
            $this->certificado = null;
        }

        parent::display($tpl);
    }
}
