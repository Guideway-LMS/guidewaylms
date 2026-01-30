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
use Joomla\CMS\Version;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Access\Access;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Component\ComponentHelper;

$version = new Version();
$JoomlaVersion = $version->getShortVersion();

if (version_compare($JoomlaVersion, '4.0.0', '>='))
{
  JLoader::registerAlias('JHtmlSidebar', 'Joomla\CMS\HTML\Helpers\Sidebar');
}

class SplmsHelper {

  public static $extension = 'com_splms';

  public static function addSubmenu($submenu) {

    // check access if not then throw error
    $user = Factory::getUser();
    $is_admin = $user->authorise('core.admin');
    $groups = Access::getGroupsByUser($user->id);
    
    if (in_array(7, $groups) || in_array(8, $groups) || $is_admin) {
      JHtmlSidebar::addEntry(
        Text::_('COM_SPLMS_DASHBOARD'),
        'index.php?option=com_splms',
        $submenu == 'dashboard'
      );
    }

    if (in_array(7, $groups) || in_array(8, $groups) || $is_admin) {
      JHtmlSidebar::addEntry(
        Text::_('COM_SPLMS_TEACHERS'),
        'index.php?option=com_splms&view=teachers',
        $submenu == 'teachers'
      );
    }

    if (in_array(7, $groups) || in_array(8, $groups) || $is_admin) {
      JHtmlSidebar::addEntry(
        Text::_('COM_SPLMS_COURSE_CATEGORIES'),
        'index.php?option=com_splms&view=coursescategories',
        $submenu == 'coursescategories'
      );
    }

    JHtmlSidebar::addEntry(
      Text::_('COM_SPLMS_COURSES'),
      'index.php?option=com_splms&view=courses',
      $submenu == 'courses'
    );

    JHtmlSidebar::addEntry(
      Text::_('COM_SPLMS_LESSON_TOPICS'),
      'index.php?option=com_splms&view=lessiontopics',
      $submenu == 'lessiontopics'
    );

    JHtmlSidebar::addEntry(
      Text::_('COM_SPLMS_LESSONS'),
      'index.php?option=com_splms&view=lessons',
      $submenu == 'lessons'
    );

    // --- BLOCO ADICIONADO ---
    JHtmlSidebar::addEntry(
      'Trabalhos Enviados',
      'index.php?option=com_splms&view=submissions',
      $submenu == 'submissions'
    );
    // ------------------------

    JHtmlSidebar::addEntry(
      Text::_('COM_SPLMS_TITLE_QUIZQUESTIONS'),
      'index.php?option=com_splms&view=quizquestions',
      $submenu == 'quizquestions'
    );

    if (in_array(7, $groups) || in_array(8, $groups) || $is_admin) {
      JHtmlSidebar::addEntry(
        Text::_('COM_SPLMS_TITLE_QUIZRESULTS'),
        'index.php?option=com_splms&view=quizresults',
        $submenu == 'quizresults'
      );
    }

    if (in_array(7, $groups) || in_array(8, $groups) || $is_admin) {
      JHtmlSidebar::addEntry(
        Text::_('COM_SPLMS_TITLE_CERTIFICATES'),
        'index.php?option=com_splms&view=certificates',
        $submenu == 'certificates'
      );
    }

    if (in_array(7, $groups) || in_array(8, $groups) || $is_admin) {
      JHtmlSidebar::addEntry(
        Text::_('COM_SPLMS_SPEAKERS'),
        'index.php?option=com_splms&view=speakers',
        $submenu == 'speakers'
      );
    }

    if (in_array(7, $groups) || in_array(8, $groups) || $is_admin) {
      JHtmlSidebar::addEntry(
        Text::_('COM_SPLMS_EVENT_CATEGORIES'),
        'index.php?option=com_splms&view=eventcategories',
        $submenu == 'eventcategories'
      );
    }

    if (in_array(7, $groups) || in_array(8, $groups) || $is_admin) {
      JHtmlSidebar::addEntry(
        Text::_('COM_SPLMS_EVENTS'),
        'index.php?option=com_splms&view=events',
        $submenu == 'events'
      );
    }

    if (in_array(7, $groups) || in_array(8, $groups) || $is_admin) {
      JHtmlSidebar::addEntry(
        Text::_('COM_SPLMS_REVIEWS'),
        'index.php?option=com_splms&view=reviews',
        $submenu == 'reviews'
      );
    }
    
    if (in_array(7, $groups) || in_array(8, $groups) || $is_admin) {
      JHtmlSidebar::addEntry(
        'Fórum de Dúvidas',
        'index.php?option=com_splms&view=forums',
        $submenu == 'forums',
                'comments'
      );
    }

    if (in_array(7, $groups) || in_array(8, $groups) || $is_admin) {
      JHtmlSidebar::addEntry(
        Text::_('COM_SPLMS_ORDERS'),
        'index.php?option=com_splms&view=orders',
        $submenu == 'orders'
      );
    }
  }
  // check access if not then redirect to the courses page
  public static function hasVisitAccess() {
    $user     = Factory::getUser();
    $is_admin   = $user->authorise('core.admin');
    $groups   = Access::getGroupsByUser($user->id);
    if (!in_array(7, $groups) && !in_array(8, $groups) && !$is_admin) {
      $app = Factory::getApplication();
      $app->redirect(Route::_('index.php?option=com_splms&view=courses', false));
    }
  }

