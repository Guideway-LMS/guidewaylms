<?php
namespace JExtstore\Component\Gptranslate\Administrator\Framework\Helpers;
/** 
 * @package GPTRANSLATE::components::com_gptranslate
 * @subpackage framework
 * @subpackage helpers
 * @author Joomla! Extensions Store
 * @Copyright (C) 2015 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html   
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

/**
 * Override for html utility functions
 * @package GPTRANSLATE::components::com_gptranslate
 * @subpackage framework
 * @subpackage helpers
 * @since 1.0
 */ 
class Html {
	/**
	 * Generates a yes/no radio list.
	 *
	 * @param   string  $name      The value of the HTML name attribute
	 * @param   array   $attribs   Additional HTML attributes for the `<select>` tag
	 * @param   string  $selected  The key that is selected
	 * @param   string  $yes       Language key for Yes
	 * @param   string  $no        Language key for no
	 * @param   mixed   $id        The id for the field or false for no id
	 *
	 * @return  string  HTML for the radio list
	 *
	 * @since   1.5
	 */
	public static function booleanlist($name, $attribs = array(), $selected = null, $yes = 'JYES', $no = 'JNO', $id = false)
	{
		$arr = array(HTMLHelper::_('select.option', '1', Text::_($yes)), HTMLHelper::_('select.option', '0', Text::_($no)));
	
		return self::radiolist( $arr, $name, $attribs, 'value', 'text', (int) $selected, $id);
	}
	
	public static function radiolist($data, $name, $attribs = null, $optKey = 'value', $optText = 'text', $selected = null, $idtag = false,
			$translate = false)
	{
	
		if (is_array($attribs))
		{
			$attribs = ArrayHelper::toString($attribs);
		}
	
		$id_text = $idtag ?: $name;
	
		$html = '<div class="controls">';
	
		foreach ($data as $obj)
		{
			$k = $obj->$optKey;
			$t = $translate ? Text::_($obj->$optText) : $obj->$optText;
			$id = (isset($obj->id) ? $obj->id : null);
	
			$extra = '';
			$id = $id ? $obj->id : $id_text . $k;
	
			if (is_array($selected))
			{
				foreach ($selected as $val)
				{
					$k2 = is_object($val) ? $val->$optKey : $val;
	
					if ($k == $k2)
					{
						$extra .= ' selected="selected" ';
						break;
					}
				}
			}
			else
			{
				$extra .= ((string) $k === (string) $selected ? ' checked="checked" ' : '');
			}
	
			$html .= "\n\t" . '<label for="' . $id . '" id="' . $id . '-lbl" class="radio">';
			$html .= "\n\t\n\t" . '<input type="radio" name="' . $name . '" id="' . $id . '" value="' . $k . '" ' . $extra
			. $attribs . '>' . $t;
			$html .= "\n\t" . '</label>';
		}
	
		$html .= "\n";
		$html .= '</div>';
		$html .= "\n";
	
		return $html;
	}
	
	/**
	 * Method to create a select list of states for filtering
	 * By default the filter shows only published and unpublished items
	 *
	 * @param string $filter_state
	 *        	The initial filter state
	 * @param string $published
	 *        	The Text string for published
	 * @param string $unpublished
	 *        	The Text string for Unpublished
	 * @param string $archived
	 *        	The Text string for Archived
	 * @param string $trashed
	 *        	The Text string for Trashed
	 *        	
	 * @return string
	 *
	 * @since 1.5
	 */
	public static function state($filter_state = '*', $published = 'JPUBLISHED', $unpublished = 'JUNPUBLISHED', $archived = null, $trashed = null) {
		$state = array (
				'' => '- ' . Text::_ ( 'JLIB_HTML_SELECT_STATE' ) . ' -',
				'P' => Text::_ ( $published ),
				'U' => Text::_ ( $unpublished ) 
		);
		
		if ($archived) {
			$state ['A'] = Text::_ ( $archived );
		}
		
		if ($trashed) {
			$state ['T'] = Text::_ ( $trashed );
		}
		
		return HTMLHelper::_ ( 'select.genericlist', $state, 'filter_state', array (
				'list.attr' => 'class="form-select" size="1" onchange="Joomla.submitform();"',
				'list.select' => $filter_state,
				'option.key' => null 
		) );
	}
}

