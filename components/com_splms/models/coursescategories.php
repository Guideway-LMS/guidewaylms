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
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\ListModel;

class SplmsModelCoursescategories extends ListModel {

	protected function getListQuery() {
		$app = Factory::getApplication();
		$user = Factory::getUser();
		// Get Params
		$params   = $app->getMenu()->getActive()->getParams();
		$ordering = $params->get('ordering', ' DESC');
	
		// Create a new query object.
		$db		= $this->getDbo();
		$query	= $db->getQuery(true);

		// Select the required fields from the table.
		$query->select('a.*');
		$query->from($db->quoteName('#__splms_coursescategories', 'a'));

		$comParams = Factory::getApplication()->getParams();
		if( (int) $comParams->get('subcategory_enabled') === 1 ) {
			$query->where('a.parent_id=0');
		}

		//Authorised
		$groups = implode(',', $user->getAuthorisedViewLevels());
		$query->where('a.access IN (' . $groups . ')');

		// Filter by language
		$query->where('a.language in (' . $db->quote(Factory::getLanguage()->getTag()) . ',' . $db->quote('*') . ')');
		$query->where($db->quoteName('a.published')." = ".$db->quote('1'));
		$query->order($db->quoteName('a.ordering') . $ordering);

		return $query;
	}

	protected function populateState($ordering = null, $direction = null) {
		$app = Factory::getApplication('site');
		$params = $app->getParams();
		$this->setState('list.start', $app->input->get('limitstart', 0, 'uint'));
		$limit = $params->get('limit');
		$this->setState('list.limit', $limit);
	}

	//if item not found
	public function &getItem($id = null) {
		$item = parent::getItem($id);
		if(Factory::getApplication()->isSite()) {
			if($item->id) {
				return $item;
			} else {
				throw new \Exception(Text::_('COM_SPLMS_NO_ITEMS_FOUND'), 404);
			}
		} else {
			return $item;
		}
	}

	/**
	 * Get sub category list by parent id
	 *
	 * @param [type] $parent_id
	 * @return mixed
	 * 
	 * @since 4.0.5
	 */
	public function getSubcategories($parent_id) {
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*');
		$query->from($db->quoteName('#__splms_coursescategories'));
		$query->where($db->quoteName('published')." = 1");
		$query->where($db->quoteName('parent_id')." = ".$db->quote($parent_id));
		$query->order('ordering DESC');
		$db->setQuery($query);

		$rows = $db->loadObjectList();
		
		$menus = Factory::getApplication()->getMenu();
		$menu = $menus->getActive();

		foreach ($rows as &$item) {
			$item->subcategory_id = $item->id;
			$item->courses	= count( $this->getCoursesByCategory( $item->id ) );
			$item->url		= Route::_('index.php?option=com_splms&view=coursescategory&id=' . $item->id . ':' . $item->alias . '&Itemid=' . $menu->id , false);
		}

		return $rows;
	}

	// Get Courses By Course Category ID
	public function getCoursesByCategory($category_id, $subcategories_id = null) {

		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*');
		$query->from($db->quoteName('#__splms_courses'));
		$query->where($db->quoteName('published')." = 1");
		
		// Get Sub-Category courses if subcategory exists
		if ($subcategories_id) {
			array_push($subcategories_id,$category_id);
			$query->whereIn($db->quoteName('coursecategory_id'),$subcategories_id);
		}
		else {
			$query->where($db->quoteName('coursecategory_id') . " = " . $db->quote($category_id));
		}
		
		$query->order('ordering DESC');
		$db->setQuery($query);

		$results = $db->loadObjectList();

		foreach ($results as &$result) {
			$result->url 		= Route::_('index.php?option=com_splms&view=course&id='.$result->id.':'.$result->alias . SplmsHelper::getItemid('courses'));
			$result->price 		= SplmsHelper::formatPrice($result->price);
			$result->sale_price = SplmsHelper::formatPrice($result->price) ?: $result->sale_price;

		}

		return $results;
	}

	/**
	 * Get a single column value from #__splms_coursescategories' table
	 *
	 * @param string $column 	Column name
	 * @param int 	 $id 		ID of a specific row
	 * @return void
	 */
	public function getCourseCategoryInfo($column, $id)
	{
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select($column);
		$query->from($db->quoteName('#__splms_coursescategories'));
		$query->where($db->quoteName('id') . " = " . $db->quote($id));
		$db->setQuery($query);

		$result = $db->loadColumn();
		return $result;
	}


}