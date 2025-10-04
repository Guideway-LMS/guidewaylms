<?php
/**
 * @package     SP LMS
 * @subpackage	com_splms
 *
 * @copyright   Copyright (C) 2010 - 2021 JoomShaper. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

 // No direct access to this file
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Component\ComponentHelper;

require_once JPATH_COMPONENT . '/helpers/helper.php';
jimport('joomla.application.component.helper');
$params = ComponentHelper::getParams('com_splms');
$currency = explode(':', $params->get('currency', 'USD:$'));

$doc = Factory::getDocument();
HTMLHelper::_('jquery.framework');
$doc->addStylesheet( Uri::base(true) . '/components/com_splms/assets/css/font-awesome.min.css' );
$doc->addScript( Uri::base(true) . '/components/com_splms/assets/js/Chart.min.js' );

// Orders
$total_orders = SplmsHelper::getOrders();
$total_courses = SplmsHelper::getCourses();
$total_lessons = SplmsHelper::getLessons();
$total_earns_subtraction = SplmsHelper::getTotalSales();
$users = SplmsHelper::getUsers();

?>

<div id="splms" class="splms-dashboard">

	<div class="splms-sidebar">
		<ul class="nav nav-list">
			<li class="nav-header"><strong><i class="fa fa-graduation-cap"></i><span class="hidden-xs"> <?php echo Text::_('COM_SPLMS'); ?></span></strong></li>
			<li class="active"><a href="index.php?option=com_splms"><i class="fa fa-tachometer"></i><span class="hidden-xs"> <?php echo Text::_('COM_SPLMS_DASHBOARD'); ?></span></a></li>
			<li><a href="index.php?option=com_splms&amp;view=teachers"><i class="fa fa-users"></i><span class="hidden-xs"> <?php echo Text::_('COM_SPLMS_TEACHERS'); ?></span></a></li>
			<li><a href="index.php?option=com_splms&amp;view=coursescategories"><i class="fa fa-folder-open-o"></i><span class="hidden-xs"> <?php echo Text::_('COM_SPLMS_COURSE_CATEGORIES'); ?></span></a></li>
			<li><a href="index.php?option=com_splms&amp;view=courses"><i class="fa fa-book"></i><span class="hidden-xs"> <?php echo Text::_('COM_SPLMS_COURSES'); ?></span></a></li>
			<li><a href="index.php?option=com_splms&amp;view=lessons"><i class="fa fa-bars"></i><span class="hidden-xs"> <?php echo Text::_('COM_SPLMS_LESSONS'); ?></span></a></li>

			<li><a href="index.php?option=com_splms&amp;view=quizquestions"><i class="fa fa-check-circle-o"></i><span class="hidden-xs"> <?php echo Text::_('COM_SPLMS_TITLE_QUIZQUESTIONS'); ?></span></a></li>
			<li><a href="index.php?option=com_splms&amp;view=quizresults"><i class="fa fa-pie-chart"></i><span class="hidden-xs"> <?php echo Text::_('COM_SPLMS_TITLE_QUIZRESULTS'); ?></span></a></li>
			<li><a href="index.php?option=com_splms&amp;view=certificates"><i class="fa fa-bookmark"></i><span class="hidden-xs"> <?php echo Text::_('COM_SPLMS_TITLE_CERTIFICATES'); ?></span></a></li>

			<li><a href="index.php?option=com_splms&amp;view=orders"><i class="fa fa-line-chart"></i><span class="hidden-xs"> <?php echo Text::_('COM_SPLMS_ORDERS'); ?></span></a></li>
			<li class="divider hidden-xs"></li>
			<li><a href="index.php?option=com_splms&amp;view=speakers"><i class="fa fa-users"></i><span class="hidden-xs"> <?php echo Text::_('COM_SPLMS_SPEAKERS'); ?></span></a></li>
			<li><a href="index.php?option=com_splms&amp;view=eventcategories"><i class="fa fa-folder-open-o"></i><span class="hidden-xs"> <?php echo Text::_('COM_SPLMS_EVENT_CATEGORIES'); ?></span></a></li>
			<li><a href="index.php?option=com_splms&amp;view=events"><i class="fa fa-bullhorn"></i><span class="hidden-xs"> <?php echo Text::_('COM_SPLMS_EVENTS'); ?></a></span></li>

			<li class="divider hidden-xs"></li>
			<li><a href="index.php?option=com_config&amp;view=component&amp;component=com_splms"><i class="fa fa-cog"></i><span class="hidden-xs"> <?php echo Text::_('COM_SPLMS_SETTINGS'); ?></span></a></li>
		</ul>
	</div>

	<div class="splms-dashboard-content">
		<div class="splms-row">
			<div class="splms-col-sm-12 splms-col-md-6">
				<div class="splms-row">

					<div class="splms-col-sm-12">
						<div class="total-earnings splms-box">
							<i class="fa fa-users"></i>
							<span><?php echo $users; ?></span>
							<?php echo Text::_('COM_SPLMS_TOTAL_STUDENTS'); ?>
						</div>
					</div>

					<div class="splms-col-xs-6 splms-col-sm-6">
						<div class="total-earnings splms-box">
							<i class="fa fa-usd"></i>
							<span><?php echo SplmsHelper::getPrice($total_earns_subtraction); ?></span>
							<?php echo Text::_('COM_SPLMS_TOTAL_EARNINGS'); ?>
						</div>
					</div>

					<div class="splms-col-xs-6 splms-col-sm-6">
						<div class="total-orders splms-box">
							<i class="fa fa-bar-chart"></i>
							<span><?php echo $total_orders; ?></span>
							<?php echo Text::_('COM_SPLMS_TOTAL_ORDERS'); ?>
						</div>
					</div>

					<div class="splms-col-xs-6 splms-col-sm-6">
						<div class="total-courses splms-box">
							<i class="fa fa-book"></i>
							<span><?php echo $total_courses; ?></span>
							<?php echo Text::_('COM_SPLMS_TOTAL_COURSES'); ?>
						</div>
					</div>

					<div class="splms-col-xs-6 splms-col-sm-6">
						<div class="total-lessons splms-box">
							<i class="fa fa-bars"></i>
							<span><?php echo $total_lessons; ?></span>
							<?php echo Text::_('COM_SPLMS_TOTAL_LESSONS'); ?>
						</div>
					</div>
				</div>
			</div>

			<div class="splms-col-sm-12 splms-col-md-6">
				<div class="splms-dashboard-canvas">
					<div>
						<canvas id="canvas" height="250"></canvas>
					</div>
				</div>

				<?php

				$currentTime = new Date('now');

				$jnow 		= Factory::getDate();
				$month 		= $jnow->format('m');
				$year 		= $jnow->format('Y');

				$days = date('t', mktime(0, 0, 0, $month, 1, $year));
				$data = '';

				for ($i=1; $i<=$days; $i++) {
					$data .= '"' . SplmsHelper::getSales($i, $month, $year) . '",';
				}

				$data = rtrim($data, ',');

				$labels = '';
				$month = $jnow->format('M');

				for ($i=1; $i<=$days; $i++) {
					$labels .= '"' . $month .' - ' . $i . '",';
				}

				$labels = rtrim($labels, ',');

				?>
			</div>

			<script>
				var randomScalingFactor = function(){ return Math.round(Math.random()*100)};
				var lineChartData = {
					labels : [<?php echo $labels; ?>],
					datasets : [
					{
						fillColor : "rgba(0,136,206,0.5)",
						strokeColor : "#08c",
						pointColor : "#08c",
						pointStrokeColor : "#eee",
						pointHighlightFill : "#eee",
						pointHighlightStroke : "rgba(151,187,205,1)",
						data : [<?php echo $data; ?>]
					}
					]

				}

				window.onload = function(){
					var ctx = document.getElementById("canvas").getContext("2d");
					window.myLine = new Chart(ctx).Line(lineChartData, {
						responsive: true,
						maintainAspectRatio: false
					});
				}
			</script>

		</div>

		<div class="splms-row">
			<div class="splms-col-sm-6">
				<div class="latest-courses splms-box">
					<h3><?php echo Text::_('COM_SPLMS_LATEST_COURSES'); ?></h3>
					<ul>
						<?php
						$courses = SplmsHelper::getCoursesList();

						foreach ($courses as $course) {
							echo '<li><a href="index.php?option=com_splms&view=course&id='. $course->splms_course_id .'">' . $course->title . '</a><small class="created">'. HTMLHelper::_('date', $course->created_on, Text::_('DATE_FORMAT_LC3')) .'</small></li>';
						}
						?>
					</ul>
				</div>
			</div>

			<div class="splms-col-sm-6">
				<div class="recent-orders splms-box">
					<h3><?php echo Text::_('COM_SPLMS_RECENT_ORDERS'); ?></h3>
					<ul>
						<?php
						$orders = SplmsHelper::getOrdersList();

						foreach ($orders as $order) {
							echo '<li><a href="index.php?option=com_splms&view=course&id='. $order->splms_order_id .'">' . $order->title . '<strong class="pull-right">' . SplmsHelper::getPrice($order->order_payment_price) . '</strong></a><small class="created">'. HTMLHelper::_('date', $course->created_on, Text::_('DATE_FORMAT_LC3')) .'</small></li>';
						}
						?>
					</ul>
				</div>
			</div>
		</div>

		<div class="splms-box splms-dashboard-footer">
			<div class="splms-row">
				<div class="splms-col-sm-6">
					<p>&copy; 2010 - <?php echo date('Y'); ?> JoomShaper. All Rights Reserved | License: <a href="http://www.gnu.org/copyleft/gpl.html">GNU General Public License</a></p>
				</div>
				<div class="splms-col-sm-6 text-right">
					<p>Version: <?php echo SplmsHelper::getVersion(); ?></p>
				</div>
			</div>
		</div>


	</div>
</div>
