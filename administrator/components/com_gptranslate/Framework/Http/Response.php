<?php
namespace JExtstore\Component\Gptranslate\Administrator\Framework\Http;
/**
 * @package GPTRANSLATE::FRAMEWORK::components::com_gptranslate
 * @subpackage framework
 * @subpackage http
 * @author Joomla! Extensions Store
 * @copyright (C) 2015 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('_JEXEC') or die('Restricted access');

/**
 * HTTP response data object class.
 *
 * @package GPTRANSLATE::FRAMEWORK::components::com_gptranslate
 * @subpackage framework
 * @subpackage http
 * @since 1.0
 */
class Response {
	/**
	 * @var    integer  The server response code.
	 * @since  11.3
	 */
	public $code;

	/**
	 * @var    array  Response headers.
	 * @since  11.3
	 */
	public $headers = array();

	/**
	 * @var    string  Server response body.
	 * @since  11.3
	 */
	public $body;
	
	/**
	 * @var    string  The URL checked
	 * @since  11.3
	 */
	public $urlChecked;
}
