<?php
namespace JExtstore\Component\Gptranslate\Administrator\Controller;
/**
 *
 * @package GPTRANSLATE::CONFIG::administrator::components::com_gptranslate
 * @subpackage controllers
 * @author Joomla! Extensions Store
 * @copyright (C) 2015 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html 
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Component\ComponentHelper;
use JExtstore\Component\Gptranslate\Administrator\Framework\Controller as GptranslateController;
use JExtstore\Component\Gptranslate\Administrator\Framework\Helpers\Config as GptranslateConfigFile;

/**
 * Config controller concrete implementation
 *
 * @package GPTRANSLATE::CPANEL::administrator::components::com_gptranslate
 * @subpackage controllers
 * @since 1.6
 */
class ConfigController extends GptranslateController {

	/**
	 * Show configuration
	 * @access public
	 * @return void
	 */
	public function display($cachable = false, $urlparams = false) {
		parent::display($cachable);
	}

	/**
	 * Save config entity
	 * @access public
	 * @return bool
	 */
	public function saveEntity(): bool {
		$model = $this->getModel();
		$option = $this->option;
		
		if(!$model->storeEntity()) {
			// Model set exceptions for something gone wrong, so enqueue exceptions and levels on application object then set redirect and exit
			$modelException = $model->getException(null, false);
			$this->app->enqueueMessage($modelException->getMessage(), $modelException->getExceptionLevel());
			$this->setRedirect ( "index.php?option=$option&task=config.display", Text::_('COM_GPTRANSLATE_ERROR_SAVING_PARAMS'));
			return false;
		}
		$this->setRedirect( "index.php?option=$option&task=config.display", Text::_('COM_GPTRANSLATE_SAVED_PARAMS'));
		
		return true;
	}
	
	/**
	 * Export sources as db table entities
	 *
	 * @access public
	 * @return void
	 */
	public function exportConfig() {
		$option = $this->option;
		
		// Get the file manager instance with db connector dependency injection
		$filesManager = new GPTranslateConfigFile ( Factory::getContainer()->get('DatabaseDriver'), $this->app );
		
		$cParams = ComponentHelper::getParams('com_gptranslate');
		$filesManagerExportResult = $filesManager->export ();
		
		if (! $filesManagerExportResult) {
			// Model set exceptions for something gone wrong, so enqueue exceptions and levels on application object then set redirect and exit
			$filesManagerException = $filesManager->getError ( null, false );
			$this->app->enqueueMessage ( $filesManagerException->getMessage (), $filesManagerException->getExceptionLevel () );
			$this->setRedirect ( "index.php?option=$option&task=config.display", Text::_ ( 'COM_GPTRANSLATE_ERROR_CONFIG_EXPORT' ) );
			return false;
		}
		
		$this->setRedirect ( "index.php?option=$option&task=config.display", Text::_ ( 'COM_GPTRANSLATE_SUCCESS_CONFIG_EXPORT' ) );
	}
	
	/**
	 * Import sources as db table entities
	 *
	 * @access public
	 * @return void
	 */
	public function importConfig() {
		$option = $this->option;
		
		// Get the file manager instance with db connector dependency injection
		$filesManager = new GPTranslateConfigFile ( Factory::getContainer()->get('DatabaseDriver'), $this->app );
		
		$cParams = ComponentHelper::getParams('com_gptranslate');
		$filesManagerImportResult = $filesManager->import ();
		
		if (! $filesManagerImportResult) {
			// Model set exceptions for something gone wrong, so enqueue exceptions and levels on application object then set redirect and exit
			$filesManagerException = $filesManager->getError ( null, false );
			$this->app->enqueueMessage ( $filesManagerException->getMessage (), $filesManagerException->getExceptionLevel () );
			$this->setRedirect ( "index.php?option=$option&task=config.display", Text::_ ( 'COM_GPTRANSLATE_ERROR_CONFIG_IMPORT' ) );
			return false;
		}
		
		$this->setRedirect ( "index.php?option=$option&task=config.display", Text::_ ( 'COM_GPTRANSLATE_SUCCESS_CONFIG_IMPORT' ) );
	}
}