<?php
namespace JExtstore\Component\Gptranslate\Administrator\Model;
/**
 * @package GPTRANSLATE::LINKS::administrator::components::com_gptranslate
 * @subpackage models
 * @author Joomla! Extensions Store
 * @copyright (C) 2015 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Event\Finder as FinderEvent;
use Joomla\Event\DispatcherInterface;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\String\StringHelper;
use JExtstore\Component\Gptranslate\Administrator\Framework\Model as GptranslateModel;
use JExtstore\Component\Gptranslate\Administrator\Framework\Html\Languages as GptranslateHtmlLanguages;
use JExtstore\Component\Gptranslate\Administrator\Framework\Helpers\Html as GptranslateHelpersHtml;
use JExtstore\Component\Gptranslate\Administrator\Framework\Exception as GptranslateException;

/**
 * Links model concrete implementation <<testable_behavior>>
 *
 * @package GPTRANSLATE::LINKS::administrator::components::com_gptranslate
 * @subpackage models
 * * @since 1.0
 */
class TranslationsModel extends GptranslateModel {
	/**
	 * Build list entities query
	 * 
	 * @access protected
	 * @return string
	 */
	protected function buildListQuery() {
		// WHERE
		$where = array ();
		$whereString = null;
		$orderString = null;

		// STATE FILTER
		if ($filter_state = $this->state->get ( 'state' )) {
			if ($filter_state == 'U') {
				$where [] = 's.published = 0';
			} elseif ($filter_state == 'P') {
				$where [] = 's.published = 1';
			}
		}
		
		// TEXT FILTER
		if ($this->state->get ( 'searchword' )) {
			$where [] = "(s.pagelink LIKE " . $this->dbInstance->quote("%" . $this->state->get ( 'searchword' ) . "%") . ")";
		}
		
		// LANGUAGE FILTER
		if ($this->state->get ( 'languagegptranslate' )) {
			$where [] = "(s.languagetranslated = " . $this->dbInstance->quote($this->state->get ( 'languagegptranslate' )) . ")";
		}
		
		// TRANSLATION ENGINE
		if ($this->state->get ( 'translationengine' )) {
			$where [] = "(s.translation_engine = " . $this->dbInstance->quote($this->state->get ( 'translationengine' )) . ")";
		}
		
		if (count ( $where )) {
			$whereString = "\n WHERE " . implode ( "\n AND ", $where );
		}
		
		// ORDERBY
		if ($this->state->get ( 'order' )) {
			$orderString = "\n ORDER BY " . $this->state->get ( 'order' ) . " ";
		}
		
		// ORDERDIR
		if ($this->state->get ( 'order_dir' )) {
			$orderString .= $this->state->get ( 'order_dir' );
		}
		
		$query = "SELECT s.*" .
				 "\n FROM #__gptranslate AS s" .
				 "\n LEFT JOIN #__users AS u" .
				 "\n ON s.checked_out = u.id" .
				 $whereString .
				 $orderString;
		return $query;
	}

	/**
	 * Main get data methods
	 * 
	 * @access public
	 * @return Object[]
	 */
	public function getData(): array {
		// Build query
		$query = $this->buildListQuery ();
		try {
			$dbQuery = method_exists ( $this->dbInstance, 'createQuery' ) ? $this->dbInstance->createQuery () : $this->dbInstance->getQuery ( true );
			$dbQuery->setQuery ( $query )->setLimit ( $this->getState ( 'limit' ), $this->getState ( 'limitstart' ) );
			$this->dbInstance->setQuery ( $dbQuery );
			$result = $this->dbInstance->loadObjectList ();
		} catch (GptranslateException $e) {
			$this->app->enqueueMessage($e->getMessage(), $e->getExceptionLevel());
			$result = array();
		} catch (\Exception $e) {
			$gptranslateException = new GptranslateException($e->getMessage(), 'error');
			$this->app->enqueueMessage($gptranslateException->getMessage(), $gptranslateException->getExceptionLevel());
			$result = array();
		}
		return $result;
	}
	
