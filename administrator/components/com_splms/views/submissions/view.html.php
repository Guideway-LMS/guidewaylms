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
    
    // --- CORREÇÃO: Estas variáveis DEVEM ser PUBLIC ---
    public $filterForm;
    public $activeFilters;
    // --------------------------------------------------

    public function display($tpl = null)
    {
        // 1. Pega os dados do Model
        $this->items = $this->get('Items');
        $this->pagination = $this->get('Pagination');

        // 2. Pega os dados do Filtro (Isso lê o XML filter_submissions.xml)
        $this->filterForm    = $this->get('FilterForm');
        $this->activeFilters = $this->get('ActiveFilters');

        // Verifica erros
        if (count($errors = $this->get('Errors'))) {
            throw new Exception(implode("\n", $errors), 500);
        }

        parent::display($tpl);
    }
}