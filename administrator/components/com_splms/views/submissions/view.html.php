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
    public $sidebar; // Variável para armazenar o menu lateral

    public function display($tpl = null)
    {
        // Pega os dados do Model
        $this->items = $this->get('Items');
        $this->pagination = $this->get('Pagination');

        // Verifica erros
        if (count($errors = $this->get('Errors'))) {
            throw new Exception(implode("\n", $errors), 500);
        }

        // --- ADIÇÃO: Carregar a Barra Lateral (Menu) ---
        
        // 1. Carrega o Helper se ainda não estiver carregado
        if (!class_exists('SplmsHelper')) {
            require_once JPATH_COMPONENT_ADMINISTRATOR . '/helpers/splms.php';
        }

        // 2. Chama a função que cria os links, destacando 'submissions'
        SplmsHelper::addSubmenu('submissions');

        // 3. Renderiza o HTML da sidebar para usar no Template
        $this->sidebar = JHtmlSidebar::render();
        // -----------------------------------------------

        parent::display($tpl);
    }
}