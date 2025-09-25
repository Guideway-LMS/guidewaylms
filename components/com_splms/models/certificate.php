<?php
/**
 * @package com_splms
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2024 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
 */
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\ItemModel;
use Joomla\CMS\Language\Multilanguage;

class SplmsModelCertificate extends ItemModel {

	protected $_context = 'com_splms.certificate';

	protected function populateState() {
		$app = Factory::getApplication('site');
		$itemId = $app->input->getInt('id');
		$this->setState('certificate.id', $itemId);
		$this->setState('filter.language', Multilanguage::isEnabled());
	}

	public function getItem( $itemId = null ) {

		$itemId = (!empty($itemId))? $itemId : (int)$this->getState('certificate.id');
		$app = Factory::getApplication('site');
		$userId = $app->getIdentity()->get('id');

		if ( $this->_item == null ) {
			$this->_item = array();
		}

		if (!isset($this->_item[$itemId])) {
			try {
				$db = $this->getDbo();
				$query = $db->getQuery(true);
				$query->select('a.*');
				$query->from('#__splms_certificates as a');
				$query->where('a.id = ' . (int) $itemId);
				$query->where('a.userid = ' . (int) $userId);
				
				// Filter by published state.
				$query->where('a.published = 1');

				$db->setQuery($query);
				$data = $db->loadObject();

				if (empty($data)) {
					throw new \Exception(Text::_('COM_SPLMS_ERROR_ITEM_NOT_FOUND'), 404);
				}

				$this->_item[$itemId] = $data;
				return $this->_item[$itemId];
			}
			catch (Exception $e) {
				Factory::getApplication()->enqueueMessage($e->getMessage(),'error');
				$this->_item[$itemId] = false;
			}
		}		
	}
}
