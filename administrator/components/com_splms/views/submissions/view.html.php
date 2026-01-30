<?php
/**
 * @package     com_splms
 * @license     GNU/GPLv2 or later
 */

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView;

class SplmsViewSubmissions extends HtmlView
{
    protected $items;
    protected $pagination;

    public function display($tpl = null)
    {
        // Pega os dados do Model que criamos acima
        $this->items = $this->get('Items');
        $this->pagination = $this->get('Pagination');

        // Verifica erros
        if (count($errors = $this->get('Errors'))) {
            throw new Exception(implode("\n", $errors), 500);
        }

        parent::display($tpl);
    }
}