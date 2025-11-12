<?php
namespace JExtstore\Component\Gptranslate\Administrator\Model;
/**
 *
 * @package GPTRANSLATE::AJAXSERVER::administrator::components::com_gptranslate
 * @subpackage models
 * @author Joomla! Extensions Store
 * @copyright (C) 2024 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\String\StringHelper;
use Joomla\Event\Event;
use JExtstore\Component\Gptranslate\Administrator\Framework\Model as GptranslateModel;
use JExtstore\Component\Gptranslate\Administrator\Framework\Exception as GptranslateException;

/**
 * Ajax Server model responsibilities
 *
 * @package GPTRANSLATE::AJAXSERVER::administrator::components::com_gptranslate
 * @subpackage models
 * @since 2.4
 */
interface IAjaxserverModel {
	public function loadAjaxEntity($id, $param, $DIModels);
}

/**
 * Classe che gestisce il recupero dei dati per il POST HTTP
 *
 * @package GPTRANSLATE::AJAXSERVER::administrator::components::com_gptranslate
 * @subpackage models
 * @since 2.4
 */
class AjaxserverModel extends GptranslateModel implements IAjaxserverModel {
	/**
	 * MVCFactory instance
	 *
	 * @access private
	 * @var Object
	 */
	private $mvcFactoryInstance;

	/**
	 * Clean the cache
	 * @param   string   $group      The cache group
	 * @param   integer  $client_id  The ID of the client
	 * @return  void
	 * @since   11.1
	 */
	private function cleanComponentCache($group = null, $client_id = 0) {
		// Initialise variables;
		$conf = $this->app->getConfig();
		
		$options = array(
				'defaultgroup' => ($group) ? $group : $this->app->getInput()->get('option'),
				'cachebase' => ($client_id) ? JPATH_ADMINISTRATOR . '/cache' : $conf->get('cache_path', JPATH_CACHE));
		
		$cache = Factory::getContainer()->get(\Joomla\CMS\Cache\CacheControllerFactoryInterface::class)->createCacheController( 'callback', $options );
		$cache->clean();
		
		// Trigger the onContentCleanCache event.
		$this->app->getDispatcher()->dispatch('onContentCleanCache', new Event('onContentCleanCache', $options));
	}

	/**
	 * Auto configure received cookies/domains to the matching categories, also creating description records
	 *
	 * @access private
	 * @param Object[] $additionalModels
	 *        	Array for additional injected models type hinted by interface
	 * @return Object
	 */
	private function syncTranslation($params, $additionalModels = null) {
		// Response JSON object
		$response = new \stdClass ();
		$cParams = $this->getComponentParams ();

		try {
			// Loop over all records for the same target language, json_decode all translations, loop over each single translation and if the original matches then update translated and save the record
			$translationsModel = $additionalModels['Translations'];
			$translations = $translationsModel->getData();
			$table = $this->getTable ( 'Translations', 'Administrator' );
			foreach ( $translations as $translation ) {
				if($translation->languagetranslated == $params->languagetranslated) {
					$translationsObject = json_decode ( $translation->translations );
					foreach ( $translationsObject as $original => $translated ) {
						// Found a match
						if($original == $params->original) {
							$table->load($translation->id);
							$translated = $params->translated;
							$table->translations[$original] = $translated;
							$table->translations = json_encode($table->translations);
							$table->store();
						}
					}
				}
			}
			
			// All completed successfully
			$response->result = true;
		} catch ( GptranslateException $e ) {
			$response->result = false;
			$response->exception_message = $e->getMessage ();
			$response->errorlevel = $e->getExceptionLevel ();
			return $response;
		} catch ( \Exception $e ) {
			$gptranslateException = new GptranslateException ( Text::sprintf ( 'COM_GPTRANSLATE_SYNC_ERROR', $e->getMessage () ), 'danger' );
			$response->result = false;
			$response->exception_message = $gptranslateException->getMessage ();
			$response->errorlevel = $gptranslateException->getExceptionLevel ();
			return $response;
		}
		
		// Clean the cache.
		$this->cleanComponentCache('_system', 0);
		$this->cleanComponentCache('_system', 1);

		return $response;
	}

	/**
	 * Mimic an entities list, as ajax calls arrive are redirected to loadEntity public responsibility to get handled
	 * by specific subtask.
	 * Responses are returned to controller and encoded from view over HTTP to JS client
	 *
	 * @access public
	 * @param string $id
	 *        	Rappresenta l'op da eseguire tra le private properties
	 * @param mixed $param
	 *        	Parametri da passare al private handler
	 * @param Object[]& $DIModels
	 * @return Object& $utenteSelezionato
	 */
	public function loadAjaxEntity($id, $param, $DIModels) {
		// Delega la private functions delegata dalla richiesta sulla entity
		$response = $this->$id ( $param, $DIModels );

		return $response;
	}

	/**
	 * Class constructor
	 *
	 * @access public
	 * @param $config array
	 * @return Object&
	 */
	public function __construct($config = array (), ?MVCFactoryInterface $factory = null) {
		parent::__construct ( $config, $factory );

		$this->mvcFactoryInstance = $factory;
	}
}