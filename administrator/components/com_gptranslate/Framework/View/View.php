<?php
namespace JExtstore\Component\Gptranslate\Administrator\Framework;
/**
 *
 * @package GPTRANSLATE::FRAMEWORK::administrator::components::com_gptranslate
 * @subpackage framework
 * @subpackage view
 * @author Joomla! Extensions Store
 * @copyright (C) 2015 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;

/**
 * Base view for all display core
 *
 * @package GPTRANSLATE::FRAMEWORK::administrator::components::com_gptranslate
 * @subpackage framework
 * @subpackage view
 * @since 2.0
 */
class View extends HtmlView {
	/**
	 * User object for ACL authorise check
	 *
	 * @access protected
	 * @var Object
	 */
	protected $user;
	
	/**
	 * Reference to application
	 *
	 * @access public
	 * @var Object
	 */
	public $app;
	
	/**
	 * Document object, needed by views to inject
	 * CSS/JS tags into document output
	 *
	 * @access public
	 * @var Object
	 */
	public $doc;
	
	/**
	 * Reference to option executed
	 *
	 * @access public
	 * @var string
	 */
	public $option;
	
	/**
	 * Inject language constant into JS Domain maintaining same name mapping
	 *
	 * @access protected
	 * @param $translations array
	 * @param $document Object&
	 * @return void
	 */
	protected function injectJsTranslations($translations, $document): void {
		$jsInject = null;
		// Do translations
		foreach ( $translations as $translation ) {
			$jsTranslation = strtoupper ( $translation );
			$translated = Text::_( $jsTranslation, true );
			$jsInject .= <<<JS
			var $translation = '{$translated}';
JS;
		}
		$document->getWebAssetManager()->addInlineScript($jsInject);
	}
	
	/**
	 * Manage injecting jQuery framework into document with class inheritance support
	 *
	 * @access protected
	 * @param Object& $doc
	 * @return void
	 */
	protected function loadJQuery($document, $fullStack = true): void {
		$wa = $document->getWebAssetManager();
		if($fullStack) {
			$wa->useScript('jquery');
			$wa->useScript('jquery-noconflict');
			array_map ( function ($script) use ($wa) {
				$wa->useScript ( 'bootstrap.' . $script );
			}, [
					'collapse',
					'modal',
					'popover',
					'tab'
			] );
		} else {
			$wa->useScript('jquery');
			$wa->useScript('jquery-noconflict');
		}
		
		$wa->useScript('core');
		
		// jQuery foundation framework
		$wa->registerAndUseScript('gptranslate.jstorage', 'administrator/components/com_gptranslate/js/jstorage.min.js', [], [], ['jquery']);
	}
	
	/**
	 * Manage injecting Bootstrap framework into document
	 *
	 * @access protected
	 * @param Object& $doc
	 * @return void
	 */
	protected function loadBootstrap($document): void {
		// Main styles for admin interface
		$document->getWebAssetManager()->registerAndUseStyle ( 'gptranslate.boostrap-interface', 'administrator/components/com_gptranslate/css/bootstrap-interface.css');
	
		if(version_compare(JVERSION, '5', '>=') && $this->app->isClient('administrator')) {
			$document->getWebAssetManager()->registerAndUseStyle ( 'gptranslate.dark-theme', 'administrator/components/com_gptranslate/css/dark-theme.css');
		}
		
		// Main JS file for admin interface
		$document->getWebAssetManager()->registerAndUseScript ( 'gptranslate.boostrap-interface', 'administrator/components/com_gptranslate/js/bootstrap-interface.js', [], [], ['jquery']);
	}
	
	/**
	 * Manage injecting valildation plugin into document
	 *
	 * @access protected
	 * @param Object& $doc
	 * @return void
	 */
	protected function loadValidation($document): void {
		$document->getWebAssetManager()->registerAndUseStyle ( 'gptranslate.simplevalidation', 'administrator/components/com_gptranslate/css/simplevalidation.css');
		
		$document->getWebAssetManager()->registerAndUseScript ( 'gptranslate.simplevalidation', 'administrator/components/com_gptranslate/js/jquery.simplevalidation.js', [], [], ['jquery']);
	}
	
	/**
	 * Manage injecting jQuery UI framework into document
	 *
	 * @access protected
	 * @param Object& $doc
	 * @return void
	 */
	protected function loadJQueryUI($document): void {
		$document->getWebAssetManager()->registerAndUseStyle ( 'gptranslate.jqueryui', 'administrator/components/com_gptranslate/css/jqueryui/jquery-ui.custom.min.css');
		
		$document->getWebAssetManager()->registerAndUseScript ( 'gptranslate.jqueryui', 'administrator/components/com_gptranslate/js/jquery-ui.min.js', [], [], ['jquery']);
		
		$document->getWebAssetManager()->registerAndUseStyle ( 'gptranslate.timepicker', 'administrator/components/com_gptranslate/css/jqueryui/jquery.ui-timepicker-addon.css');
		$document->getWebAssetManager()->registerAndUseScript ( 'gptranslate.timepicker', 'administrator/components/com_gptranslate/js/jquery.ui-timepicker-addon.js', [], [], ['jquery', 'gptranslate.jqueryui']);
		
		// Load the language object and the translation accordingly
		$language = Factory::getApplication()->getLanguage ();
		$langTag = $language->getTag ();
		
		$explodedLangTag = explode ( '-', $langTag );
		$langCode = array_shift ( $explodedLangTag );
		
		// Security safe
		if (! file_exists ( JPATH_SITE . '/administrator/components/com_gptranslate/js/i18n/datepicker/datepicker-' . $langCode . '.js' )) {
			$langCode = 'en';
		}
		
		$document->getWebAssetManager()->registerAndUseScript ( 'gptranslate.i18n.datepicker', 'administrator/components/com_gptranslate/js/i18n/datepicker/datepicker-' . $langCode . '.js', [], [], ['gptranslate.jqueryui'] );
		
		// Security safe
		if (! file_exists ( JPATH_SITE . '/administrator/components/com_gptranslate/js/i18n/timepicker/timepicker-' . $langCode . '.js' )) {
			$langCode = 'en';
		}
		
		$document->getWebAssetManager()->registerAndUseScript ( 'gptranslate.i18n.timepicker', 'administrator/components/com_gptranslate/js/i18n/timepicker/timepicker-' . $langCode . '.js', [], [], ['gptranslate.timepicker'] );
	}
	
	/**
	 * Class constructor
	 *
	 * @param array $config
	 *        	return Object
	 */
	public function __construct($config = array()) {
		parent::__construct ( $config );
		
		$this->app = Factory::getApplication ();
		$this->option = $this->app->getInput()->get ( 'option' );
		$this->user = $this->app->getIdentity ();
		$this->doc = $this->app->getDocument();
	}
}