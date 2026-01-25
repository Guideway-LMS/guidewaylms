<?php
/**
 * @package     com_splms
 * @version     4.2.0
 * @author      JoomShaper http://www.joomshaper.com
 * @copyright   Copyright (c) 2010 - 2024 JoomShaper
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
 */

defined('_JEXEC') or die;

use Joomla\CMS\Form\FormRule;

/**
 * Form Rule class for the Joomla Framework.
 */
class JFormRuleExecutable extends FormRule
{
	/**
	 * Method to test the value.
	 *
	 * @param   \SimpleXMLElement  $element  The SimpleXMLElement object representing the `<field>` tag for the form field object.
	 * @param   mixed              $value    The form field value to validate.
	 * @param   string             $group    The field name group control value. This acts as an array key for the field code
	 *                                       in the submitted form data (e.g. jform or params).
	 * @param   \Joomla\Registry\Registry  $input    An optional Registry object with the entire data set to validate against the entire form.
	 * @param   \Joomla\CMS\Form\Form      $form     The form object for which the field is being tested.
	 *
	 * @return  boolean  True if the value is valid, false otherwise.
	 */
	public function test(\SimpleXMLElement $element, $value, $group = null, \Joomla\Registry\Registry $input = null, \Joomla\CMS\Form\Form $form = null)
	{
		// If the value is empty, return true (allow empty if not required)
		if (empty($value))
		{
			return true;
		}

		// Check if file exists and is executable
		if (is_file($value) && is_executable($value))
		{
			return true;
		}

		return false;
	}
}
