<?php
namespace JExtstore\Component\Gptranslate\Administrator\Model;
/**
 *
 * @package GPTRANSLATE::CONFIG::administrator::components::com_gptranslate
 * @subpackage models
 * @author Joomla! Extensions Store
 * @copyright (C) 2015 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html 
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\Model\FormModel;
use Joomla\CMS\Table\Asset;
use Joomla\CMS\Access\Rules;
use Joomla\CMS\Table\Extension;
use Joomla\CMS\Form\Form;
use Joomla\Event\Event;
use JExtstore\Component\Gptranslate\Administrator\Framework\Exception\Exceptions;
use JExtstore\Component\Gptranslate\Administrator\Framework\Exception as GptranslateException;

/**
 * Config model responsibilities
 *
 * @package GPTRANSLATE::CONFIG::administrator::components::com_gptranslate
 * @subpackage models
 * @since 1.6
 */
interface IConfigModel {
	
	/**
	 * Ottiene i dati di configurazione da db params field record component
	 *
	 * @access public
	 * @return Object
	 */
	public function &getData();
	
	/**
	 * Effettua lo store dell'entity config
	 *
	 * @access public
	 * @return boolean
	 */
	public function storeEntity();
}

/**
 * Config model concrete implementation
 *
 * @package GPTRANSLATE::CONFIG::administrator::components::com_gptranslate
 * @subpackage models
 * @since 1.6
 */
class ConfigModel extends FormModel implements IConfigModel {
	use Exceptions;
	
	/**
	 * Variables in request array
	 *
	 * @access protected
	 * @var Object
	 */
	protected $requestArray;
	
	/**
	 * App reference
	 *
	 * @access protected
	 * @var Object
	 */
	protected $appInstance;
	
	/**
	 * Database reference
	 *
	 * @access protected
	 * @var Object
	 */
	protected $dbInstance;
	
	/**
	 * Clean the cache
	 * @param   string   $group      The cache group
	 * @param   integer  $client_id  The ID of the client
	 * @return  void
	 * @since   11.1
	 */
	private function cleanComponentCache($group = null, $client_id = 0) {
		// Initialise variables;
		$conf = Factory::getApplication()->getConfig();
	
		$options = array(
				'defaultgroup' => ($group) ? $group : $this->appInstance->getInput()->get('option'),
				'cachebase' => ($client_id) ? JPATH_ADMINISTRATOR . '/cache' : $conf->get('cache_path', JPATH_CACHE));
	
		$cache = Factory::getContainer()->get(\Joomla\CMS\Cache\CacheControllerFactoryInterface::class)->createCacheController( 'callback', $options );
		$cache->clean();
	
		// Trigger the onContentCleanCache event.
		$this->appInstance->getDispatcher()->dispatch('onContentCleanCache', new Event('onContentCleanCache', $options));
	}
	
	/**
	 * Ottiene i dati di configurazione da db params field record component
	 *
	 * @access public
	 * @return Object
	 */
	private function &getConfigData() { 
		$instance = ComponentHelper::getParams('com_gptranslate'); 
		return $instance;
	}
	
	/**
	 * Effettua lo storing dell'asset delle permissions sul component level
	 *
	 * @access protected
	 * @return boolean
	 */
	protected function storePermissionsAsset($data) {
		// Save the rules.
		if (isset ( $data ['params'] ) && isset ( $data ['params'] ['rules'] )) {
			$form = $this->getForm ( $data );
			// Validate the posted data.
			$postedRules = $this->validate ( $form, $data ['params'] );
			
			$rules = new Rules ( $postedRules ['rules'] );
			$asset = new Asset($this->dbInstance);
			
			if (! $asset->loadByName ( $data ['option'] )) {
				$root = new Asset($this->dbInstance);
				$root->loadByName ( 'root.1' );
				$asset->name = $data ['option'];
				$asset->title = $data ['option'];
				$asset->setLocation ( $root->id, 'last-child' );
			}
			$asset->rules = ( string ) $rules;
			
			if (! $asset->check () || ! $asset->store ()) {
				$this->setException ( $asset->getError () );
				return false;
			}
		}
		
		return true;
	}
	
	/**
	 * Method to get a form object.
	 *
	 * @param	array	$data		Data for the form.
	 * @param	boolean	$loadData	True if the form is to load its own data (default case), false if not.
	 *
	 * @return	mixed	A \Joomla\CMS\Form\Form object on success, false on failure
	 * @since	1.6
	 */
	public function getForm($data = array(), $loadData = true) {
		Form::addFormPath ( JPATH_ADMINISTRATOR . '/components/com_gptranslate' );
	
		// Get the form.
		$form = $this->loadForm ( 'com_gptranslate.component', 'config', array ('control' => 'params', 'load_data' => $loadData ), false, '/config' );
	
		if (empty ( $form )) {
			return false;
		}
	
		return $form;
	}
	
	/**
	 * Ottiene i dati di configurazione del componente
	 *
	 * @access public
	 * @return Object
	 */
	public function &getData() {
		return $this->getConfigData ();
	}
	/**
	 * Effettua lo store dell'entity config
	 *
	 * @access public
	 * @return boolean
	 */
	public function storeEntity() {
		$table = new Extension($this->dbInstance);

		try {
			// Found as installed extension
			if (!$extensionID = $table->find(array('element' => 'com_gptranslate'))) {
				throw new GptranslateException($table->getError (), 'error');
			} 
			
			$table->load($extensionID);

			// Translate posted jform array to params for ORM table binding
			$post = $this->appInstance->getInput()->post;
			
			if (!$table->bind ($post->getArray($this->requestArray))) {
				throw new GptranslateException($table->getError (), 'error');
			}
			
			// pre-save checks
			if (!$table->check()) {
				throw new GptranslateException($table->getError (), 'error');
			}

			// save the changes
			if (!$table->store()) {
				throw new GptranslateException($table->getError (), 'error');
			}

			// save the changes
			if (! $this->storePermissionsAsset ( $post->getArray ( $this->requestArray ) )) {
				throw new GptranslateException ( Text::_ ( 'COM_GPTRANSLATE_ERROR_STORING_PERMISSIONS' ), 'error' );
			}
		} catch (GptranslateException $e) {
			$this->setException($e);
			return false;
		} catch (\Exception $e) {
			$gptranslateException = new GptranslateException($e->getMessage(), 'error');
			$this->setException($gptranslateException);
			return false;
		}

		// Clean the cache.
		$this->cleanComponentCache('_system', 0);
		$this->cleanComponentCache('_system', 1);
		return true;
	}
	
	/**
	 * Class contructor
	 *
	 * @access public
	 * @return Object&
	 */
	public function __construct($config = array(), ?MVCFactoryInterface $factory = null) {
		parent::__construct ( $config, $factory );
	
		// App reference
		$this->appInstance = Factory::getApplication();
		$this->requestArray = &$_POST;
		
		// Joomla 4.2+
		if(method_exists($this, 'getDatabase')) {
			$this->dbInstance = $this->getDatabase();
		} else {
			$this->dbInstance = $this->getDbo();
		}
	}
}