	/**
	 * Storing entity by ORM table
	 *
	 * @access public
	 * @param bool $updateNulls
	 * @return mixed
	 */
	public function storeEntity($updateNulls = true) {
		$table = parent::storeEntity($updateNulls);
		
		// Parameter for finder integration
		if($this->getComponentParams()->get('enable_indexer', 0)) {
			$originalBackendLanguageTag = $this->app->getLanguage()->getTag();
			$dispatcher = Factory::getContainer()->get(DispatcherInterface::class);
			PluginHelper::importPlugin('finder', null, true, $dispatcher);
			// Trigger the onFinderBeforeSave event.
			if(version_compare(JVERSION, '5', '>=')) {
				$dispatcher->dispatch('onFinderAfterSave', new FinderEvent\AfterSaveEvent('onFinderAfterSave', [
						'context' => 'com_gptranslate.translations',
						'subject' => $table,
						'isNew'   => false
				]));
			} else {
				$this->app->triggerEvent('onFinderAfterSave', ['com_gptranslate.translations', $table, false]);
			}
			$jLang = Factory::getContainer()->get(\Joomla\CMS\Language\LanguageFactoryInterface::class)->createLanguage($originalBackendLanguageTag, false);
			Factory::$language = $jLang;
		}
		
		return $table;
	}
	
	/**
	 * Delete entity
	 *
	 * @param array $ids
	 * @access public
	 * @return bool
	 */
	public function deleteEntity($ids): bool {
		$table = $this->getTable ( $this->getName (), 'Administrator' );
		
		// Ciclo su ogni entity da cancellare
		if (is_array ( $ids ) && count ( $ids )) {
			foreach ( $ids as $id ) {
				try {
					// Parameter for finder integration
					if($this->getComponentParams()->get('enable_indexer', 0)) {
						$dispatcher = Factory::getContainer()->get(DispatcherInterface::class);
						PluginHelper::importPlugin('finder', null, true, $dispatcher);
						$table->load($id);
						$url = '/' . ltrim(StringHelper::str_ireplace(Uri::root(false), '', $table->pagelink), '/');
						// Get the link ids for the content items.
						$query = method_exists ( $this->dbInstance, 'createQuery' ) ? $this->dbInstance->createQuery () : $this->dbInstance->getQuery ( true );
						$query->select($this->dbInstance->quoteName('link_id'))
							  ->from($this->dbInstance->quoteName('#__finder_links'))
							  ->where($this->dbInstance->quoteName('route') . ' = ' . $this->dbInstance->quote($url));
						$this->dbInstance->setQuery($query);
						$item = $this->dbInstance->loadObject();
						// Trigger the onFinderBeforeSave event.
						if($item) {
							if(version_compare(JVERSION, '5', '>=')) {
								$dispatcher->dispatch('onFinderAfterDelete', new FinderEvent\AfterDeleteEvent('onFinderAfterDelete', [
										'context' => 'com_gptranslate.translations',
										'subject' => $item
								]));
							} else {
								$this->app->triggerEvent('onFinderAfterDelete', ['com_gptranslate.translations', $item]);
							}
						}
					}
					
					if (! $table->delete ( $id )) {
						throw new GptranslateException ( $table->getException (), 'error' );
					}
					// Only if table supports ordering
					if (property_exists ( $table, 'ordering' )) {
						$table->reorder ();
					}
				} catch ( GptranslateException $e ) {
					$this->setException ( $e );
					return false;
				} catch ( \Exception $e ) {
					$gptranslateException = new GptranslateException ( $e->getMessage (), 'error' );
					$this->setException ( $gptranslateException );
					return false;
				}
				
				// If integration with ActionLogs
				if($this->getComponentParams()->get('actionlogs_integration', 0)) {
					$action = Text::_('COM_GPTRANSLATE_ACTIONLOGS_DELETED');
					$contextClass = (new \ReflectionClass($this))->getShortName();
					$context = StringHelper::str_ireplace('model', '', StringHelper::strtolower($contextClass));
					$this->storeActionLog($action, $context, Text::sprintf('COM_GPTRANSLATE_ACTIONLOGS_GENERIC_STORE_ACTION', $id, $table->getTableName()), 'index.php?option=com_gptranslate&task=' . $context . '.editEntity&cid[]=' . $id, $id);
				}
			}
		}
		
		return true;
	}
	
