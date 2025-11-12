<?php 
namespace JExtstore\Component\Gptranslate\Administrator\View\Ajaxserver;

/**
 * @package GPTRANSLATE::AJAXSERVER::administrator::components::com_gptranslate
 * @subpackage views
 * @subpackage ajaxserver
 * @author Joomla! Extensions Store
 * @copyright (C) 2024 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
use JExtstore\Component\Gptranslate\Administrator\Framework\View as GptranslateView;

/**
 * Config view
 *
 * @package GPTRANSLATE::AJAXSERVER::administrator::components::com_gptranslate
 * @subpackage views
 * @subpackage ajaxserver
 * @since 2.4
 */
class JsonView extends GptranslateView {
	/**
	 * Return application/json response to JS client APP
	 * Replace $tpl optional param with $userData contents to inject
	 *        	
	 * @access public
	 * @param string $tpl
	 * @return void
	 */
	public function display($userData = null) {
		echo json_encode($userData);  
	}
}