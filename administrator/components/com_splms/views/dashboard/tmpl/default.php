<?php

/**
* @package com_splms
* @author JoomShaper http://www.joomshaper.com
* @copyright Copyright (c) 2010 - 2024 JoomShaper
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

// No direct access
defined('_JEXEC') or die('Restricted access');

$params = ComponentHelper::getParams('com_splms');
$currency = explode(':', $params->get('currency', 'USD:$'));

$doc = Factory::getDocument();
HTMLHelper::_('jquery.framework');
$doc->addStylesheet( Uri::base(true) . '/components/com_splms/assets/css/font-awesome.min.css' );
$doc->addScript( Uri::base(true) . '/components/com_splms/assets/js/Chart.min.js' );

$totalOrdersIcon = SplmsHelper::getJoomlaVersion() < 4 ? 'fa fa-bar-chart' : 'fas fa-bar-chart';
$totalEarnings   = SplmsHelper::getJoomlaVersion() < 4 ? 'fa fa-usd' : 'fas fa-money';
?>
<div class="splms-view splms-dashboard">
    <?php if (SplmsHelper::getJoomlaVersion() < 4 && !empty( $this->sidebar)) { ?>
    <div id="j-sidebar-container" class="span2">
        <?php echo $this->sidebar; ?>
    </div>
    <div id="j-main-container" class="span10">
        <!-- <div class="splms-dashboard-content"> -->
        <?php } else { ?>
        <div id="j-main-container">
            <?php } ?>

            <div id="splms">
                <div class="splms-row">
                    <div class="splms-col-sm-6">
                        <div class="splms-row">
                            <div class="splms-col-sm-12">
                                <div class="total-earnings splms-box">
                                    <i class="fa fa-users"></i>
                                    <span><?php echo $this->students; ?></span>
                                    <?php echo Text::_('COM_SPLMS_TOTAL_STUDENTS'); ?>
                                </div>
                            </div>

                            <div class="splms-col-sm-6">
                                <div class="total-earnings splms-box">
                                    <i class="<?php echo $totalEarnings; ?>"></i>
                                    <span><?php echo $this->earnings; ?></span>
                                    <?php echo Text::_('COM_SPLMS_TOTAL_EARNINGS'); ?>
                                </div>
                            </div>

                            <div class="splms-col-sm-6">
                                <div class="total-orders splms-box">
                                    <i class="<?php echo $totalOrdersIcon; ?>"></i>
                                    <span><?php echo $this->orders; ?></span>
                                    <?php echo Text::_('COM_SPLMS_TOTAL_ORDERS'); ?>
                                </div>
                            </div>

                            <div class="splms-col-sm-6">
                                <div class="total-courses splms-box">
                                    <i class="fa fa-book"></i>
                                    <span><?php echo $this->courses; ?></span>
                                    <?php echo Text::_('COM_SPLMS_TOTAL_COURSES'); ?>
                                </div>
                            </div>

                            <div class="splms-col-sm-6">
                                <div class="total-lessons splms-box">
                                    <i class="fa fa-bars"></i>
                                    <span><?php echo $this->lessons; ?></span>
                                    <?php echo Text::_('COM_SPLMS_TOTAL_LESSONS'); ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="splms-col-sm-6">
                        <div class="splms-dashboard-canvas splms-box">
                            <div>
                                <canvas id="canvas" height="250"></canvas>
                            </div>
                        </div>

                        <?php
        $currentTime = new Date('now');
        $jnow = Factory::getDate();
        $month = $jnow->format('m');
        $year = $jnow->format('Y');
        $days = cal_days_in_month(CAL_GREGORIAN, $month, $year);
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

                        <script>
                        var randomScalingFactor = function() {
                            return Math.round(Math.random() * 100)
                        };
                        var lineChartData = {
                            labels: [<?php echo $labels; ?>],
                            datasets: [{
                                fillColor: "rgba(0,136,206,0.5)",
                                strokeColor: "#08c",
                                pointColor: "#08c",
                                pointStrokeColor: "#eee",
                                pointHighlightFill: "#eee",
                                pointHighlightStroke: "rgba(151,187,205,1)",
                                data: [<?php echo $data; ?>]
                            }]

                        }

                        window.onload = function() {
                            var ctx = document.getElementById("canvas").getContext("2d");
                            window.myLine = new Chart(ctx).Line(lineChartData, {
                                responsive: true,
                                maintainAspectRatio: false
                            });
                        }
                        </script>
                    </div>
                </div>

                <div class="splms-row">
                    <div class="splms-col-sm-6">
                        <div class="latest-courses splms-box">
                            <h3><?php echo Text::_('COM_SPLMS_LATEST_COURSES'); ?></h3>
                            <ul>
                                <?php
            foreach ($this->courseList as $course) {
              echo '<li><a href="index.php?option=com_splms&view=course&layout=edit&id='. $course->id .'">' . $course->title . '</a><small class="created">'. HTMLHelper::_('date', $course->created, Text::_('DATE_FORMAT_LC3')) .'</small></li>';
            }
            ?>
                            </ul>
                        </div>
                    </div>

                    <div class="splms-col-sm-6">
                        <div class="recent-orders splms-box">
                            <h3><?php echo Text::_('COM_SPLMS_RECENT_ORDERS'); ?></h3>
                            <?php if(isset($this->orderList) && count($this->orderList)) { ?>
                            <ul>
                                <?php foreach ($this->orderList as $order) {
                echo '<li><a href="index.php?option=com_splms&view=course&layout=edit&id='. $order->id .'">' . $order->title . '<strong class="pull-right">' . SplmsHelper::getPrice($order->order_payment_price) . '</strong></a><small class="created">'. HTMLHelper::_('date', $course->created, Text::_('DATE_FORMAT_LC3')) .'</small></li>';
              } ?>
                            </ul>
                            <?php } else { ?>
                            <p class="alert alert-info"><?php echo Text::_('COM_SPLMS_COMMON_NORECORDS'); ?></p>
                            <?php } ?>

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
        </div> <!-- /.splms-dashboard -->