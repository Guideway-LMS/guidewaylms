<?php

/**
 * @package     SP LMS
 *
 * @copyright   Copyright (C) 2010 - 2021 JoomShaper. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No Direct Access
defined ('_JEXEC') or die('Resticted Aceess');

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Uri\Uri;

class JFormFieldGmap extends FormField
{

	protected $type = 'Gmap';

	protected function getInput() {
		//$required  = $this->required ? ' required aria-required="true"' : '';

		$params = ComponentHelper::getParams('com_splms');
		$select_map = $params->get('select_map', 0);
		$disable_gmap = $params->get('disable_gmap', 0);
		$gmap_api = $params->get('gmap_api', '');

		HTMLHelper::_('jquery.framework');
		$doc = Factory::getDocument();

		if ($gmap_api) {
			$doc->addScript('//maps.google.com/maps/api/js?sensor=false&libraries=places&key='. $gmap_api .'');
		} else{
			$doc->addScript('//maps.google.com/maps/api/js?sensor=false&libraries=places');
		}
		$doc->addScript( Uri::base(true) . '/components/com_splms/assets/js/locationpicker.jquery.js' );

		if ( empty($this->value) ) {
			$this->value = '40.7324319, -73.82480799999996';
		}

		$map = explode( ',', $this->value );

		$doc->addStyleDeclaration('.splms-gmap-canvas {
			height: 300px;
			margin-top: 10px;
		}
		.pac-container {
			z-index: 99999;
		}
		');
		if($disable_gmap == 0)
		{
			if($select_map == 1)
			{
				return '<input class="form-control" type="text" name="' . $this->name . '" id="' . $this->id . '" value="' . $this->value . '"/>';
			}

			return '<input class="addon-input gmap-latlng" type="hidden" name="' . $this->name . '" id="' . $this->id . '" value="' . $this->value .'">
					<input class="form-control splms-gmap-address" type="text" data-latitude="' . trim($map[0]) . '" data-longitude="' . trim($map[1]) . '" autocomplete="off">';
		}
		return '';

	}
}
