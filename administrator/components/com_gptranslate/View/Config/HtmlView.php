<?php
namespace JExtstore\Component\Gptranslate\Administrator\View\Config;
/**
 *
 * @package GPTRANSLATE::CONFIG::administrator::components::com_gptranslate
 * @subpackage views
 * @subpackage config
 * @author Joomla! Extensions Store
 * @copyright (C) 2015 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html 
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
use Joomla\CMS\Language\Text;
use JExtstore\Component\Gptranslate\Administrator\Framework\Helpers\Toolbars as ToolbarHelper;
use JExtstore\Component\Gptranslate\Administrator\Framework\View as GptranslateView;

/**
 * Config view
 *
 * @package GPTRANSLATE::CONFIG::administrator::components::com_gptranslate
 * @subpackage views
 * @since 1.6
 */
class HtmlView extends GptranslateView {
	// Template view variables
	protected $params_form;
	protected $params;
	protected $fieldset;

	/**
	 * Add the page title and toolbar.
	 *
	 * @since	1.6
	 */
	protected function addDisplayToolbar() {
		$doc = $this->app->getDocument();
		ToolbarHelper::title( Text::_('COM_GPTRANSLATE_MAINTITLE_TOOLBAR') . Text::_('COM_GPTRANSLATE_CONFIG' ), 'gptranslate' );
		ToolbarHelper::save('config.saveentity', 'COM_GPTRANSLATE_SAVECONFIG');
		ToolbarHelper::custom('config.exportConfig', 'download', 'download', 'COM_GPTRANSLATE_EXPORT_CONFIG', false);
		ToolbarHelper::custom('config.importConfig', 'upload', 'upload', 'COM_GPTRANSLATE_IMPORT_CONFIG', false);
		ToolbarHelper::custom('cpanel.display', 'home', 'home', 'COM_GPTRANSLATE_CPANEL', false);
	}
	
	/**
	 * Effettua il rendering dei tabs di configurazione del componente
	 * @access public
	 * @return void
	 */
	public function display($tpl = null) {
		$doc = $this->app->getDocument();
		$this->loadJQuery($doc);
		$this->loadBootstrap($doc);
		$this->loadValidation($doc);
		$doc->getWebAssetManager()->registerAndUseScript('gptranslate.fileconfig', 'administrator/components/com_gptranslate/js/fileconfig.js', [], [], ['jquery']);
		
		// Inject js translations
		$translations = array('COM_GPTRANSLATE_VALIDATION_ERROR');
		$this->injectJsTranslations($translations, $doc);
		
		// Load specific JS App
		$doc->getWebAssetManager()->addInlineScript("
				Joomla.submitbutton = function(pressbutton) {
					if(!jQuery.fn.validation) {
						jQuery.extend(jQuery.fn, gptranslatejQueryBackup.fn);
					}
			
					jQuery('#adminForm').validation();
	
					if (pressbutton == 'cpanel.display') {
						jQuery('#adminForm').off();
						Joomla.submitform( pressbutton );
						return true;
					}
	
					if(jQuery('#adminForm').validate()) {
						Joomla.submitform( pressbutton );
						return true;
					}
					var parentId = jQuery('ul.errorlist').parents('div.tab-pane').attr('id');

					var nodeElement = document.querySelector('#tab_configuration a[data-element=' + parentId + ']');
					if(nodeElement) {
						var tabInstance = new bootstrap.Tab(nodeElement);
						tabInstance.show();
					}
					return false;
				};
			");
		
		$doc->getWebAssetManager()->addInlineStyle('@media (max-width: 1200px) { body.admin.com_gdpr { min-width: 1200px; }}');
		// Inject js translations
		$translations = array(
				'COM_GPTRANSLATE_REQUIRED',
				'COM_GPTRANSLATE_PICKFILE',
				'COM_GPTRANSLATE_STARTIMPORT',
				'COM_GPTRANSLATE_CANCELIMPORT',
				'COM_GPTRANSLATE_OPEN_COOKIE_TOOLBAR',
				'COM_GPTRANSLATE_CUSTOM_COPY_CODE',
				'COM_GPTRANSLATE_CUSTOM_COPIED_CODE',
				'COM_GPTRANSLATE_RESET_ALL_CONSENTS',
				'COM_GPTRANSLATE_RESET_ALL_CONSENTS_TITLE',
				'COM_GPTRANSLATE_RESET_ALL_CONSENTS_DESC',
				'COM_GPTRANSLATE_RESET_YEARLY_CONSENTS',
				'COM_GPTRANSLATE_RESET_YEARLY_CONSENTS_TITLE',
				'COM_GPTRANSLATE_RESET_YEARLY_CONSENTS_DESC'
		);
		$this->injectJsTranslations($translations, $doc);
		
		$model = $this->getModel();
		$params = $model->getData();
		$form = $model->getForm();
		
		// Bind the form to the data.
		if ($form && $params) {
			$form->bind($params);
		}
		
		$this->params_form = $form;
		$this->params = $params;
		$this->fieldset = $this->getModel()->getState('fieldset');
		
		// Aggiunta toolbar
		$this->addDisplayToolbar();
		
		// Output del template
		parent::display();
	}
}
?>