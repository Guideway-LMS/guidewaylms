<?php
namespace JExtstore\Component\Gptranslate\Administrator\Controller;
/**
 * @package GPTRANSLATE::CPANEL::administrator::components::com_gptranslate
 * @subpackage controllers
 * @author Joomla! Extensions Store
 * @Copyright (C) 2015 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html 
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use JExtstore\Component\Gptranslate\Administrator\Framework\Controller as GptranslateController;
use JExtstore\Component\Gptranslate\Administrator\Framework\Http;

/**
 * CPanel controller
 *
 * @package GPTRANSLATE::CPANEL::administrator::components::com_gptranslate
 * @subpackage controllers
 * @since 1.0
 */
class CpanelController extends GptranslateController {
	/**
	 * Show Control Panel
	 * @access public
	 * @return void
	 */
	function display($cachable = false, $urlparams = false) {
		$view = $this->getView('cpanel', 'html', '', array('base_path' => $this->basePath, 'layout' => 'default'));
		
		// Dependency injection setter on view/model
		$HTTPClient = new Http();
		$view->httpClient = $HTTPClient;
		
		// No operations
		parent::display ($cachable); 
	}
	
	/**
	 * Class Constructor
	 *
	 * @access public
	 * @return Object&
	 */
	public function __construct($config = array(), ?MVCFactoryInterface $factory = null, $app = null, $input = null) {
		parent::__construct($config, $factory, $app, $input);
	}
}
?>