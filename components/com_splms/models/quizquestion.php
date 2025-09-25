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

class SplmsModelQuizquestion extends ItemModel {

	protected $_context = 'com_splms.quizquestion';

	protected function populateState() {
		$app = Factory::getApplication('site');
		$itemId = $app->input->getInt('id');
		$this->setState('quizquestion.id', $itemId);
		$this->setState('filter.language', Multilanguage::isEnabled());
	}

	/**
	 * Handle old data and convert to new
	 * @param data the old data
	 * @param keys key exists on the data
	 * @param prefix index name prefix
	 */
	public function old2new($data, $keys = array(), $prefix = '' )
	{
		if (empty($data)) $data = [];
		foreach($keys as $key)
			if (!array_key_exists($key, $data)) 
				return false;

		$index = 0;
		$arr = array();
		foreach($data[$keys[0]] as $i => $d)
		{
			$temp = array();
			foreach($keys as $k) 
				$temp[$k] = $data[$k][$i];

			$arr[$prefix.$index] = $temp;
			$index++;
		}
		return $arr;
	}

	public function getItem( $itemId = null ) {
		$user = Factory::getUser();

		$itemId = (!empty($itemId))? $itemId : (int)$this->getState('quizquestion.id');

		if ( $this->_item == null ) {
			$this->_item = array();
		}

		if (!isset($this->_item[$itemId])) {
			try {
				$db = $this->getDbo();
				$query = $db->getQuery(true);
				$query->select('a.*');
				$query->from('#__splms_quizquestions as a');
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
					throw new \Exception($e->getMessage(), 404);
				} else {
					Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');
					$this->_item[$itemId] = false;
				}
			}
		}
		$this->_item[$itemId]->list_answers = json_decode($this->_item[$itemId]->list_answers, true);
		if (!is_bool($list_answers = $this->old2new((array)$this->_item[$itemId]->list_answers, ['qes_title', 'ans_one','ans_two','ans_three','ans_four','right_ans'], 'list_answers')))
		{
			$this->_item[$itemId]->list_answers = $list_answers;
		}
		return $this->_item[$itemId];
	}

}
