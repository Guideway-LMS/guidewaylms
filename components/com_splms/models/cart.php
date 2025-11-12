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
use Joomla\CMS\Router\Route;
use Joomla\CMS\MVC\Model\ItemModel;

class SplmsModelCart extends ItemModel {

    protected $_context = 'com_splms.cart';
    
    public function getItem($pk = null)
    {
       
    }

	public function getItems() {
		$cookie  = Factory::getApplication()->input->cookie;
		$cartItems = unserialize( base64_decode( $cookie->get('lmsOrders', base64_encode( serialize(array()) )) ) );
        $cartItems = (!is_array($cartItems) && !$cartItems) ? array() : $cartItems;
        
        $items = array();

        if(count($cartItems)) {
            foreach($cartItems as $cartItem) {
                $items[] = self::getCourses($cartItem['product']);
            }
        }

        return $items;
    }
    
    private static function getCourses($id) {
        $db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('a.*');
		$query->from($db->quoteName('#__splms_courses', 'a'));
		$query->where($db->quoteName('a.published')." = 1");
		$query->where($db->quoteName('a.id')." = " . (int) $id);
		$db->setQuery($query);
        $item = $db->loadObject();
        if(!empty($item)) {
            $item->url        = Route::_('index.php?option=com_splms&view=course&id=' . $item->id . ':' . $item->alias . SplmsHelper::getItemId('courses'));
            $item->thumbnail  = SplmsHelper::getThumbnail($item->image);
            $item->price      = SplmsHelper::formatPrice($item->price);
            $item->sale_price = SplmsHelper::formatPrice($item->sale_price) ?: $item->sale_price;

        }
        return $item;
    }
}