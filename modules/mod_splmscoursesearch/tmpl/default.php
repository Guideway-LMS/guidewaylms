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

$app = Factory::getApplication();
$input = $app->input;
$terms    = htmlspecialchars($input->get('terms', '', 'STRING'));
?>

<div class="<?php echo $moduleclass_sfx; ?>">
	<form action="<?php echo Route::_('index.php?option=com_splms&view=courses' . SplmsHelper::getItemid('courses')); ?>">
		<div class="mod-splms-course-search">
			<div class="course-search-inner">
				<input type="text" name="terms" class="splms-coursesearch-input" placeholder="<?php echo $params->get('placeholder'); ?>" value="<?php echo $terms; ?>" />
				<span class="splms-course-search-icons"><i class="splms-icon-search"></i></span>
			</div>
			<div class="splms-course-search-results"></div>
		</div>
	</form>
</div>