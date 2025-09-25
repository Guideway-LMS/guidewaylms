<?php
namespace JExtstore\Component\Gptranslate\Administrator\View\Cpanel;
/**
 *
 * @package GPTRANSLATE::CPANEL::administrator::components::com_gptranslate
 * @subpackage views
 * @subpackage cpanel
 * @author Joomla! Extensions Store
 * @copyright (C) 2015 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html 
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use JExtstore\Component\Gptranslate\Administrator\Framework\Helpers\Toolbars as ToolbarHelper;
use JExtstore\Component\Gptranslate\Administrator\Framework\Html\Modulestatus;
use JExtstore\Component\Gptranslate\Administrator\Framework\View as GptranslateView;

/**
 * CPanel view
 *
 * @package GPTRANSLATE::CPANEL::administrator::components::com_gptranslate
 * @subpackage views
 * @subpackage cpanel
 * @since 1.6
 */
class HtmlView extends GptranslateView {
	// Template view variables
	protected $componentParams;
	protected $updatesData;
	protected $infodata;
	protected $currentVersion;
	protected $icons;
	public $httpClient;
	
	/**
	 * Renderizza l'iconset del cpanel
	 *
	 * @param $link string
	 * @param $image string
	 * @access private
	 * @return string
	 */
	private function getIcon($link, $image, $text, $target = '', $title = null, $class = 'icons') {
		$app = Factory::getApplication ();
		$lang = $app->getLanguage ();
		$option = $this->option;
		?>
		<div class="<?php echo $class;?>" style="float:<?php echo ($lang->isRTL()) ? 'right' : 'left'; ?>;">
			<div class="icon" aria-hidden="true">
				<a <?php echo $title . $class;?> <?php echo $target;?> href="<?php echo $link; ?>" role="button">
					<div class="task <?php echo $image;?>"></div> <span class="task"><?php echo $text; ?></span>
				</a>
			</div>
		</div>
<?php
		}
		
	/**
	 * Add the page title and toolbar.
	 *
	 * @since	1.6
	 */
	protected function addDisplayToolbar() {
		$doc = $this->app->getDocument();
		ToolbarHelper::title( Text::_('COM_GPTRANSLATE_CPANEL_TOOLBAR' ), 'gptranslate' );
		ToolbarHelper::custom('cpanel.display', 'home', 'home', 'COM_GPTRANSLATE_CPANEL', false);
	}
	
	/**
	 * Effettua il rendering del pannello di controllo
	 * @access public
	 * @return void
	 */
	public function display($tpl = null) {
		$doc = $this->app->getDocument ();
		$user = $this->app->getIdentity();
		$this->loadJQuery($doc);
		$this->loadBootstrap($doc);
		
		$wa = $doc->getWebAssetManager();
		
		$wa->registerAndUseStyle ( 'gptranslate.cpanel', 'administrator/components/com_gptranslate/css/cpanel.css');
		
		$wa->registerAndUseScript ( 'gptranslate.chart', 'administrator/components/com_gptranslate/js/chart.js', [], [], ['jquery']);
		$wa->registerAndUseScript ( 'gptranslate.cpanel', 'administrator/components/com_gptranslate/js/cpanel.js', [], [], ['jquery', 'gptranslate.boostrap-interface', 'gptranslate.chart']);
		
		// Inject js translations
		$translations = array(
				'COM_GPTRANSLATE_START_CHART',
				'COM_GPTRANSLATE_TRANSLATIONS_SHORT_CHART',
				'COM_GPTRANSLATE_UTRANSLATIONS_SHORT_CHART',
				'COM_GPTRANSLATE_END_CHART' 
		);
		$this->injectJsTranslations($translations, $doc);
		
		// Buffer delle icons
		ob_start ();
		$this->getIcon ( 'index.php?option=com_gptranslate&task=translations.display', 'icon-globe', Text::_ ( 'COM_GPTRANSLATE_TRANSLATIONS' ) );
   		$this->getIcon ( 'index.php?option=com_gptranslate&task=config.display', 'icon-cog', Text::_ ( 'COM_GPTRANSLATE_CONFIG' ) );
		$this->getIcon ( 'https://storejextensions.org/gptranslate_documentation.html', 'icon-help', Text::_ ( 'COM_GPTRANSLATE_HELP' ) );
		
		$contents = ob_get_clean ();
		
		$infoData = $this->getModel()->getData();
		$doc->getWebAssetManager()->addInlineScript('var gptranslateChartData = ' . json_encode($infoData));
		
		// Assign reference variables
		$this->icons = $contents;
		$this->componentParams = $this->getModel()->getComponentParams();
		$this->updatesData = $this->getModel()->getUpdates($this->httpClient);
		$this->infodata = $infoData;
		$this->currentVersion = strval(simplexml_load_file(JPATH_COMPONENT_ADMINISTRATOR . '/gptranslate.xml')->version);
		$moduleStatusCtrlClass = new Modulestatus();
		$this->moduleStatus = $moduleStatusCtrlClass->getHtmlCode();
		
		// Add toolbar
		$this->addDisplayToolbar();
		
		// Output del template
		parent::display ();
	}
}
?>