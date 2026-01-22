/**
 * @package     SP LMS
 * @subpackage  mod_splmscart
 *
 * @copyright   Copyright (C) 2010 - 2021 JoomShaper. All rights reserved.
 * @license     GNU General Public License version 2 or later.
 */

 jQuery(function($){ 'use strict';

    //Cart list
    if($('.splms-cart-list').length>0){
        var coursesList = $('.mod-splms-courses .splms-courses-list-wrap');

        $('.mod-splms-courses .cart-icon').on('click', function(){
          coursesList.slideToggle(300);  
          $(this).toggleClass('active');
        });
    }

});
