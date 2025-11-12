<?php
/**
* @package com_splms
* @subpackage  mod_splmspersons
*
* @author JoomShaper http://www.joomshaper.com
* @copyright Copyright (c) 2010 - 2024 JoomShaper
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/

// No Direct Access
defined ('_JEXEC') or die('Resticted Aceess');

use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Uri\Uri;

jimport('joomla.filesystem.file');

//Joomla Component Helper & Get LMS Params
jimport('joomla.application.component.helper');
$splmsparams = ComponentHelper::getParams('com_splms');

// Get image thumb
$thumb_size = strtolower($splmsparams->get('course_thumbnail_small', '100X60'));
?>

<div class="mod-splms-teachers <?php echo $moduleclass_sfx; ?>">
    <div class="splms splms-teachers-list">
        <div class="splms-row">
            <?php foreach (array_chunk($items, $columns) as $items) { ?>
                <?php foreach ($items as $item) { ?>
                    <div class="splms-col-sm-6 splms-col-md-<?php echo round(12 / $columns); ?>">
                        <div class="mod-splms-teacher">
                            <a href="<?php echo $item->url; ?>">
                                <?php
                                $filename = basename($item->image);
                                $path = JPATH_BASE . '/' . dirname($item->image) . '/thumbs/' . File::stripExt($filename) . '_' . $thumb_size . '.' . File::getExt($filename);
                                $src = Uri::base(true) . '/' . dirname($item->image) . '/thumbs/' . File::stripExt($filename) . '_' . $thumb_size . '.' . File::getExt($filename);

                                if (File::exists($path)) {
                                    $thumb = $src;
                                } else {
                                    $thumb = $item->image;
                                }
                                ?>
                                <img src="<?php echo $thumb; ?>" class="splms-teacher-img splms-img-responsive" alt="<?php echo $item->title; ?>">
                            </a>
                            <h4 class="splms-teacher-title">
                                <a href="<?php echo $item->url; ?>"><?php echo $item->title; ?></a>
                            </h4>
                            <p><?php echo $item->designation; ?></p>
                        </div><!-- /.mod-splms-teacher -->
                    </div>
                <?php } ?>
            <?php } ?>
        </div> <!-- /.splms-row -->
    </div>
</div>

<?php