  public static function getActions($messageId = 0) {
    $result = new CMSObject();

    if (empty($messageId)) {
      $assetName = 'com_splms';
    } else {
      $assetName = 'com_splms.course.'.(int) $messageId;
    }

    //$actions = Access::getActions('com_splms', 'component');
    $actions = Access::getActionsFromFile(
      JPATH_ADMINISTRATOR . '/components/com_splms/access.xml', '/access/section[@name="component"]/'
    );
    
    foreach ($actions as $action) {
      $result->set($action->name, Factory::getUser()->authorise($action->name, $assetName));
    }

    return $result;
  }

  // Create thumbs
  public static function createThumbs($src, $folder, $base_name, $ext, $sizes = array()) {

    list($originalWidth, $originalHeight) = getimagesize($src);

    switch($ext) {
      case 'bmp': $img = imagecreatefromwbmp($src); break;
      case 'gif': $img = imagecreatefromgif($src); break;
      case 'jpg': $img = imagecreatefromjpeg($src); break;
      case 'jpeg': $img = imagecreatefromjpeg($src); break;
      case 'png': $img = imagecreatefrompng($src); break;
    }

    if(count($sizes)) {
      $output = array();

      if($base_name) {
        $output['original'] = $folder . '/' . $base_name . '.' . $ext;
      }

      foreach ($sizes as $key => $size) {
        $targetWidth = $size[0];
        $targetHeight = $size[1];
        $ratio_thumb = $targetWidth/$targetHeight;
        $ratio_original = $originalWidth/$originalHeight;

        if ($ratio_original >= $ratio_thumb) {
          $height = $originalHeight;
          $width = ceil(($height*$targetWidth)/$targetHeight);
          $x = ceil(($originalWidth-$width)/2);
          $y = 0;
        } else {
          $width = $originalWidth;
          $height = ceil(($width*$targetHeight)/$targetWidth);
          $y = ceil(($originalHeight-$height)/2);
          $x = 0;
        }

        $new = imagecreatetruecolor($targetWidth, $targetHeight);

        if($ext == "gif" or $ext == "png") {
          imagecolortransparent($new, imagecolorallocatealpha($new, 0, 0, 0, 127));
          imagealphablending($new, false);
          imagesavealpha($new, true);
        }

        imagecopyresampled($new, $img, 0, 0, $x, $y, $targetWidth, $targetHeight, $width, $height);

        if($base_name) {
          $dest = dirname($src) . '/' . $base_name . '_' . $key . '.' . $ext;
          $output[$key] = $folder . '/' . $base_name . '_' . $key . '.' . $ext;
        } else {
          $dest = $folder . '/' . $key . '.' . $ext;
        }

        switch($ext){
          case 'bmp': imagewbmp($new, $dest); break;
          case 'gif': imagegif($new, $dest); break;
          case 'jpg': imagejpeg($new, $dest); break;
          case 'jpeg': imagejpeg($new, $dest); break;
          case 'png': imagepng($new, $dest); break;
        }
      }

      return $output;
    }

    return false;
  }

