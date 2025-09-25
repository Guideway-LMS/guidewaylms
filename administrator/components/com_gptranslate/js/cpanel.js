/**
 * CPanel main JS APP class, manage chart generation
 * 
 * @package GPTRANSLATE::CPANEL::administrator::components::com_gptranslate 
 * @subpackage js 
 * @author Joomla! Extensions Store
 * @copyright (C)2013 Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html  
*/
//'use strict';
(function ($) {
    var CPanel = function(targetSelector) {
    	/**
		 * Reference to ChartJS lib object
		 * 
		 * @access private
		 * @var Object
		 */
    	var chartJS = new Array();
    	
    	/**
		 * Charts options
		 * 
		 * @access private
		 * @var Object
		 */
    	var chartOptions = {animation:true, scaleFontSize: 11, scaleOverride: true, scaleSteps:1, scaleStepWidth: 50};
    	
    	/**
		 * Chart data to render, copy from global injected scope
		 * 
		 * @access private
		 * @var Object
		 */
    	var chartData = {};
    	
    	/**
		 * Element target to render chart
		 * 
		 * @access private
		 * @var HTMLElement
		 */
    	var context;

        /**
		 * Interact with ChartJS lib to generate charts
		 * 
		 * @access private
		 * @return Void
		 */
        function generateLineChart(context, elem, animation) {
        	var elemIndex = $(elem).attr('id');
        	chartData = {};
        	
        	// Instance Chart object lib
        	chartJS[elemIndex] = new Chart(context);
        	
        	// Max value encountered
        	var maxValue = 10;
        	
        	// Normalize chart data to render
        	chartData.labels = new Array();
        	chartData.datasets = new Array();
        	var subDataSet = new Array();
            $.each(gptranslateChartData[elemIndex], function(label, value){
            	var labelSuffix = label.toUpperCase();
            	
            	if(window['COM_GPTRANSLATE_' + labelSuffix + '_CHART'] !== undefined) {
            		chartData.labels[chartData.labels.length] = eval('COM_GPTRANSLATE_' + labelSuffix + '_CHART');
            	} else {
            		chartData.labels[chartData.labels.length] = labelSuffix;
            	}
            	subDataSet[subDataSet.length] = parseInt(value);
            	if(value > maxValue) {
            		maxValue = value;
            	}
            });
            
            // Override scale
            var konstant = 1;
            if(maxValue > 100) {
            	konstant = 10;
            }
            if(maxValue > 1000) {
            	konstant = 80;
            }
            if(maxValue > 10000) {
            	konstant = 500;
            }
            if(maxValue > 100000) {
            	konstant = 5000;
            }
            chartOptions.scaleStepWidth = parseInt((maxValue * konstant) / (maxValue / 10)); 
            chartOptions.scaleSteps = parseInt((maxValue / chartOptions.scaleStepWidth) + 1);
            
            chartData.datasets[0] = {
            		fillColor : "rgba(151,187,205,0.5)",
					strokeColor : "rgba(151,187,205,1)",
					pointColor : "rgba(151,187,205,1)",
					pointStrokeColor : "#fff",
					data : subDataSet
            };
        	
            // Override options
            chartOptions.animation = animation;
            
            // Paint chart on canvas
        	chartJS[elemIndex].Line(chartData, chartOptions);
        }
        
        /**
		 * Function dummy constructor
		 * 
		 * @access private
		 * @param String contextSelector
		 * @method <<IIFE>>
		 * @return Void
		 */
        (function __construct() {
            // Get target canvas context 2d to render chart
        	if(!!document.createElement('canvas').getContext) {
        		var resizeRepaintCanvas = function(animation) {
        			$.each(targetSelector, function(k, elem){
        				// Get context
        				context = $(elem).get(0).getContext('2d');
        				// Get HTMLCanvasElement
        				var canvas = $(elem).get(0);
        				// Get parent container width
        				var containerWidth = $(canvas).parent().width();
        				// Set dinamically canvas width
        				canvas.width  = parseInt((containerWidth * 90) / 100);
        				canvas.height = 180;
        				// Repaint canvas contents
        				generateLineChart(context, elem, animation);
        			});
        		}
        		
            	// Start generation
            	setTimeout(function(){
            		resizeRepaintCanvas(true);
            	}, 200);
            	
            	$(document).on('click', '#menu-collapse', function(event){
            		setTimeout(function(){
            			resizeRepaintCanvas(false);
            		}, 300);
            	});
            	
        		$(window).on('resize', function(){
        			resizeRepaintCanvas();
        		});
        	}
        }).call(this);
    }

    // On DOM Ready
    $(function () {
        var GptranslateCPanel = new CPanel(['#chart_links_canvas']);
    });
})(jQuery);