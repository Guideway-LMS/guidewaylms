<?php
namespace JExtstore\Component\Gptranslate\Administrator\Framework\Html;
/**  
 * @package GPTRANSLATE::components::com_instantfblogin::administrator
 * @subpackage framework
 * @subpackage html
 * @author Joomla! Extensions Store
 * @copyright (C) 2015 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html   
 */ 
defined ( '_JEXEC' ) or die ();
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

/**
 * HTML generic accessor to the \Joomla\CMS\Form\FormField element
 *
 * @package INSTANTFBLOGIN::components::com_instantfblogin::administrator
 * @subpackage framework
 * @subpackage html
 * @since 1.6
 */
class Modulestatus {
	/**
	 * Method to get the radio button field input markup.
	 *
	 * @return string The field input markup.
	 *
	 * @since 11.1
	 */
	protected function getInput() {
		// Initialize variables.
		$html = array ();
	
		// Retrieve status informations about the login module
		$db = Factory::getContainer()->get('DatabaseDriver');
		$queryModuleStatus = "SELECT id, published, position" .
							 "\n FROM #__modules" .
							 "\n WHERE " . $db->quoteName('module') . "=" . $db->quote('mod_gptranslate') .
							 "\n AND " . $db->quoteName('published') . ">= 0" ;
		$db->setQuery($queryModuleStatus);
		$publishedModule = $db->loadObject();
		if (is_object($publishedModule)) {
			$isModulePublished = $publishedModule->published && ($publishedModule->position != '');
		}
	
		// Initialize some field attributes.
	
		if ($isModulePublished) {
			$html [] = 	'<a target="_blank" href="index.php?option=com_modules&amp;task=module.edit&amp;id=' . $publishedModule->id . '">' .
					'<span data-bs-content="' . Text::sprintf ( 'COM_GPTRANSLATE_MODULE_ENABLED_DESC', $publishedModule->position) .
					'" class="badge bg-success label-large hasPopover modulestatus">' . '<span class="icon-checkmark icon-inline"></span>' .
					Text::sprintf ( 'COM_GPTRANSLATE_MODULE_ENABLED' ) . '</span></a>';
		} else {
			$html [] = 	'<a target="_blank" href="index.php?option=com_modules&amp;task=module.edit&amp;id=' . $publishedModule->id . '">' .
					'<span data-bs-content="' . Text::_ ( 'COM_GPTRANSLATE_MODULE_DISABLED_DESC' ) .
					'" class="badge bg-danger label-large hasPopover modulestatus">' . '<span class="icon-remove icon-inline"></span>' .
					Text::sprintf ( 'COM_GPTRANSLATE_MODULE_DISABLED' ) . '</span></a>';
		}
	
		return implode ( $html );
	}
	
	/**
	 * Return the module status html for the control
	 *
	 * @access public
	 * @return string The control html
	 */
	public function getHtmlCode() {
		return $this->getInput();
	}
}
