<?php
/**
 * @author Joomla! Extensions Store
 * @package JSPEED::plugins::system
 * @copyright (C) 2020 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
// No direct access
defined ( '_JEXEC' ) or die ();

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\SubscriberInterface;
use Joomla\Event\Event;
use Joomla\Event\DispatcherInterface;
use Joomla\CMS\Event\Finder as FinderEvent;
use Joomla\Filter\OutputFilter;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Language\Language;
use Joomla\CMS\Language\LanguageHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Plugin\Pluginhelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Router\Route;

class PlgSystemGPTranslate extends CMSPlugin implements SubscriberInterface {
	/**
	 * Ignore the Smart Search Highlight results. Treat them as same URLs
	 * 
	 * @access private
	 * @param string $url
	 * @return string|$newUrl
	 */
	private function removeHighlightParam($url) {
		// Parse the URL and its components
		$parsedUrl = parse_url($url);
		
		// If the URL has no query string, return it as is
		if (!isset($parsedUrl['query'])) {
			return $url;
		}
		
		// Parse the query string into an array
		parse_str($parsedUrl['query'], $queryParams);
		
		// If 'highlight' is in the query string, remove it
		if (isset($queryParams['highlight'])) {
			unset($queryParams['highlight']);
		}
		
		// Rebuild the query string without the 'highlight' parameter
		$newQuery = http_build_query($queryParams);
		
		// Rebuild the full URL without the 'highlight' parameter
		$newUrl = 	$parsedUrl['scheme'] . '://' . $parsedUrl['host'] .
					(isset($parsedUrl['port']) ? ':' . $parsedUrl['port'] : '') .
					$parsedUrl['path'] .
					(!empty($newQuery) ? '?' . $newQuery : '');
		
		return $newUrl;
	}
	
	/**
	 * Event to query the Google PageSpeed Insights API
	 *
	 * @param Event $event
	 *
	 * @return void
	 */
	public function processDBTranslations(Event $event) {
		$response = new \stdClass ();
		
		$cParams = ComponentHelper::getParams('com_gptranslate');
		$app = Factory::getApplication();
		$input = $app->getInput();
		$task = $input->get('task');
		
		$pageLink = $this->removeHighlightParam($input->getString('pagelink'));
		$languageOriginal = $input->get('language_original');
		$languageTranslated = $input->get('language_translated');
		$translationEngine = $input->get('translation_engine');
		$fullTranslations = $input->getRaw('translations');
		$insertDate = Date::getInstance()->toSql();
		
		$db = Factory::getContainer()->get('DatabaseDriver');
		
		// Siamo nel caso in cui le traduzioni devono essere memorizzate sul server
		if($task == 'storetranslations') {
			// Check if a translations already exists for the unique key pagelink-languageoriginal-languagetranslated and perform insert or update. Update only if unpublished record
			$query = method_exists ( $db, 'createQuery' ) ? $db->createQuery () : $db->getQuery ( true );
			$query->select($db->quoteName('id'))
				  ->from($db->quoteName('#__gptranslate'))
				  ->where($db->quoteName('pagelink') . '=' . $db->quote($pageLink))
				  ->where($db->quoteName('languageoriginal') . '=' . $db->quote($languageOriginal))
				  ->where($db->quoteName('languagetranslated') . '=' . $db->quote($languageTranslated));
			try {
				$result = $db->setQuery ( $query )->loadResult ();
				
				if($result) {
					$query = method_exists ( $db, 'createQuery' ) ? $db->createQuery () : $db->getQuery ( true );
					$query->update('#__gptranslate')
						  ->set($db->quoteName('translations') . ' = ' . $db->quote( $fullTranslations ))
						  ->set( $db->quoteName ('translate_date') . '=' . $db->quote( $insertDate ) )
						  ->where( $db->quoteName ('id') . '=' . (int)$result );
				} else {
					$query = method_exists ( $db, 'createQuery' ) ? $db->createQuery () : $db->getQuery ( true );
					$query->insert ($db->quoteName('#__gptranslate'))
						  ->columns($db->quoteName('pagelink') . ',' .
									$db->quoteName('translations') . ',' .
									$db->quoteName('languageoriginal') . ',' .
									$db->quoteName('languagetranslated') . ',' .
									$db->quoteName('published') . ',' .
							  		$db->quoteName('translate_date') . ',' .
							  		$db->quoteName('translation_engine'))
						  ->values ($db->quote($pageLink) . ',' .
									$db->quote($fullTranslations) . ',' .
									$db->quote($languageOriginal) . ',' .
									$db->quote($languageTranslated) . ',' .
									'1,' .
							  		$db->quote($insertDate) . ',' .
							  		$db->quote($translationEngine));
				}
				
				$queryResult = $db->setQuery ( $query )->execute();
				$response->result = true;
				
				// Parameter for finder integration only on insert new records
				if($cParams->get('enable_indexer', 0) && !$result) {
					$lastInsertId = $db->insertid();
					$dispatcher = Factory::getContainer()->get(DispatcherInterface::class);
					PluginHelper::importPlugin('finder', null, true, $dispatcher);
					// Trigger the onFinderBeforeSave event.
					$table = new stdClass;
					$table->id = $result ? $result : $lastInsertId;
					$table->pagelink = $pageLink;
					$table->body = $fullTranslations;
					$table->language = $languageTranslated;
					$table->start_date = $insertDate;
					$table->publish_start_date = $insertDate;
					$table->state = 1;
					$isNew = $result ? false : true;
					
					// Switch Joomla version and bind correct method and event system
					if(version_compare(JVERSION, '5', '>=')) {
						$dispatcher->dispatch('onFinderAfterSave', new FinderEvent\AfterSaveEvent('onFinderAfterSave', [
								'context' => 'com_gptranslate.translations',
								'subject' => $table,
								'isNew'   => $isNew
						]));
					} else {
						$app->triggerEvent('onFinderAfterSave', ['com_gptranslate.translations', $table, $isNew]);
					}
				}
			} catch (\Exception $e) {
				$response->result = false;
				$response->exception = $e->getMessage();
			}
		} elseif ($task == 'gettranslations') {
			// Always perform a new realtime translation
			if($cParams->get('realtime_translations', 0)) {
				$response->result = false;
			} else {
				$query = method_exists ( $db, 'createQuery' ) ? $db->createQuery () : $db->getQuery ( true );
				$query->select($db->quoteName('translations'))
					  ->from($db->quoteName('#__gptranslate'))
					  ->where($db->quoteName('pagelink') . '=' . $db->quote($pageLink))
					  ->where($db->quoteName('languageoriginal') . '=' . $db->quote($languageOriginal))
					  ->where($db->quoteName('languagetranslated') . '=' . $db->quote($languageTranslated))
					  ->where($db->quoteName('published') . '= 1');
				try {
					$result = $db->setQuery ( $query )->loadResult ();

					if($result) {
						$response->result = true;
						$response->translations = json_decode($result);
					} else {
						$response->result = false;
					}
				} catch ( \Exception $e ) {
					$response->result = false;
					$response->exception = $e->getMessage();
				}
			}
		}
		
		$event->setArgument ( 'result', $response );
	}

	/** Manage the Joomla updater based on the user license
	 *
	 * @access public
	 * @return void
	 */
	public function gptranslateUpdateInstall(Event $event) {
		// subparams: &$url, &$headers
		$arguments = $event->getArguments();
		$url = isset($arguments[0]) ? $event->getArgument(0) : $event->getArgument('url');
		$headers = isset($arguments[1]) ? $event->getArgument(1) : $event->getArgument('headers');
		
		$uri 	= Uri::getInstance($url);
		$parts 	= explode('/', $uri->getPath());
		if ($uri->getHost() == 'storejextensions.org' && in_array('com_gptranslate.zip', $parts)) {
			// Init as false unless the license is valid
			$validUpdate = false;
			
			// Load component language
			$appInstance = Factory::getApplication();
			$jLang = $appInstance->getLanguage();
			$jLang->load('com_gptranslate', JPATH_BASE . '/components/com_gptranslate', 'en-GB', true, true);
			if($jLang->getTag() != 'en-GB') {
				$jLang->load('com_gptranslate', JPATH_BASE, null, true, false);
				$jLang->load('com_gptranslate', JPATH_BASE . '/components/com_gptranslate', null, true, false);
			}
			
			// Email license validation API call and &$url building construction override
			$cParams = ComponentHelper::getParams('com_gptranslate');
			$registrationEmail = $cParams->get('registration_email', null);
			
			// License
			if($registrationEmail) {
				$prodCode = 'gptranslate';
				$cdFuncUsed = 'str_' . 'ro' . 't' . '13';
				
				// Retrieve license informations from the remote REST API
				$apiResponse = null;
				$apiEndpoint = $cdFuncUsed('uggcf' . '://' . 'fgberwrkgrafvbaf' . '.bet') . "/option,com_easycommerce/action,licenseCode/email,$registrationEmail/productcode,$prodCode";
				if (function_exists('curl_init')){
					$ch = curl_init();
					curl_setopt($ch, CURLOPT_URL, $apiEndpoint);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
					$apiResponse = curl_exec($ch);
					curl_close($ch);
				}
				$objectApiResponse = json_decode($apiResponse);
				
				if(!is_object($objectApiResponse)) {
					// Message user about error retrieving license informations
					$appInstance->enqueueMessage(Text::_('COM_GPTRANSLATE_ERROR_RETRIEVING_LICENSE_INFO'));
				} else {
					if(!$objectApiResponse->success) {
						switch ($objectApiResponse->reason) {
							// Message user about the reason the license is not valid
							case 'nomatchingcode':
								$appInstance->enqueueMessage(Text::_('COM_GPTRANSLATE_LICENSE_NOMATCHING'));
								break;
								
							case 'expired':
								// Message user about license expired on $objectApiResponse->expireon
								$appInstance->enqueueMessage(Text::sprintf('COM_GPTRANSLATE_LICENSE_EXPIRED', $objectApiResponse->expireon));
								break;
						}
						
					}
					
					// Valid license found, builds the URL update link and message user about the license expiration validity
					if($objectApiResponse->success) {
						$url = $cdFuncUsed('uggc' . '://' . 'fgberwrkgrafvbaf' . '.bet' . '/TCGENAFYNGR1401TFPEvtmu0043568423ctlgre19td1ozba09dj9.ugzy');
						// Joomla 5+ native BeforePackageDownloadEvent
						if(method_exists($event, 'updateUrl')) {
							$event->updateUrl($url);
						} else {
							// Fallback to generic Event up to Joomla 4
							$event->setArgument(0, $url);
						}
						
						$validUpdate = true;
						$appInstance->enqueueMessage(Text::sprintf('COM_GPTRANSLATE_EXTENSION_UPDATED_SUCCESS', $objectApiResponse->expireon));
					}
				}
			} else {
				// Message user about missing email license code
				$appInstance->enqueueMessage(Text::sprintf('COM_GPTRANSLATE_MISSING_REGISTRATION_EMAIL_ADDRESS', OutputFilter::ampReplace('index.php?option=com_gptranslate&task=config.display#_licensepreferences')));
			}
			
			if(!$validUpdate) {
				$appInstance->enqueueMessage(Text::_('COM_GPTRANSLATE_UPDATER_STANDARD_ADVISE'), 'notice');
			}
		}
	}
	
	/**
	 * Event to manipulate the menu item dashboard in backend
	 *
	 * @param Event $event
	 * @subparam   array  &$policy  The privacy policy status data, passed by reference, with keys "published" and "editLink"
	 *
	 * @return  void
	 */
	public function processMenuItemsDashboard(Event $event) {
		// subparams: $context, $items
		$arguments = $event->getArguments();
		$context = isset($arguments[0]) ? $event->getArgument(0) : $event->getArgument('context');
		$items = isset($arguments[1]) ? $event->getArgument(1) : $event->getArgument('subject');
		
		if(!empty($items) && $context == 'administrator.module.mod_submenu') {
			foreach ($items as $item) {
				if($item->element == 'com_gptranslate') {
					$item->img = Uri::base() . 'components/com_gptranslate/images/gptranslate-16x16.png';
					$item->title = 'COM_GPTRANSLATE_DASHBOARD_TITLE';
				}
			}
		}
		
		// Kill com_joomlaupdate informations about extensions missing updater info, leave only main one
		$appInstance = Factory::getApplication();
		$document = $appInstance->getDocument();
		if(!$appInstance->get('jextstore_joomlaupdate_script') && $appInstance->getInput()->get('option') == 'com_joomlaupdate' && !$appInstance->getInput()->get('view') && !$appInstance->getInput()->get('task')) {
			$document->getWebAssetManager()->addInlineScript ("
				window.addEventListener('DOMContentLoaded', function(e) {
					if(document.querySelector('#preupdatecheck')) {
						var jextensionsIntervalCount = 0;
						var jextensionsIntervalTimer = setInterval(function() {
						    [].slice.call(document.querySelectorAll('#compatibilityTable1 tbody tr th.exname')).forEach(function(th) {
						        let txt = th.innerText;
						        if (txt && txt.toLowerCase().match(/jsitemap|gdpr|gptranslate|jpagebuilder|responsivizer|jchatsocial|jcomment|jshortcodes|jrealtime|jspeed|jredirects|vsutility|visualstyles|visual\sstyles|instant\sfacebook\slogin|instantpaypal|screen\sreader|jspeed|jamp/i)) {
						            th.parentElement.style.display = 'none';
						            th.parentElement.classList.remove('error');
									th.parentElement.classList.add('jextcompatible');
						        }
						    });
							[].slice.call(document.querySelectorAll('#compatibilityTable2 tbody tr th.exname')).forEach(function(th) {
						        let txt = th.innerText;
						        if (txt && txt.toLowerCase().match(/jsitemap|gdpr|gptranslate|jpagebuilder|responsivizer|jchatsocial|jcomment|jshortcodes|jrealtime|jspeed|jredirects|vsutility|visualstyles|visual\sstyles|instant\sfacebook\slogin|instantpaypal|screen\sreader|jspeed|jamp/i)) {
									th.parentElement.classList.remove('error');
									th.parentElement.classList.add('jextcompatible');
						            let smallDiv = th.querySelector(':scope div.small');
									if(smallDiv) {
										smallDiv.style.display = 'none';
									}
						        }
						    });
							if (document.querySelectorAll('#compatibilityTable0 tbody tr').length == 0 &&
								document.querySelectorAll('#compatibilityTable1 tbody tr:not(.jextcompatible)').length == 0 &&
								document.querySelectorAll('#compatibilityTable2 tbody tr:not(.jextcompatible)').length == 0) {
						        [].slice.call(document.querySelectorAll('#preupdatecheckbox, #preupdateCheckCompleteProblems')).forEach(function(element) {
						            element.style.display = 'none';
						        });
								if(document.querySelector('#noncoreplugins')) {
									document.querySelector('#noncoreplugins').checked = true;
								}
								if(document.querySelector('button.submitupdate')) {
							        document.querySelector('button.submitupdate').disabled = false;
							        document.querySelector('button.submitupdate').classList.remove('disabled');
								}
								if(document.querySelector('#joomlaupdate-precheck-extensions-tab span.fa')) {
									let tabIcon = document.querySelector('#joomlaupdate-precheck-extensions-tab span.fa');
									tabIcon.classList.remove('fa-times');
									tabIcon.classList.remove('text-danger');
									tabIcon.classList.remove('fa-exclamation-triangle');
									tabIcon.classList.remove('text-warning');
									tabIcon.classList.add('fa-check');
									tabIcon.classList.add('text-success');
								}
						    };
					
							if (document.querySelectorAll('#compatibilityTable0 tbody tr').length == 0) {
								if(document.querySelectorAll('#compatibilityTable1 tbody tr:not(.jextcompatible)').length == 0) {
									let compatibilityTable1 = document.querySelector('#compatibilityTable1');
									if(compatibilityTable1) {
										compatibilityTable1.style.display = 'none';
									}
								}
								clearInterval(jextensionsIntervalTimer);
							}
					
						    jextensionsIntervalCount++;
						}, 1000);
					};
				});");
			$appInstance->set('jextstore_joomlaupdate_script', true);
		}
	}
	
	/**
	 * Hook for the auto Pingomatic third party extensions that have not its own
	 * route helper and work with the universal JSitemap route helper framework
	 *
	 * @param Event $event
	 * @access public
	 * @return boolean
	 */
	public function configManage(Event $event) {
		$app = Factory::getApplication();
		
		// Redirect to the component configuration if the Joomla global configuration is requested instead
		$dispatchedComponent = $app->getInput()->get ( 'option' );
		$dispatchedView = $app->getInput()->get ( 'view' );
		$componentConfig = $app->getInput()->get ( 'component' );
		if($dispatchedComponent == 'com_config' && $dispatchedView == 'component' && $componentConfig == 'com_gptranslate') {
			$app->redirect(Route::_('index.php?option=com_gptranslate&task=config.display', false));
			return;
		}
	}
	
	/**
	 * Returns an array of events this subscriber will listen to.
	 *
	 * @return array
	 *
	 * @since 4.0.0
	 */
	public static function getSubscribedEvents(): array {
		return [ 
				'onAjaxGptranslate' => 'processDBTranslations',
				'onPreprocessMenuItems' => 'processMenuItemsDashboard',
				'onAfterRoute' => 'configManage',
				'onInstallerBeforePackageDownload' => 'gptranslateUpdateInstall'
		];
	}
}
