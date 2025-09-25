/**
 * @package     SP LMS
 * @subpackage  mod_splmscoursesearch
 *
 * @copyright   Copyright (c) 2010 - 2021 JoomShaper. All rights reserved.
 * @license     GNU General Public License version 2 or later.
 */

 jQuery(function($) {

    var coursesearchdelay = (function(){
        var timer = 0;
        return function(callback, ms){
            clearTimeout (timer);
            timer = setTimeout(callback, ms);
        };
    })();

    $('.splms-coursesearch-input').on('keyup', function(event) {

        event.preventDefault();

        //Return on escape
        if(event.keyCode==27) {
            $('.splms-course-search-results').fadeOut(400);
            return;
        }
        
        var data = $(this).val(),
        icon = $(this).next('.splms-course-search-icons').find('i'),
        request = {
            'option' : 'com_ajax',
            'module' : 'splmscoursesearch',
            'data'   : data,
            'format' : 'json'
        };

        icon.removeClass('splms-icon-search').addClass('splms-icon-spinner splms-icon-spin');

        coursesearchdelay(function(){

            if(data=='') {
                $('.splms-course-search-results').fadeOut(400);
            } else {
                $('.splms-course-search-results').fadeIn(400);
            }

            $.ajax({
                type   : 'POST',
                data   : request,
                success: function (response) {
                    icon.removeClass('splms-icon-spinner splms-icon-spin').addClass('splms-icon-search');
                    $('.splms-course-search-results').html(response.data);
                }
            });

        }, 500 );
        
        return false;
    });
});