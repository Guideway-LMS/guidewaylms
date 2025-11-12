<?php
namespace JExtstore\Component\Gptranslate\Administrator\Model;
/**
 *
 * @package GPTRANSLATE::CPANEL::administrator::components::com_gptranslate
 * @subpackage models
 * @author Joomla! Extensions Store
 * @copyright (C) 2015 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
define ( 'SERVER_REMOTE_URI', 'http://storejextensions.org/dmdocuments/updates/' );
define ( 'UPDATES_FORMAT', '.json' );
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use JExtstore\Component\Gptranslate\Administrator\Framework\Model as GptranslateModel;
use JExtstore\Component\Gptranslate\Administrator\Framework\Language\Multilang;
use JExtstore\Component\Gptranslate\Administrator\Framework\Http;
use JExtstore\Component\Gptranslate\Administrator\Framework\Exception as GptranslateException;

/**
 * Messages model responsibilities contract
 *
 * @package GPTRANSLATE::MESSAGES::administrator::components::com_gptranslate
 * @subpackage models
 * @since 1.6
 */
interface ICPanelModel {
	/**
	 * Get by remote server informations for new updates of this extension
	 *
	 * @access public
	 * @param Http $httpClient        	
	 * @return mixed An object json decoded from server if update information retrieved correctly otherwise false
	 */
	public function getUpdates(Http $httpClient);
	
	/**
	 * Delete from file system all obsolete exchanged files
	 * 
	 * @access public
	 * @return boolean
	 */
	public function purgeFileCache();
}
/**
 * CPanel model concrete implementation
 *
 * @package GPTRANSLATE::CPANEL::administrator::components::com_gptranslate
 * @subpackage models
 * @since 1.6
 */
class CpanelModel extends GptranslateModel {
	/**
	 * Counter result set
	 *
	 * @access protected
	 * @return int
	 */
	protected function buildListQueryPublishedTranslation() {
		$query = "SELECT COUNT(*)" .
				 "\n FROM #__gptranslate AS s" .
				 "\n WHERE s.published = 1";
	
		return $query;
	}
	
	/**
	 * Counter result set
	 *
	 * @access protected
	 * @return int
	 */
	protected function buildListQueryUnpublishedTranslation() {
		$query = "SELECT COUNT(*)" .
				"\n FROM #__gptranslate AS s" .
				"\n WHERE s.published = 0";
		
		return $query;
	}
	
	/**
	 * Counter result set
	 *
	 * @access protected
	 * @return int
	 */
	protected function buildListQueryGroupedTranslations() {
		$query = "SELECT s.languagetranslated, COUNT(s.id) AS numtranslations" .
				 "\n FROM `#__gptranslate` AS s" .
				 "\n GROUP BY s.languagetranslated";
		return $query;
	}
	
	/**
	 * Main get data method
	 *
	 * @access public
	 * @return array
	 */
	public function getData(): array {
		$calculatedStats = array ();
		// Build queries
		try {
			$query = $this->buildListQueryPublishedTranslation ();
			$this->dbInstance->setQuery ( $query );
			$publishedTranslations = $this->dbInstance->loadResult ();
			
			$query = $this->buildListQueryUnpublishedTranslation ();
			$this->dbInstance->setQuery ( $query );
			$unpublishedTranslations = $this->dbInstance->loadResult ();
			
			$query = $this->buildListQueryGroupedTranslations ();
			$this->dbInstance->setQuery ( $query );
			$groupedTranslations = $this->dbInstance->loadObjectList();
			
			$calculatedStats ['chart_links_canvas'] ['start'] = 0;
			$calculatedStats ['chart_links_canvas'] ['translations_short'] = $publishedTranslations;
			$calculatedStats ['chart_links_canvas'] ['utranslations_short'] = $unpublishedTranslations;
			foreach ($groupedTranslations as $singleTranslation) {
				$calculatedStats ['chart_links_canvas'] [$singleTranslation->languagetranslated] = $singleTranslation->numtranslations;
			}
			$calculatedStats ['chart_links_canvas'] ['end'] = 0;
		} catch ( GptranslateException $e ) {
			$this->app->enqueueMessage ( $e->getMessage (), $e->getExceptionLevel () );
			$calculatedStats = array ();
		} catch ( \Exception $e ) {
			$gptranslateException = new GptranslateException ( $e->getMessage (), 'error' );
			$this->app->enqueueMessage ( $gptranslateException->getMessage (), $gptranslateException->getExceptionLevel () );
			$calculatedStats = array ();
		}
		
		return $calculatedStats;
	}
	
	/**
	 * Get by remote server informations for new updates of this extension
	 *
	 * @access public
	 * @param Http $httpClient        	
	 * @return mixed An object json decoded from server if update information retrieved correctly otherwise false
	 */
	public function getUpdates(Http $httpClient) {
		// Updates server remote URI
		$option = $this->getState ( 'option', 'com_gptranslate' );
		if (! $option) {
			return false;
		}
		$url = SERVER_REMOTE_URI . $option . UPDATES_FORMAT;
		
		// Try to get informations
		try {
			$response = $httpClient->get ( $url )->body;
			if ($response) {
				$decodedUpdateInfos = json_decode ( $response );
			}
			return $decodedUpdateInfos;
		} catch ( GptranslateException $e ) {
			return false;
		} catch ( \Exception $e ) {
			return false;
		}
	}
	
	/**
	 * Class constructor
	 * 
	 * @access public
	 * @param array $config        	
	 * @return Object&
	 */
	public function __construct($config = array(), ?MVCFactoryInterface $factory = null) {
		parent::__construct ( $config, $factory );
	}
}