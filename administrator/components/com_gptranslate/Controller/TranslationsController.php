<?php
namespace JExtstore\Component\Gptranslate\Administrator\Controller;
/**
 * @package GPTRANSLATE::LINKS::administrator::components::com_gptranslate
 * @subpackage controllers
 * @author Joomla! Extensions Store
 * @copyright (C) 2015 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html  
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use JExtstore\Component\Gptranslate\Administrator\Framework\Helpers\Translations;
use JExtstore\Component\Gptranslate\Administrator\Framework\Controller as GptranslateController;

/**
 * Controller for links entity tasks
 * @package GPTRANSLATE::LINKS::administrator::components::com_gptranslate
 * @subpackage controllers
 * * @since 1.0
 */
class TranslationsController extends GptranslateController {
	/**
	 * Set model state from session userstate
	 * @access protected
	 * @param string $scope
	 * @return object
	 */
	protected function setModelState($scope = 'default', $ordering = true): object  {
		$option = $this->option;
		
		// Get request state
		$filter_order = $this->getUserStateFromRequest ( "$option.$scope.filter_order", 'filter_order', 's.id', 'cmd' );
		$filter_order_Dir = $this->getUserStateFromRequest ( "$option.$scope.filter_order_Dir", 'filter_order_Dir', 'desc', 'word' );
		$filter_state = $this->getUserStateFromRequest ( "$option.$scope.filterstate", 'filter_state', '' );
		$menu_filter_state = $this->getUserStateFromRequest ( "$option.$scope.menufilterstate", 'menu_filter_state', '' );
		$language = $this->getUserStateFromRequest ( "$option.$scope.languagegptranslate", 'languagegptranslate', '' );
		$translationEngine = $this->getUserStateFromRequest ( "$option.$scope.translationengine", 'translationengine', '' );
		
		$defaultModel = parent::setModelState ( $scope, false );
		
		// Set model ordering state
		$defaultModel->setState ( 'order', $filter_order );
		$defaultModel->setState ( 'order_dir', $filter_order_Dir );
		$defaultModel->setState ( 'state', $filter_state );
		$defaultModel->setState ( 'menustate', $menu_filter_state );
		$defaultModel->setState ( 'languagegptranslate', $language );
		$defaultModel->setState ( 'translationengine', $translationEngine );
		
		return $defaultModel;
	}
	
	/**
	 * Default listEntities
	 *
	 * @access public
	 * @param $cachable string
	 *       	 the view output will be cached
	 * @return void
	 */
	public function display($cachable = false, $urlparams = false) {
		// Set model state
		$defaultModel = $this->setModelState('translations');
		
		// Parent construction and view display
		parent::display($cachable);
	}
	
	/**
	 * Manage entity apply/save after edit entity
	 *
	 * @access public
	 * @return bool
	 */
	public function saveEntity(): bool {
		// Security layer for tags html outputted fields
		$sanitizedFields = array('pagelink');
		foreach ($sanitizedFields as $field) {
			$this->requestArray[$field] = strip_tags($this->requestArray[$field]);
		}
		
		return parent::saveEntity();
	}
	
	/**
	 * Export translations as CSV data
	 *
	 * @access public
	 * @return void
	 */
	public function exportEntities() {
		$option = $this->option;
		
		// Set model state
		$defaultModel = $this->setModelState('translations');
		
		$viewType = $this->document->getType ();
		$coreName = $this->getName ();
		$viewLayout = $this->app->getInput()->get ( 'layout', 'default' );
		
		$view = $this->getView ( $coreName, $viewType, '', array (
				'base_path' => $this->basePath
		) );
		
		// Push the model into the view (as default)
		$view->setModel ( $defaultModel, true );
		
		// Set the layout
		$view->setLayout ( $viewLayout );
		$view->display ('export');
	}
	
	/**
	 * Import translations from CSV data
	 *
	 * @access public
	 * @return void
	 */
	public function importEntities() {
		$option = $this->option;
		
		// Get the file manager instance with db connector dependency injection
		$filesManager = new Translations(Factory::getContainer()->get('DatabaseDriver'), $this->app);
		
		if(!$filesManager->import()) {
			// Model set exceptions for something gone wrong, so enqueue exceptions and levels on application object then set redirect and exit
			$filesManagerException = $filesManager->getError(null, false);
			$this->app->enqueueMessage($filesManagerException->getMessage(), $filesManagerException->getExceptionLevel());
			$this->setRedirect ( "index.php?option=$option&task=translations.display", Text::_('COM_GPTRANSLATE_ERROR_IMPORT'));
			return false;
		}
		
		$this->setRedirect ( "index.php?option=$option&task=translations.display", Text::_('COM_GPTRANSLATE_SUCCESS_IMPORT'));
	}
	
	
	/**
	 * Migrate translations to https domain
	 *
	 * @access public
	 * @return void
	 */
	public function migrateEntities() {
		$option = $this->option;
		
		//Load della  model e bind store
		$model = $this->getModel ();
		
		$currentDomain = $this->input->getString('migratetranslations_currentdomain');
		$newDomain = $this->input->getString('migratetranslations_newdomain');
		
		if(!$result = $model->domainsMigrate($currentDomain, $newDomain)) {
			// Model set exceptions for something gone wrong, so enqueue exceptions and levels on application object then set redirect and exit
			$modelException = $model->getException(null, false);
			$this->app->enqueueMessage($modelException->getMessage(), $modelException->getExceptionLevel());
			$this->setRedirect ( "index.php?option=$option&task=cpanel.display", Text::_('COM_GPTRANSLATE_ERROR_MIGRATE_DOMAINS'));
			return false;
		}
		
		$this->setRedirect ( "index.php?option=$option&task=translations.display", Text::_('COM_GPTRANSLATE_SUCCESS_MIGRATE_DOMAINS'));
	}
	
	/**
	 * 
	 * Class Constructor
	 * 
	 * @access public
	 * @param $config
	 * @return Object&
	 */
	public function __construct($config = array(), ?MVCFactoryInterface $factory = null, $app = null, $input = null) {
		parent::__construct($config, $factory, $app, $input);
		
		// Register Extra tasks
		$this->registerTask ( 'applyEntity', 'saveEntity' );
		$this->registerTask ( 'unpublish', 'publishEntities' );
		$this->registerTask ( 'publish', 'publishEntities' );
	}
}
?>