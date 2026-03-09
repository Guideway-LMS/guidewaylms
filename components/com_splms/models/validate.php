<?php
/**
 * GUIDEWAY CUSTOM - 
 * Model de validação pública de certificados
 */
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

class SplmsModelValidate extends BaseDatabaseModel
{
    /**
     * Busca certificado pelo hash (certificate_no)
     * Retorna os dados do aluno e curso se válido, null se inválido
     */
    public function getCertificadoPorHash($hash)
    {
        if (empty($hash)) {
            return null;
        }

        $db    = $this->getDbo();
        $query = $db->getQuery(true);

        $query->select('c.id, c.certificate_no, c.issue_date, c.instructor')
              ->select('u.name AS aluno')
              ->select('co.title AS curso')
              ->from($db->quoteName('#__splms_certificates', 'c'))
              ->join('LEFT', $db->quoteName('#__users', 'u') . ' ON u.id = c.userid')
              ->join('LEFT', $db->quoteName('#__splms_courses', 'co') . ' ON co.id = c.course_id')
              ->where($db->quoteName('c.certificate_no') . ' = ' . $db->quote($hash))
              ->where($db->quoteName('c.published') . ' = 1');

        $db->setQuery($query);
        return $db->loadObject();
    }
}
