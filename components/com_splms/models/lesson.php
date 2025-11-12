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
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

class SplmsModelLesson extends ItemModel {

	protected $_context = 'com_splms.lesson';

	protected function populateState() {
		$app = Factory::getApplication('site');
		$itemId = $app->input->getInt('id');
		$this->setState('lesson.id', $itemId);
		$this->setState('filter.language', Multilanguage::isEnabled());
	}

	public function getItem( $itemId = null ) {

		$user = Factory::getUser();

		$itemId = (!empty($itemId))? $itemId : (int)$this->getState('lesson.id');

		if ( $this->_item == null ) {
			$this->_item = array();
		}

		if (!isset($this->_item[$itemId])) {
			try {
				$db = $this->getDbo();
				$query = $db->getQuery(true);
				$query->select('a.*');
				$query->from('#__splms_lessons as a');
				$query->where('a.id = ' . (int) $itemId);
				
				// Filter by published state.
				$query->where('a.published = 1');

				if ($this->getState('filter.language')) {
					$query->where('a.language in (' . $db->quote(Factory::getLanguage()->getTag()) . ',' . $db->quote('*') . ')');
				}

				//Authorised
				$groups = implode(',', $user->getAuthorisedViewLevels());
				$query->where('a.access IN (' . $groups . ')');

				$db->setQuery($query);
				$data = $db->loadObject();

				$model 		  = BaseDatabaseModel::getInstance('Course', 'SplmsModel');
				$data->topics = $model->getTopics((int) $data->course_id);


				if (empty($data)) {
					throw new \Exception(Text::_('COM_SPLMS_ERROR_ITEM_NOT_FOUND'), 404);
				}

				$user = Factory::getUser();
				$groups = $user->getAuthorisedViewLevels();
				if(!in_array($data->access, $groups)) {
					throw new \Exception(Text::_('COM_SPLMS_ERROR_NOT_AUTHORISED'), 404);
				}

				$this->_item[$itemId] = $data;
			}
			catch (Exception $e) {
				if ($e->getCode() == 404 ) {
					throw new \Exception($e->getMessage());
				} else {
					Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');
					$this->_item[$itemId] = false;
				}
			}
		}

		return $this->_item[$itemId];
	}

}
