<?php

/**
 * @package com_splms
 * @subpackage  mod_splmscoursesearch
 *
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2024 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
 */

// No Direct Access
defined ('_JEXEC') or die('Resticted Aceess');

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Filter\OutputFilter;

// Load Component Helper
require_once JPATH_BASE . '/components/com_splms/helpers/helper.php';

class modSplmscoursesearchHelper {

	public static function getAjax(){

		$input  = Factory::getApplication()->input;

		$input 	= $input->get('data', '', 'RAW');

		if($input == '') return;

		$results = self::getSearchedCourses($input);

		$output = '<ul class="splms-courses-search results-list">';

		if (!empty($results)) {
			foreach ($results as $result) {
				$output .= '<li>';
				$output .= '<a href='.$result->url.'>';
				$output .= $result->title;
				$output .= '</a>';
				$output .= '</li>';
			}
		}else{
			$output .= '<li class="splms-empty">';
			$output .= Text::_('MOD_SPLMSCOURSESEARCH_NO_ITEM_FOUND');
			$output .= '</li>';
		}

		$output .= '</ul>';

		return $output;
	}

	private static function getSearchedCourses($getCourseName){
		$db = Factory::getDbo();
		$query = $db->getQuery(true);

		$search = preg_replace('#\xE3\x80\x80#s', " ", trim($getCourseName));
		$search_array = explode(" ", $db->escape($search) );
		
		$query->select($db->quoteName(array('id', 'title', 'alias', 'description')));
		$query->from($db->quoteName('#__splms_courses'));
		$query->where($db->quoteName('published')." = 1");
		$query->where('language in (' . $db->quote(Factory::getLanguage()->getTag()) . ',' . $db->quote('*') . ')');
		$query->where($db->quoteName('access')." IN (" . implode( ',', Factory::getUser()->getAuthorisedViewLevels() ) . ")");
		$query->where($db->quoteName('title') . " LIKE '%" . implode("%' OR " . $db->quoteName('title') . " LIKE '%", $search_array) . "%'");
		$query->order('ordering DESC');
		$query->setLimit(10);
		$db->setQuery($query); 
		$results = $db->loadObjectList();

		foreach ($results as &$result) {
			$result->url  = Route::_('index.php?option=com_splms&view=course&id='.$result->id.':'.$result->alias . SplmsHelper::getItemid('courses'));
			$result->title = OutputFilter::ampReplace($result->title);	
		}

		return $results;
	}

}
