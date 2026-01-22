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
use Joomla\CMS\Language\Text;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Component\ComponentHelper;

class SplmsHelper {

	public static function getItemid($view = 'courses') {
		$db = Factory::getDbo();

		$query = $db->getQuery(true);
		$query->select($db->quoteName(array('id')));
		$query->from($db->quoteName('#__menu'));
		$query->where($db->quoteName('link') . ' LIKE '. $db->quote('%option=com_splms&view='. $view .'%'));
		$query->where($db->quoteName('client_id') . ' = '. $db->quote('0'));
		$query->where($db->quoteName('published') . ' = '. $db->quote('1'));
		$db->setQuery($query);
		$result = $db->loadResult();

		if($result && is_numeric($result)) {
			return '&Itemid=' . $result;
		}

		return;
	}

	public static function getCourseCategories( $limit = NULL, $featured = NULL, $hideEmpty = false ,$category_id = NULL) {
		$db = Factory::getDbo();
        $query = $db->getQuery(true);
        $query->select($db->quoteName(array( 'a.id', 'a.alias', 'a.title', 'a.featured', 'a.show', 'a.icon', 'a.image' )));
		$query->from($db->quoteName('#__splms_coursescategories', 'a'));
		
		// course count
		$query->select("CASE WHEN courses.count IS NULL THEN 0 ELSE courses.count END as count")
			->join('LEFT', '( SELECT b.coursecategory_id, COUNT(b.coursecategory_id) AS count FROM #__splms_courses b WHERE b.published = 1 GROUP BY b.coursecategory_id ) AS courses ON a.id = courses.coursecategory_id');
			
		if ($category_id) {
			//$query->where($db->quoteName('a.id') . "=" . $db->quote($category_id));
			$query->where($db->quoteName('a.parent_id') . "=" . $db->quote($category_id));
			//$query->whereIn($db->quoteName('a.parent_id'),$category_id);
		}
		
		if($hideEmpty) {
			$query->where($db->quoteName('courses.count') . ' != 0');
		}

		$query->where($db->quoteName('a.published')." = 1");
		// if show only featured items
		if($featured) {
			$query->where($db->quoteName('a.featured')." = 1");
		}
		// if limit of the list
		if($limit) {
			$query->setLimit($limit);
		}
		$query->order('a.ordering ASC');
        $db->setQuery($query);
		$results = $db->loadObjectList();
		
		if(is_array($results)) {
			return $results;
		} else {
			return array();
		}
	}

	public static function getCourseLavels() {
		// Get all courses
		$courses = self::getCoursesList();
		
		// Generate lavels
		$course_lavels = array();
		foreach ($courses as $course) {
			// if have value in lavel and not already pushed
			if( $course->level && !in_array($course->level, $course_lavels)  ) {
				$course_lavels[] = $course->level;
			}
		}
		return $course_lavels;
	}

	// Get course list
	public static function getCoursesList() {
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select($db->quoteName( array('a.id', 'a.title', 'a.created', 'a.price', 'a.level') ));
		$query->from($db->quoteName('#__splms_courses', 'a'));
		$query->where($db->quoteName('a.published')." = 1");
		$query->order('a.ordering DESC');
		$db->setQuery($query);
		$results = $db->loadObjectList();

		return $results;
	}

	// Chceck dublicate tx id
	public static function getDublicateTransaction($transaction_id){
		$db = Factory::getDbo();
		// Create a new query object.
		$query = $db->getQuery(true);

		// Select all records from the user profile table where key begins with "custom.".
		// Order it by the ordering field.
		$query->select($db->quoteName(array('splms_order_id', 'order_payment_id')));
		$query->from($db->quoteName('#__splms_orders'));
		$query->where($db->quoteName('published')." = 1");
		$query->where($db->quoteName('order_payment_id')." = ".$db->quote($transaction_id));
		$query->order('ordering DESC');

		// Reset the query using our newly populated query object.
		$db->setQuery($query);

		// Load the results as a list of stdClass objects (see later for more options on retrieving data).
		$result = $db->loadObject();


	 	return $result;
	}

	// generate Currency
	public static function generateCurrency($amount = 0, $sale_amount = 0) {
		return self::getPrice($amount, $sale_amount);
	}

