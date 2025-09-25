<?php
/**
 * @package     SP LMS
 *
 * @copyright   Copyright (C) 2010 - 2021 JoomShaper. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No Direct Access
defined('JPATH_PLATFORM') or die('Resticted Aceess');

use Joomla\CMS\Form\FormField;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;

class JFormFieldLmsfile extends FormField 
{

	protected $type = 'Lmsfile';
	protected $accept;

	public function __get($name) {
		switch ($name) {
			case 'accept':
			return $this->$name;
		}

		return parent::__get($name);
	}

	public function __set($name, $value) {
		switch ($name) {
			case 'accept':
			$this->$accept = (string) $value;
			break;

			default:
			parent::__set($name, $value);
		}
	}

	public function setup(SimpleXMLElement $element, $value, $group = null) {
		$return = parent::setup($element, $value, $group);

		if ($return) {
			$this->accept = (string) $this->element['accept'];
		}

		return $return;
	}

	protected function getInput(){
		$accept    = !empty($this->accept) ? ' accept="' . $this->accept . '"' : '';
		$size      = !empty($this->size) ? ' size="' . $this->size . '"' : '';
		$class     = !empty($this->class) ? ' class="' . $this->class . '"' : '';
		$disabled  = $this->disabled ? ' disabled' : '';
		$required  = $this->required ? ' required aria-required="true"' : '';
		$autofocus = $this->autofocus ? ' autofocus' : '';
		$multiple  = $this->multiple ? ' multiple' : '';

		$onchange = $this->onchange ? ' onchange="' . $this->onchange . '"' : '';

		HTMLHelper::_('jquery.framework');
		HTMLHelper::_('script', 'system/html5fallback.js', false, true);

		$images = array('jpg', 'jpeg', 'png', 'gif', 'bmp');
		$attachments = array('pdf', 'zip');
		$ext = strtolower(File::getExt($this->value));

		$output = '';
		if(in_array($ext, $attachments)) {
			$output .= '<p><a href="'. Uri::root() . $this->value .'">' . basename($this->value) . '</a></p>';
		} else if (in_array($ext, $images)) {
			$output .= '<div style="margin-bottom: 15px; padding: 5px; border: 1px solid #e5e5e5; border-radius: 3px; display: inline-block;"><img src="'. Uri::root() . $this->value .'" style="max-width: 200px; max-height: 200px;"></div><br />';
		}

		$output .= '<input type="file" name="' . $this->name . '" id="' . $this->id . '" ' . $accept
		. $disabled . $class . $size . $onchange . $required . $autofocus . $multiple . ' />';

		return $output;
	}

}
