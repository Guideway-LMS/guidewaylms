<?php
/**
* @package com_splms
* @author JoomShaper http://www.joomshaper.com
* @copyright Copyright (c) 2010 - 2024 JoomShaper
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/

// No Direct Access
defined ('_JEXEC') or die('Resticted Aceess');

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

class SplmsViewEvent extends HtmlView{

	protected $item;
	protected $params;
	function display($tpl = null) {
		// Assign data to the view
		$this->item = $this->get('Item');
		$app = Factory::getApplication();
		$this->params = $app->getParams();
		$menus = Factory::getApplication()->getMenu();
		$menu = $menus->getActive();

		// Import Joomla component helper
		$this->params = ComponentHelper::getParams('com_splms');

		// Load Lessons model
		BaseDatabaseModel::addIncludePath(JPATH_SITE.'/components/com_splms/models');

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			throw new \Exception(implode("\n", $errors), 500);
			return false;
		}

		// legacy compatibility
		$this->item->splms_speaker_id = $this->item->speaker_id;

		//Joomla Component Helper & Get LMS Params

		// Google map
		$disable_gmap = $this->params->get('disable_gmap', 0);
		$select_map = $this->params->get('select_map', 0);

		if(!$disable_gmap && $select_map == 0) {
			$gmap_api = $this->params->get('gmap_api');
			// Load Map js
			$doc = Factory::getDocument();
			if ($gmap_api) {
				$doc->addScript('//maps.google.com/maps/api/js?sensor=false&libraries=places&key='. $gmap_api .'');
			} else{
				$doc->addScript('//maps.google.com/maps/api/js?sensor=false&libraries=places');
			}
			$doc->addScript( Uri::base(true) . '/components/com_splms/assets/js/gmap.js');
		}

		// Load courses & lesson Model
		$model 		   	= BaseDatabaseModel::getInstance( 'Events', 'SplmsModel' );
		$speakers_model = BaseDatabaseModel::getInstance('Speakers', 'SplmsModel');

		$root 	= Uri::base();
		$root 	= new Uri($root);
		$event_url = Route::_('index.php?option=com_splms&view=event&id=' . $this->item->id . ':' . $this->item->alias . SplmsHelper::getItemid('event'));
		$this->item->url  	= $root->getScheme() . '://' . $root->getHost() . $event_url;

		if (isset($this->item->topics) && !is_array($this->item->topics) && self::isJson($this->item->gallery)) {
			$this->item->topics = json_decode($this->item->topics,  TRUE);
		}

		$this->topics = array();
		if (isset($this->item->topics) && is_array($this->item->topics) ) {
			foreach ($this->item->topics as &$this->item->topic) {
				
				$this->item->topic['speaker_infos'] = '';
				$speaker_id = '';
				if( isset($this->item->topic['speaker_id']) && $this->item->topic['speaker_id'] ) {
					$speaker_id = $this->item->topic['speaker_id'];
				} elseif( isset($this->item->topic['splms_speaker_id']) && $this->item->topic['splms_speaker_id']  ) {
					$speaker_id = $this->item->topic['splms_speaker_id'];
				}
				
				if ($speaker_id) {
					$this->item->topic['speaker_infos'] = $speakers_model->getSpeakerById($speaker_id);
				}

				$group = trim($this->item->topic['group']);
				if (isset($this->topics[$group])) {
				 $this->topics[$group][] = $this->item->topic;
				} else {
				 $this->topics[$group] = array($this->item->topic);
				}
			}
		}

		$this->item->price = SplmsHelper::formatPrice($this->item->price) ?: $this->item->price;

		// gallery
		if (isset($this->item->gallery) && !is_array($this->item->gallery) && self::isJson($this->item->gallery)) {
			$this->item->gallery = json_decode($this->item->gallery, TRUE);
		}

		// Pricing table
		if (isset($this->item->pricing_tables) && !is_array($this->item->pricing_tables) && self::isJson($this->item->pricing_tables)) {
			$this->item->pricing_tables = json_decode($this->item->pricing_tables, TRUE);
		}

		// Map
		$this->map = explode(',', $this->item->map);
		//Get Eveent Sepaker
		$this->item->speaker_infos = $model->getEventSpeakers($this->item->speaker_id);
		// Get Currency
		$this->item->currency = explode(':', $this->params->get('currency', 'USD:$'));
		if ( $this->item->price && $this->item->price != 0) {
			$this->item->price = SplmsHelper::getPrice($this->item->price);
		}


		//Generate Item Meta
		$itemMeta               = array();
		$itemMeta['title']      = $this->item->title;
		$cleanText              = $this->item->description;
		$itemMeta['metadesc']   = HTMLHelper::_('string.truncate', OutputFilter::cleanText($cleanText), 155);
		$itemMeta['image']      = Uri::base() . $this->item->image;
		SplmsHelper::itemMeta($itemMeta);

		parent::display($tpl);
	}

	protected static function isJson($string) {
		return is_string($string) && is_array(json_decode($string, true)) && (json_last_error() == JSON_ERROR_NONE) ? true : false;
	}

}