	/**
	 * Update metainfo records to https domain
	 *
	 * @access public
	 * @return boolean
	 */
	public function domainsMigrate($currentDomain, $newDomain) {
		try {
			$query = "UPDATE " . $this->dbInstance->quoteName('#__gptranslate') .
					 "\n SET " . $this->dbInstance->quoteName('pagelink') . " = " .
					 "REPLACE(" . $this->dbInstance->quoteName('pagelink') . "," . $this->dbInstance->quote($currentDomain) . "," . $this->dbInstance->quote($newDomain) . ")";
			$this->dbInstance->setQuery($query);
			$this->dbInstance->execute();
		} catch (GptranslateException $e) {
			$this->setException($e);
			return false;
		} catch (\Exception $e) {
			$gptranslateException = new GptranslateException($e->getMessage(), 'error');
			$this->setException($gptranslateException);
			return false;
		}
		return true;
	}
	
	/**
	 * Return select lists used as filter for listEntities
	 *
	 * @access public
	 * @return array
	 */
	public function getFilters(): array {
		$filters = [];
		
		// Filter by redirect state
		$filterState = [];
		$filterState[] = HTMLHelper::_('select.option', null, Text::_('COM_GPTRANSLATE_TRANSLATION_ALL'));
		$filterState[] = HTMLHelper::_('select.option', 'P', Text::_('COM_GPTRANSLATE_TRANSLATION_PUBLISHED'));
		$filterState[] = HTMLHelper::_('select.option', 'U', Text::_('COM_GPTRANSLATE_TRANSLATION_UNPUBLISHED'));
		
		$filters ['state'] = HTMLHelper::_ ( 'select.genericlist', $filterState, 'filter_state', 'onchange="Joomla.submitform();"', 'value', 'text', $this->getState ( 'state' ));
		
		$languageOptions = GptranslateHtmlLanguages::getAvailableLanguageOptions(true);
		$filters ['languages'] = HTMLHelper::_ ( 'select.genericlist', $languageOptions, 'languagegptranslate', 'onchange="Joomla.submitform();" class="form-select"', 'value', 'text', $this->getState ( 'languagegptranslate' ) );
		
		// Filter by redirect state
		$translationEngine = [];
		$translationEngine[] = HTMLHelper::_('select.option', null, Text::_('COM_GPTRANSLATE_CHATGPT_TRANSLATION_ENGINE'));
		$translationEngine[] = HTMLHelper::_('select.option', 'gtranslate', Text::_('COM_GPTRANSLATE_CHATGPT_TRANSLATION_ENGINE_GTRANSLATE'));
		$translationEngine[] = HTMLHelper::_('select.option', 'chatgpt', Text::_('COM_GPTRANSLATE_CHATGPT_TRANSLATION_ENGINE_CHATGPT'));
		
		$filters ['translationengine'] = HTMLHelper::_ ( 'select.genericlist', $translationEngine, 'translationengine', 'onchange="Joomla.submitform();"', 'value', 'text', $this->getState ( 'translationengine' ));
		
		return $filters;
	}
	
	/**
	 * Return select lists used as filter for editEntity
	 *
	 * @access public
	 * @param Object $record
	 * @return array
	 */
	public function getLists($record = null): array {
		$lists = parent::getLists($record);

		return $lists;
	}
}