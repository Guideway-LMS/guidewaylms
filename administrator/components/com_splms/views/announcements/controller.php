<?php
/**
 * @package     com_splms
 * @subpackage  administrator
 * @license     GNU General Public License
 */

// Acesso restrito
defined('_JEXEC') or die;

// Classe de controlador mais segura e compatível com o Padrão Legado J3.
class SplmsControllerAnnouncements extends JControllerLegacy 
{
    /**
     * O método display é necessário no controlador da view para garantir
     * que a View e o Model sejam carregados corretamente no padrão J3.
     * * @param   bool  $cachable  Se a view deve ser cacheada.
     * @param   array $urlparams Parâmetros da URL.
     * * @return  self
     */
    public function display($cachable = false, $urlparams = array())
    {
        // 1. Define o nome da view ('announcements')
        $view = $this->input->get('view', 'announcements'); 
        
        // 2. Obtém a View e o Model
        $view = $this->getView($view, 'html');
        $model = $this->getModel('Announcements');
        $view->setModel($model, true);
        
        // 3. Renderiza a view (carrega o default.php)
        $view->display();
        return $this;
    }
}