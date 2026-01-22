<?php
/**
 * @package     SP Movie Databse
 *
 * @copyright   Copyright (C) 2010 - 2021 JoomShaper. All rights reserved.
 * @license     GNU General Public License version 2 or later.
 */

defined('_JEXEC') or die('Restricted Access');

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\ItemModel;

class SplmsModelReview extends ItemModel{

	public function getItem($pk = null)
	{
	}
	public function __construct($config = array()){
		parent::__construct($config);
	}

	public function storeReview($item_id = 0, $review = '', $rating = 1) {
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$columns = array('course_id', 'review', 'rating', 'created_by', 'created', 'published','modified');
		$values = array($db->quote($item_id), $db->quote($review), $db->quote($rating), Factory::getUser()->id, $db->quote(Factory::getDate()), 1,$db->quote(Factory::getDate()));
		$query
		    ->insert($db->quoteName('#__splms_reviews'))
		    ->columns($db->quoteName($columns))
		    ->values(implode(',', $values));
		 
		$db->setQuery($query);
		$db->execute();

		return $db->insertid();
	}

	public function updateReview($review = '', $rating = 1, $review_id = '') {
		$db = Factory::getDbo();
		$query = $db->getQuery(true);

		$fields = array(
			$db->quoteName('review') . ' = ' . $db->quote($review),
			$db->quoteName('rating') . ' = ' . $db->quote($rating),
			);

		$conditions = array(
			$db->quoteName('id') . ' = ' . $db->quote($review_id),
			$db->quoteName('created_by') . ' = ' . $db->quote(Factory::getUser()->id),
		);
		$query->update($db->quoteName('#__splms_reviews'))->set($fields)->where($conditions);
		$db->setQuery($query);
		$db->execute();
	}

	public function getReview($review_id = 0) {
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select( array('a.*', 'b.email', 'b.name') );
		//$query->select('a.*');
	    $query->from($db->quoteName('#__splms_reviews', 'a'));
	    $query->join('LEFT', $db->quoteName('#__users', 'b') . ' ON (' . $db->quoteName('a.created_by') . ' = ' . $db->quoteName('b.id') . ')');
	    $query->where($db->quoteName('a.id') . ' = ' . $db->quote($review_id));
	    $query->order($db->quoteName('a.created') . ' DESC');
	    
	    $db->setQuery($query);

	    $review = $db->loadObject();

	    if(count((array)$review)) {
	    	$review->gravatar = md5($review->email);
	    	$review->created_date = SplmsHelper::timeAgo($review->created);
	    	return $review;
	    }

	    return false;
	}

	public function getRatings($item_id) {
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select( array('COUNT(a.rating) AS count', 'SUM(a.rating) AS total') );
	    $query->from($db->quoteName('#__splms_reviews', 'a'));
	    $query->where($db->quoteName('a.course_id') . ' = ' . $db->quote($item_id));
	    $db->setQuery($query);
		
		return $db->loadObject();
	}
}