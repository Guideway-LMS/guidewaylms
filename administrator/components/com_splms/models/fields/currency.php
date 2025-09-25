<?php

/**
 * @package     SP LMS
 *
 * @copyright   Copyright (C) 2010 - 2021 JoomShaper. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Version;

FormHelper::loadFieldClass('list');

$version = new Version();
$JoomlaVersion = $version->getShortVersion();

if (version_compare($JoomlaVersion, '4.0.0', '>='))
{
	JLoader::registerAlias('JFormFieldList', 'Joomla\CMS\Form\Field\ListField');
}

class JFormFieldCurrency extends JFormFieldList
{

	protected $type = 'Currency';

	public function getOptions() {
		$path = JPATH_SITE . '/components/com_splms/assets/json/currencies.json';
		$json = file_get_contents($path);
		$currencies = json_decode($json, false);

		$list = array();
		foreach($currencies as $currency) {
			$list[$currency->code . ':' . $currency->symbol] = $currency->name . ' ('. $currency->symbol .')';
		}

		$options = array_merge(parent::getOptions(), $list);
		
		return $options;
	}
}
