<?php
/**
 * @package     SP Movie Databse
 *
 * @copyright   Copyright (C) 2010 - 2021 JoomShaper. All rights reserved.
 * @license     GNU General Public License version 2 or later.
 */

defined('_JEXEC') or die('Restricted Access');

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;

$app = Factory::getApplication('com_splms');
$params = $app->getParams();
$assets_path = Uri::root(true) . '/components/com_splms/assets/';

$avatar = $displayData['avatar'];
$title = $displayData['title'];
$cssClass = isset($displayData['cssClass']) ? $displayData['cssClass'] : 'splms-col-xs-6 splms-col-sm-4 splms-col-md-2';
?>

<div class="<?php echo $cssClass; ?>">
    <div class="splms-profile-avatar">
        <img class="splms-avatar" src="<?php echo $avatar; ?>" alt="<?php echo $title; ?>" />
        <h4 class="splms-profile-title"><?php echo $title; ?></h4>
    </div>
</div>
