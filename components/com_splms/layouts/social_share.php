<?php
/**
 * @package     SP LMS
 *
 * @copyright   Copyright (C) 2010 - 2021 JoomShaper. All rights reserved.
 * @license     GNU General Public License version 2 or later.
 */

defined('_JEXEC') or die('Restricted Access');

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;

$params = Factory::getApplication('com_splms')->getParams();
$url 		= $displayData['url'];
$title 		= $displayData['title'];

$root       = Uri::base();
$root       = new Uri($root);
$link   	= $root->getScheme() . '://' . $root->getHost() . $url;

$social_share_media = $params->get('social_share_media', array());
?>

<ul class="splms-social-share-icons">
	<?php if(in_array('facebook', $social_share_media)) : ?>
		<li>
			<a class="social-facebook" href="https://www.facebook.com/sharer.php?u=<?php echo $link;?>" target="_blank" rel="nofollow"><i class="splms-icon-facebook"></i></a>
		</li>
	<?php endif; ?>

	<?php if(in_array('twitter', $social_share_media)) : ?>
		<li>
			<a class="social-twitter" href="https://twitter.com/share?url=<?php echo $link; ?>&amp;text=<?php echo str_replace(" ", "%20", $title); ?>" target="_blank" rel="nofollow"><i class="splms-icon-twitter"></i></a>
		</li>
	<?php endif; ?>

	<?php if(in_array('linkedin', $social_share_media)) : ?>
		<li>
			<a class="social-linkedin" href="https://www.linkedin.com/shareArticle?mini=true&url=<?php echo $link; ?>" target="_blank" rel="nofollow"><i class="splms-icon-linkedin"></i></a>
		</li>
	<?php endif; ?>

	<?php if(in_array('reddit', $social_share_media)) : ?>
		<li>
			<a class="social-reddit" href="https://www.reddit.com/submit?url=<?php echo $link; ?>&title=<?php echo urlencode($title); ?>" target="_blank" rel="nofollow"><i class="splms-icon-reddit"></i></a>
		</li>
	<?php endif; ?>

	<?php if(in_array('digg', $social_share_media)) : ?>
		<li>
			<a class="social-digg" href="http://digg.com/submit?phase=2&amp;url=<?php echo $link; ?>" target="_blank" rel="nofollow"><i class="splms-icon-digg"></i></a>
		</li>
	<?php endif; ?>

	<?php if(in_array('whatsapp', $social_share_media)) : ?>
		<li>
			<a class="social-whatsapp" href="https://wa.me/?text=<?php echo urlencode($title . ' ' .$link); ?>" target="_blank" rel="nofollow"><i class="splms-icon-whatsapp"></i></a>
		</li>
	<?php endif; ?>

	<?php if(in_array('vk', $social_share_media)) : ?>
		<li>
			<a class="social-vk" href="https://vk.com/share.php?url=<?php echo $link; ?>&title=<?php echo urlencode($title); ?>" target="_blank" rel="nofollow"><i class="splms-icon-vk"></i></a>
		</li>
	<?php endif; ?>
</ul>