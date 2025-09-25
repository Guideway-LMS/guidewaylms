<?php
namespace JExtstore\Component\Gptranslate\Administrator\Dispatcher;
/**
 * Backend entrypoint dispatcher of the component application
 *
 * @package GPTRANSLATE::administrator::components::com_gptranslate
 * @author Joomla! Extensions Store
 * @copyright (C) 2015 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html  
 */
defined ( '_JEXEC' ) or die ();

use Joomla\CMS\Dispatcher\ComponentDispatcher;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\Input\Input;
use Joomla\Registry\Registry;
use JExtstore\Component\Gptranslate\Administrator\Framework\Loader;

/**
 * Dispatcher class for the component backend
 */
class Dispatcher extends ComponentDispatcher {
	/**
	 * The extension namespace
	 * @var    string
	 */
	protected $namespace = 'JExtstore\\Component\\Gptranslate';
	
	/**
	 * Constructor for Dispatcher
	 *
	 * @param   CMSApplicationInterface     $app                The application instance
	 * @param   Input                       $input              The input instance
	 * @param   MVCFactoryInterface  $mvcFactory  The MVC factory instance
	 *
	 * @since   4.0.0
	 */
	public function __construct(CMSApplicationInterface $app, Input $input, MVCFactoryInterface $mvcFactory) {
		// Set MySql 5.7.8+ strict mode off
		Factory::getContainer()->get('DatabaseDriver')->setQuery("SET @@SESSION.sql_mode = ''")->execute();
		
		$option = $input->get('option') ?? 'com_gptranslate';
		
		// Define component path.
		if (!defined('JPATH_COMPONENT')) {
			define('JPATH_COMPONENT', JPATH_BASE . '/components/' . $option);
		}
		
		if (!defined('JPATH_COMPONENT_SITE')) {
			define('JPATH_COMPONENT_SITE', JPATH_SITE . '/components/' . $option);
		}
		
		if (!defined('JPATH_COMPONENT_ADMINISTRATOR')) {
			define('JPATH_COMPONENT_ADMINISTRATOR', JPATH_ADMINISTRATOR . '/components/' . $option);
		}
		
		// Auto loader setup
		// Register autoloader prefix
		require_once  JPATH_COMPONENT . '/Framework/Loader.php';
		Loader::setup();
		Loader::registerNamespacePsr4($this->namespace . '\Site', JPATH_COMPONENT );
		Loader::registerNamespacePsr4($this->namespace . '\Administrator', JPATH_COMPONENT_ADMINISTRATOR );
		
		// Manage partial language translations
		$jLang = $app->getLanguage();
		$jLang->load('com_gptranslate', JPATH_COMPONENT_ADMINISTRATOR, 'en-GB', true, true);
		if($jLang->getTag() != 'en-GB') {
			$jLang->load('com_gptranslate', JPATH_ADMINISTRATOR, null, true, false);
			$jLang->load('com_gptranslate', JPATH_COMPONENT_ADMINISTRATOR, null, true, false);
		}
		
		/**
		 * All SMVC logic is based on controller.task correcting the wrong Joomla concept
		 * of base execute on view names.
		 * When task is not specified because Joomla force view query string such as menu
		 * the view value is equals to controller and viewname = controller.display
		 */
		$controller_command = $app->getInput()->get('task', '');
		if (strpos($controller_command, '.')) {
			list($controller_name, $controller_task) = explode('.', $controller_command);
		} elseif ($controller_command) {
			$controller_name = $controller_command;
			$app->getInput()->set('controller', $controller_name);
			$app->getInput()->set('task', 'display');
		} else {
			// Defaults
			$app->getInput()->set('controller', 'cpanel');
			$app->getInput()->set('task', 'display');
		}
		
		if(isset($controller_name)) {
			$path = JPATH_COMPONENT . DIRECTORY_SEPARATOR . 'Controller' . DIRECTORY_SEPARATOR . ucfirst($controller_name) . 'Controller.php';
			if (!file_exists($path)) {
				$app->enqueueMessage(Text::_('COM_GPTRANSLATE_ERROR_NO_CONTROLLER_FILE'), 'error');
				$app->redirect(Route::_('index.php?option=com_gptranslate'));
			}
		}
		
		parent::__construct ( $app, $input, $mvcFactory );
	}
}