  // Get Orders
  public static function getOrders() {
    $db = Factory::getDbo();
    $query = $db->getQuery(true);
    $query->select('COUNT(id)');
    $query->from($db->quoteName('#__splms_orders'));
    $query->where($db->quoteName('published')." = 1");
    $query->order('ordering DESC');
    $db->setQuery($query);
    $results = $db->loadResult();

    return $results;
  }

  // Get Orders
  public static function getCourses() {
    $db = Factory::getDbo();
    $query = $db->getQuery(true);
    $query->select('COUNT(id)');
    $query->from($db->quoteName('#__splms_courses'));
    $query->where($db->quoteName('published')." = 1");
    $query->order('ordering DESC');
    $db->setQuery($query);
    $results = $db->loadResult();

    return $results;
  }

  // Get Lessons
  public static function getLessons() {
    $db = Factory::getDbo();
    $query = $db->getQuery(true);
    $query->select('COUNT(id)');
    $query->from($db->quoteName('#__splms_lessons'));
    $query->where($db->quoteName('published')." = 1");
    $query->order('ordering DESC');
    $db->setQuery($query);
    $results = $db->loadResult();

    return $results;
  }

  // Get Users
  public static function getUsers() {
    $db = Factory::getDbo();
    $query = $db->getQuery(true);
    $query->select('COUNT(id)');
    $query->from($db->quoteName('#__users'));
    $query->where($db->quoteName('block')." = 0");
    $db->setQuery($query);
    $results = $db->loadResult();

    return $results;
  }

  //Get total sales by day
  public static function getTotalSales() {

    $db = Factory::getDbo();
    $query = $db->getQuery(true);
    $query->select('SUM(order_payment_price)');
    $query->from($db->quoteName('#__splms_orders'));
    $query->where($db->quoteName('published')." = 1");
    $db->setQuery($query);
    $results = $db->loadResult();

    return round($results,2);
  }

  //Get total sales by day
  public static function getSales($day, $month, $year) {

    $db = Factory::getDbo();
    $query = $db->getQuery(true);
    $query->select('SUM(order_payment_price)');
    $query->from($db->quoteName('#__splms_orders'));
    $query->where('DAY(created) = ' . $day);
    $query->where('MONTH(created) = ' . $month);
    $query->where('YEAR(created) = ' . $year);
    $query->where($db->quoteName('published')." = 1");
    $db->setQuery($query);
    $results = $db->loadResult();

    return $results;
  }

  //Orders List
  public static function getOrdersList() {
    $db = Factory::getDbo();
    $query = $db->getQuery(true);
    $query->select($db->quoteName(array('a.splms_order_id', 'a.id', 'a.created', 'a.order_payment_price', 'b.title', 'b.id')));
    $query->from($db->quoteName('#__splms_orders', 'a'));
    $query->join('LEFT', $db->quoteName('#__splms_courses', 'b') . ' ON (' . $db->quoteName('a.id') . ' = ' . $db->quoteName('b.id') . ')');
    $query->where($db->quoteName('a.published')." = 1");
    $query->setLimit('5');
    $query->order('a.ordering DESC');
    $db->setQuery($query);
    $results = $db->loadObjectList();

    return $results;
  }
  // Get Courses List
  public static function getCoursesList() {
    $db = Factory::getDbo();
    $query = $db->getQuery(true);
    $query->select($db->quoteName(array('id', 'title', 'created', 'price')));
    $query->from($db->quoteName('#__splms_courses'));
    $query->where($db->quoteName('published')." = 1");
    $query->setLimit('5');
    $query->order('ordering DESC');
    $db->setQuery($query);
    $results = $db->loadObjectList();

    return $results;
  }

  // Generate Currency
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

  // Get component version
  public static function getVersion() {
    $db = Factory::getDbo();
    $query = $db->getQuery(true)
    ->select('e.manifest_cache')
    ->select($db->quoteName('e.manifest_cache'))
    ->from($db->quoteName('#__extensions', 'e'))
    ->where($db->quoteName('e.element') . ' = ' . $db->quote('com_splms'));
    $db->setQuery($query);
    $manifest_cache = json_decode($db->loadResult());
    if(isset($manifest_cache->version) && $manifest_cache->version) {
      return $manifest_cache->version;
    }
    
    return '1.0';
  }
  /**
   * Get the joomla version
   * */
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

}