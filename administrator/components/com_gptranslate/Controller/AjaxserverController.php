<?php 
namespace JExtstore\Component\Gptranslate\Administrator\Controller;
/**
 * @package GPTRANSLATE::AJAXSERVER::administrator::components::com_gptranslate
 * @subpackage controllers
 * @author Joomla! Extensions Store
 * @copyright (C) 2024 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html  
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use JExtstore\Component\Gptranslate\Administrator\Framework\Controller as GptranslateController;

/**
 * Controller for links entity tasks
 * @package GPTRANSLATE::AJAXSERVER::administrator::components::com_gptranslate
 * @subpackage controllers
 * @since 2.4
 */ 
class AjaxserverController extends GptranslateController { 
	/**
	 * AS SMVC entity here we treat HTTP request and identifier map
	 * @access public
	 * @param $cachable string
	 *       	 the view output will be cached
	 * @return void
	 */
	function display($cachable = false, $urlparams = false) {
		// Id entità risposta ajax che identifica il subtask da eseguire in questo caso
		$params = json_decode($this->app->getInput()->getString('data', null));
		
		// Load additional models and make Dependency Injection thanks to JS controls
		$DIModels = @$params->DIModels;
		$models = array();
		if(!empty($DIModels)) {
			foreach ($DIModels as $DIModel) {
				if($DIModel->modelside != $this->app->getClientId()) {
					// Add extra include paths
					BaseDatabaseModel::addIncludePath(JPATH_COMPONENT_SITE . 'Model/');
				}
				$models[$DIModel->modelname] = $this->getModel ($DIModel->modelname);
			}
		}
		// This model maps Remote Procedure Call
		$model = $this->getModel ();
		$userData = $model->loadAjaxEntity ($params->idtask, $params->param, $models);
		
	 	// Format response for JS client as requested
		$document = $this->app->getDocument();
		$viewType = $document->getType ();
		$coreName = $this->getName ();
		
		$view =  $this->getView ( $coreName, $viewType, '', array ('base_path' => $this->basePath ) );
		$view->display ($userData);
	} 
}