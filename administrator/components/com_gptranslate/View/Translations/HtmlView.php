<?php
namespace JExtstore\Component\Gptranslate\Administrator\View\Translations;
/**
 * @package GPTRANSLATE::LINKS::administrator::components::com_gptranslate
 * @subpackage views
 * @subpackage links
 * @author Joomla! Extensions Store
 * @copyright (C) 2015 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html  
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Pagination\Pagination;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Filter\OutputFilter;
use Joomla\String\StringHelper;
use JExtstore\Component\Gptranslate\Administrator\Framework\Helpers\Toolbars as ToolbarHelper;
use JExtstore\Component\Gptranslate\Administrator\Framework\View as GptranslateView;

/**
 * @package GPTRANSLATE::LINKS::administrator::components::com_gptranslate
 * @subpackage views
 * @subpackage links
 * * @since 1.0
 */
class HtmlView extends GptranslateView {
	// Template view variables
	protected $pagination;
	protected $searchword;
	protected $urlRewriting;
	protected $orders;
	protected $lists;
	protected $items;
	protected $isMultiLanguage;
	protected $urischeme;
	protected $componentParams;
	protected $record;
	protected $joomlaConfig;
	
	/**
	 * Add the page title and toolbar.
	 *
	 * @since	1.6
	 */
	protected function addEditEntityToolbar() {
		$user		= $this->app->getIdentity();
		$userId		= $user->id;
		$isNew		= ($this->record->id == 0);
		$checkedOut	= !($this->record->checked_out == 0 || $this->record->checked_out == $userId);
		$toolbarHelperTitle = $isNew ? 'COM_GPTRANSLATE_LINKS_NEW' : 'COM_GPTRANSLATE_LINKS_EDIT';
		
		ToolbarHelper::title( Text::_( $toolbarHelperTitle ), 'gptranslate' );
	
		if ($isNew)  {
			// For new records, check the create permission.
			if ($isNew && ($user->authorise('core.create', 'com_gptranslate'))) {
				ToolbarHelper::apply( 'translations.applyEntity', 'JAPPLY');
				ToolbarHelper::save( 'translations.saveEntity', 'JSAVE');
			}
		} else {
			// Can't save the record if it's checked out.
			if (!$checkedOut) {
				// Since it's an existing record, check the edit permission, or fall back to edit own if the owner.
				if ($user->authorise('core.edit', 'com_gptranslate')) {
					ToolbarHelper::apply( 'translations.applyEntity', 'JAPPLY');
					ToolbarHelper::save( 'translations.saveEntity', 'JSAVE');
				}
			}
		}
			
		ToolbarHelper::custom('translations.cancelEntity', 'cancel', 'cancel', 'JCANCEL', false);
	}
	
	
	/**
	 * Add the page title and toolbar.
	 *
	 * @since	1.6
	 */
	protected function addDisplayToolbar() {
		$user = $this->app->getIdentity();
		ToolbarHelper::title( Text::_('COM_GPTRANSLATE_LINKS' ), 'gptranslate' );
	
		if ($user->authorise('core.edit', 'com_gptranslate')) {
			ToolbarHelper::editList('translations.editentity', 'COM_GPTRANSLATE_EDIT_LINK');
		}
	
		if ($user->authorise('core.delete', 'com_gptranslate') && $user->authorise('core.edit', 'com_gptranslate')) {
			ToolbarHelper::deleteList('COM_GPTRANSLATE_DELETE_ENTITY', 'translations.deleteentity');
		}
		
		if ($this->user->authorise('core.create', 'com_gptranslate') && $this->user->authorise('core.create', 'com_gptranslate')) {
			$toolbar = Toolbar::getInstance();
			
			$dropdown = $toolbar->dropdownButton('importexport-group')
								->text('COM_GPTRANSLATE_EXPORT_TRANSLATIONS_BTNS')
								->toggleSplit(false)
								->icon('icon-ellipsis-h')
								->buttonClass('btn btn-action')
								->listCheck(false);
			$childBar = $dropdown->getChildToolbar();
			$childBar->standardButton('download', 'COM_GPTRANSLATE_EXPORT_TRANSLATIONS', 'translations.exportEntities')->listCheck(false);
			$childBar->standardButton('upload', 'COM_GPTRANSLATE_IMPORT_TRANSLATIONS', 'translations.importEntities')->listCheck(false);

			$dropdown = $toolbar->dropdownButton('status-group')
								->text('COM_GPTRANSLATE_MIGRATE_BTNS')
								->toggleSplit(false)
								->icon('icon-ellipsis-h')
								->buttonClass('btn btn-action')
								->listCheck(false);
			$childBar = $dropdown->getChildToolbar();
			
			$childBar->standardButton('refresh', 'COM_GPTRANSLATE_MIGRATE_TRANSLATIONS', 'translations.migrateEntities')->listCheck(false);
		}

		ToolbarHelper::custom('cpanel.display', 'home', 'home', 'COM_GPTRANSLATE_CPANEL', false);
	}
	