	// Get price
	public static function getPrice($amount = 0, $sale_amount = 0) {
		$params = ComponentHelper::getParams('com_splms');

		//Get Currency
		list($code, $symbol) = explode(':', $params->get('currency', 'USD:$'));
		$position = $params->get('currency_position', 'before');

		$price = $position == 'after' ? $amount . $symbol : $symbol . $amount;
		$sale_price = $position == 'after' ? $sale_amount . $symbol : $symbol . $sale_amount;

		$output = '';
		$output .= '<div class="splms-price-box">';
		if ($amount && $sale_amount > 0) {
			$output .= '<ins>';
	           $output .= '<div class="splms-sale-price">';
	            	$output .= '<span>' . $sale_price . '</span>';
	            $output .= '</div>';
			$output .= '</ins>';
			
			$output .= '<del>';
	            $output .= '<div class="splms-item-price">';
	            	$output .= '<span>' . $price . '</span>';
	            $output .= '</div>';
			$output .= '</del>';
		} else {
			$output .= $price;
		}
		
		$output .= '</div>';

		return $output;
	}

	//review time
	public static function timeAgo($time) {
	   $periods = array("SECOND", "MINUTE", "HOUR", "DAY", "WEEK", "MONTH", "YEAR", "DECADE");
	   $lengths = array("60","60","24","7","4.35","12","10");

       $difference     = strtotime(Factory::getDate('now')) - strtotime($time);
       $tense         = "ago";

	   for($j = 0; $difference >= $lengths[$j] && $j < count($lengths)-1; $j++) {
	       $difference /= $lengths[$j];
	   }

	   $difference = round($difference);

	   if($difference == 0) $difference = 1;

	   if($difference != 1) {
	       $periods[$j] .= "S";
	   }

	   return $difference . ' ' . Text::_('COM_SPLMS_TIMEAGO_' . $periods[$j]) . ' ' . Text::_('COM_SPLMS_TIMEAGO_AGO');
	}

	// Item Meta
	public static function itemMeta($meta = array()) {
		$config 	= Factory::getConfig();
		$app 		= Factory::getApplication();
		$doc 		= Factory::getDocument();
		$menus   	= $app->getMenu();
		$menu 		= $menus->getActive();
		$title 		= '';

		//Title
		if (isset($meta['title']) && $meta['title']) {
			$title = $meta['title'];
		} else {
			if ($menu) {
				if($menu->getParams()->get('page_title', '')) {
					$title = $menu->getParams()->get('page_title');
				} else {
					$title = $menu->title;
				}
			}
		}
		
		//Include Site title
		$sitetitle = $title;
		if($config->get('sitename_pagetitles')==2) {
			$sitetitle = $title . ' | ' . $config->get('sitename');
		} elseif ($config->get('sitename_pagetitles')===1) {
			$sitetitle = $config->get('sitename') . ' | ' . $title;
		}

		$doc->setTitle($sitetitle);
		$doc->addCustomTag('<meta property="og:title" content="' . $title . '" />');

		//Keywords
		if (isset($meta['keywords']) && $meta['keywords']) {
			$keywords = $meta['keywords'];
			$doc->setMetadata('keywords', $keywords);
		} else {
			if ($menu) {
				if ($menu->getParams()->get('menu-meta_keywords')) {
					$keywords = $menu->getParams()->get('menu-meta_keywords');
					$doc->setMetadata('keywords', $keywords);
				}
			}
		}

		//Metadescription
		if (isset($meta['metadesc']) && $meta['metadesc']) {
			$metadesc = $meta['metadesc'];
			$doc->setDescription($metadesc);
			$doc->addCustomTag('<meta property="og:description" content="'. $metadesc .'" />');
		} else {
			if ($menu) {
				if ($menu->getParams()->get('menu-meta_description')) {
					$metadesc = $menu->getParams()->get('menu-meta_description');
					$doc->setDescription($menu->getParams()->get('menu-meta_description'));
					$doc->addCustomTag('<meta property="og:description" content="'. $metadesc .'" />');
				}
			}
		}

		//Robots
		if ($menu) {
			if ($menu->getParams()->get('robots'))
			{
				$doc->setMetadata('robots', $menu->getParams()->get('robots'));
			}
		}

		//Open Graph
		foreach ( $doc->_links as $k => $array ) {
			if ( $array['relation'] == 'canonical' ) {
				unset($doc->_links[$k]);
			}
		} // Remove Joomla canonical

		$doc->addCustomTag('<meta property="og:type" content="website" />');
		$doc->addCustomTag('<link rel="canonical" href="'.Uri::current().'" />');
		$doc->addCustomTag('<meta property="og:url" content="'.Uri::current().'" />');

		if (isset($meta['image']) && $meta['image']) {
			$doc->addCustomTag('<meta property="og:image" content="'. $meta['image'] .'" />');
			$doc->addCustomTag('<meta property="og:image:width" content="600" />');
			$doc->addCustomTag('<meta property="og:image:height" content="315" />');
		}
	}

