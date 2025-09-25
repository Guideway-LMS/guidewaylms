/**
* @package com_splms
* @subpackage  mod_splmseventcalendar
*
* @author JoomShaper http://www.joomshaper.com
* @copyright Copyright (c) 2010 - 2024 JoomShaper
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/

;(function($){
    
    $.fn.eventcalendar = function(options) {
        
        var settings = {
            months: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
            days: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'sun']
        };
        
        this.each(function() {
            
            if (options) {
                $.extend(settings, options);
            }
            
            var container = this;
            var curent = new Date();
            
            if (typeof settings.events != 'undefined' && settings.events.length) {
                var events = $.parseJSON(settings.events);
                var minDate = events[0].date.split('-');
                curent = new Date(minDate[2], minDate[1] - 1);
            }
            
            var eventcalendar = function(){
                
                this.container = container;
                this.year = curent.getFullYear();
                this.month = curent.getMonth();
                this.navPrev = '';
                this.navNext = '';
                
                this.init = function() {
                    this.initBuild();
                    this.build();
                    
                    var that = this;
                    
                    this.navNext.on('click', function(event) {
                        event.preventDefault();
                        that.next();
                    });
                    
                    this.navPrev.on('click', function(event) {
                        event.preventDefault();
                        that.prev();
                    });
                }
                
                this.initBuild = function() {
                    var header = '<div class="eventcalendar-header eventcalendar-clearfix"><ul>';
                    header += '<li class="eventcalendar-nav-prev"><span class="splms-icon-angle-left"></span></li>';
                    header += '<li class="eventcalendar-nav-year-month"></li>';
                    header += '<li class="eventcalendar-nav-next"><span class="splms-icon-angle-right"></span></li>';
                    header += '</ul></div>';
                    
                    var days = $('<ul class="eventcalendar-days eventcalendar-clearfix"></ul>');
                    for(var i=0; i<=settings.days.length - 1; i++) {
                        days.append($('<li>'+settings.days[i]+'</li>'));
                    }
                    
                    var dates = $('<ul class="eventcalendar-dates eventcalendar-clearfix"></ul>');
                    
                    $(container).append($(header));
                    $(container).append(days);
                    $(container).append(dates);
                    
                    this.navPrev = $(container).find('.eventcalendar-nav-prev');
                    this.navNext = $(container).find('.eventcalendar-nav-next');
                }
                
                this.build = function() {
                    
                    var year = this.year;
                    var month = this.month;
                    var first = new Date(year, month, 1);
                    var last = new Date(year, month + 1, 0);
                    
                    while(first.getDay() != 1){
                        first.setDate(first.getDate()-1);
                    }
                    
                    while(last.getDay() != 0){
                        last.setDate(last.getDate()+1);
                    }
                    
                    var dateHtml = '';
                    
                    for(var day = first; day <= last; day.setDate(day.getDate())) {
                        for(var i = 0; i<7; i++) {
                            
                            var fulldate = day.getDate() + '-' + (day.getMonth() + 1) + '-' + day.getFullYear();
                            
                            var link = "javascript:;";
                            var data = "";
                            var cssClass = 'eventcalendar-date';
                            
                            if(day.getMonth() == this.month) {
                                if (typeof events != 'undefined') {
                                    for(var j = 0; j < events.length; j++) {
                                        if(events[j].date === fulldate) {
                                            link = events[j].url;
                                            cssClass += ' eventcalendar-has-event eventcalendar-has-tooltip';
                                            data = '<div class="eventcalendar-event-info">';
                                            data += '<div class="eventcalendar-event-info-inner">';
                                            data += '<span class="eventcalendar-event-date">' + events[j].start + '</span>';
                                            data += '<h3 class="eventcalendar-event-title">' + events[j].title + '</h3>';
                                            data += '</div>';
                                            data += '</div>';
                                        }
                                    }
                                }
                            }
                            
                            date = $('<li><a class="'+ cssClass +'" href="'+ link +'"><span>' + day.getDate() + '</span></a>'+ data +'</li>');
                            
                            if(day.toDateString() === (new Date).toDateString()) {
                                date.find('.eventcalendar-date').addClass('eventcalendar-today');
                            }
                            
                            if(day.getMonth() != this.month) {
                                date.find('.eventcalendar-date').addClass('eventcalendar-date-inactive'); 
                            }
                            
                            dateHtml += date.prop('outerHTML');
                            day.setDate(day.getDate() + 1);
                        }
                    }
                    
                    $(container).find('.eventcalendar-nav-year-month').text(settings.months[this.month] + ' ' + this.year);
                    $(container).find('.eventcalendar-dates').html(dateHtml);
                    
                }
                
                // Go to next
                this.next = function() {
                    this.month = this.month + 1;
                    if(this.month == 12) {
                        this.year = this.year + 1;
                        this.month = 0;
                    }
                    
                    this.build();
                }
                
                // Go to Prev
                this.prev = function() {
                    this.month = this.month - 1;
                    if(this.month == -1) {
                        this.year = this.year - 1;
                        this.month = 11;
                    }
                    
                    this.build();
                }
                
            }
            
            new eventcalendar().init();
        });
    }
    
})(jQuery);