	/**
	 * Default display listEntities
	 *        	
	 * @access public
	 * @param string $tpl
	 * @return void
	 */
	public function display($tpl = 'list') {
		// Get main records
		$model = $this->getModel();
		$rows = $model->getData();
		$total = $model->getTotal();
		$lists = $model->getFilters();
		
		$doc = $this->app->getDocument();
		$this->loadJQuery($doc);
		$this->loadBootstrap($doc);
		
		$doc->getWebAssetManager()->registerAndUseScript ('gptranslate.filesources', 'administrator/components/com_gptranslate/js/filesources.js', [], [], ['jquery'] );
		$doc->getWebAssetManager()->registerAndUseScript ('gptranslate.migratemeta', 'administrator/components/com_gptranslate/js/migratetranslations.js', [], [], ['jquery'] );
		
		$doc->getWebAssetManager()->addInlineStyle('@media (max-width: 1280px) and (min-width: 768px) { body.admin.com_gptranslate { min-width: 1280px; }}');
		$doc->getWebAssetManager()->addInlineStyle('@media (max-width: 640px){ body.admin.com_gptranslate { min-width: 640px; }}');
		
		// Inject js translations
		$translations = array (
				'COM_GPTRANSLATE_REQUIRED',
				'COM_GPTRANSLATE_PICKFILE',
				'COM_GPTRANSLATE_STARTIMPORT',
				'COM_GPTRANSLATE_CANCELIMPORT',
				'COM_GPTRANSLATE_MIGRATE_META_PREVIOUS_DOMAIN',
				'COM_GPTRANSLATE_MIGRATE_META_NEW_DOMAIN',
				'COM_GPTRANSLATE_MIGRATE_META_CONFIRM',
				'COM_GPTRANSLATE_MIGRATE_META_CANCEL',
				'COM_GPTRANSLATE_INVALID_URL'
		);
		$this->injectJsTranslations($translations, $doc);
		
		$orders = array ();
		$orders ['order'] = $this->getModel ()->getState ( 'order' );
		$orders ['order_Dir'] = $this->getModel ()->getState ( 'order_dir' );
		// Pagination view object model state populated
		$pagination = new Pagination ( $total, $this->getModel ()->getState ( 'limitstart' ), $this->getModel ()->getState ( 'limit' ) );
		
		$this->user = $this->app->getIdentity ();
		$this->pagination = $pagination;
		$this->searchword = $this->getModel ()->getState ( 'searchword' );
		$this->option = $this->getModel ()->getState ( 'option' );
		$this->urlRewriting = $this->app->get('sef_rewrite', 0) ? '' : 'index.php/';
		$this->orders = $orders;
		$this->lists = $lists;
		$this->items = $rows;
		$this->isMultiLanguage = PluginHelper::isEnabled('system', 'languagefilter');
		$this->componentParams = $this->getModel()->getComponentParams();
		
		$joomlaConfig = $this->app->getConfig();
		$this->joomlaConfig = $this->user->getParam ( 'timezone', $joomlaConfig->get ( 'offset' ) );
		$this->joomlaConfigLanguage = StringHelper::str_ireplace('-', '_', $joomlaConfig->get('language'));
		
		// Aggiunta toolbar
		$this->addDisplayToolbar();
			
		parent::display ( $tpl );
	}
	
	/**
	 * Edit entity view
	 *
	 * @access public
	 * @param Object& $row the item to edit
	 * @return void
	 */
	public function editEntity(&$row) {
		// Sanitize HTML Object2Form
		OutputFilter::objectHTMLSafe( $row );
		
		// Detect uri scheme
		$instance = Uri::getInstance();
		$this->urischeme = $instance->isSSL() ? 'https' : 'http';
		
		// Load JS Client App dependencies
		$doc = $this->app->getDocument();
		$base = Uri::root();
		$this->loadJQuery($doc);
		$this->loadJQueryUI($doc);
		$this->loadBootstrap($doc);
		$this->loadValidation($doc);
		$doc->getWebAssetManager()->addInlineStyle('@media (max-width: 1280px){ body.admin.com_gptranslate { min-width: 1280px; }}');
		
		// Inject js translations
		$translations = array(
							'COM_GPTRANSLATE_ORIGINAL_TEXT',
							'COM_GPTRANSLATE_TRANSLATED_TEXT',
							'COM_GPTRANSLATE_DELETE',
							'COM_GPTRANSLATE_SYNC',
							'COM_GPTRANSLATE_SYNC_TITLE',
							'COM_GPTRANSLATE_SYNC_DESC',
							'COM_GPTRANSLATE_SYNC_COMPLETED',
							'COM_GPTRANSLATE_SYNC_ERROR',
							'COM_GPTRANSLATE_VALIDATION_ERROR'	);
		$this->injectJsTranslations($translations, $doc);
		
		// Load specific JS App
		$doc->getWebAssetManager()->addInlineScript("
						Joomla.submitbutton = function(pressbutton) {
							if(!jQuery.fn.validation) {
								jQuery.extend(jQuery.fn, gptranslatejQueryBackup.fn);
							}

							jQuery('#adminForm').validation();
							
							if (pressbutton == 'translations.cancelEntity') {
								jQuery('#adminForm').off();
								Joomla.submitform( pressbutton );
								return true;
							}
				
							if(jQuery('#adminForm').validate()) {
								Joomla.submitform( pressbutton );
								return true;
							}
							return false;
						};
					");
		
		$lists = $this->getModel()->getLists($row);
		$this->option = $this->getModel ()->getState ( 'option' );
		$this->componentParams = $this->getModel()->getComponentParams();
		$this->record = $row;
		$this->lists = $lists;
		
		// Aggiunta toolbar
		$this->addEditEntityToolbar();
		
		parent::display ( 'edit' );
	}
	
	/**
	 * Class constructor
	 *
	 * @param array $config
	 */
	public function __construct($config = array()) {
		// Parent view object
		parent::__construct ( $config );
		
		$joomlaConfig = $this->app->getConfig ();
		$this->joomlaConfig = $this->user->getParam ( 'timezone', $joomlaConfig->get ( 'offset' ) );
	}
}