	public static function isJson($string) {
		return is_string($string) && is_array(json_decode($string, true)) && (json_last_error() == JSON_ERROR_NONE) ? true : false;
	}

	public static function time_from_seconds($seconds) { 
		$h = floor($seconds / 3600); 
		$m = floor(((int)$seconds % 3600) / 60); 
		$s = (int)$seconds - ($h * 3600) - ($m * 60); 
		return sprintf('%02d:%02d', $m, $s); 
	}

	// Update //
	public static function compare($k) {
		return function ($a, $b) use($k) {
			if ($a->$k == $b->$k) 
				return 0;
			return $a->$k < $b->$k ? -1 : 1;
		};
	}
	
	public static function keySort($key, &$arr = array()) {
		usort($arr, self::compare($key));
		return $arr;
	}

	public static function formatTimeFromSeconds($seconds, $showLetters = false) {
		$h = floor($seconds / 3600); 
		$m = floor(($seconds % 3600) / 60); 
		$s = $seconds - ($h * 3600) - ($m * 60); 
		$time_string = '';
		if (!$showLetters) {
			if ($h > 0)
			$time_string .= sprintf("%02d:", $h);
			$time_string .= sprintf("%02d:%02d", $m, $s);
		} else {
			if ($h > 0)
			$time_string .= sprintf("%02dh:", $h);
			$time_string .= sprintf("%02dm:%02ds", $m, $s);
		}

		return $time_string;
	}

	public static function getThumbnail($image) {
		$params 	= ComponentHelper::getParams('com_splms');
		$size 		= strtolower($params->get('course_thumbnail', '480X300'));
		$filename 	= basename($image);
		$path 		= JPATH_BASE .'/'. dirname($image) . '/thumbs/' . File::stripExt($filename) . '_' . $size . '.' . File::getExt($filename);
		$src 		= Uri::base(true) . '/' . dirname($image) . '/thumbs/' . File::stripExt($filename) . '_' . $size . '.' . File::getExt($filename);

		if(File::exists($path)) {
			$thumb = $src;
		} else {
			$thumb = Uri::root() . $image;
		}

		return $thumb;
	}

	public static function getAvatar($userId = 0) {
		
		$app = Factory::getApplication();
		$template = $app->getTemplate();
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select(array('a.*'));
		$query->from($db->quoteName('#__user_profiles', 'a'));
		$query->where($db->quoteName('a.user_id') . ' = ' . $db->quote((int) $userId));
		$query->where($db->quoteName('a.profile_key') . ' LIKE \'profilelms.avatar%\'');
		$db->setQuery($query);
		$user = $db->loadObject();
	
		$override_path = JPATH_ROOT . '/templates/' . $template . '/images/splms/avatar.png';
		if(file_exists($override_path)) {
			$avatar = '/templates/' . $template . '/images/splms/avatar.png';
		} else {
			$avatar = '/components/com_splms/assets/images/avatar.png';
		}
		
		if(!empty($user)) {
			if(!empty($user->profile_value)) {
				$profile = json_decode($user->profile_value);
				if(isset($profile->avatar) && !empty($profile->avatar)) {
					$avatar = $profile->avatar;
				}
			}
		}

		return Uri::root(true) . $avatar;
	}

	/**
	 * Get the joomla version
	 * 
	 */
	public static function getJoomlaVersion($type = 'major')
	{
		$version = JVERSION;
		list ($major, $minor, $patch) = explode('.', $version);

		if (strpos($patch, '-') !== false)
		{
			$patch = explode('-', $patch)[0];
		}

		switch ($type)
		{
			case 'minor':
				return (int) $minor;
			case 'patch':
				return (int) $patch;
			case 'major':
			default:
				return (int) $major;
		}
	}

	/**
	 * Format a given price according to component parameters.
	 *
	 * @param  float|null $price -- The price to be formatted.
	 * @return string|null 		 -- Formatted price string or null if the input price is null or empty.
	 * @since  4.0.9
	 */
	public static function formatPrice($price)
	{
		if (is_null($price) || empty($price)) {
			return;
		}
		
		$params 		  = ComponentHelper::getParams('com_splms');
		$showNumberFormat = $params->get('use_number_format',0);
		$showRoundPrice	  = $params->get('show_rounds_price',0);

		if ($showRoundPrice) {
			$price = round((float)$price);
		}

		if($showNumberFormat)
		{
			return number_format((float)$price,2);
		}

		return $price;
	}
	
}