/**
 * @package     SP LMS
 *
 * @copyright   Copyright (C) 2010 - 2021 JoomShaper. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

jQuery(function ($) {
    'use strict';

    //Add to cart
    $(document).on('click', '#addtocart', function (event) {
        event.preventDefault();
        var $this = $(this);
        var request = {
            'option': 'com_splms',
            'task': 'cart.add',
            'course': $(this).data('course')
        };

        $.ajax({
            type: 'POST',
            url: splms_url,
            data: request,
            beforeSend: function () {
                $this.find('.splms-icon').addClass(' splms-icon-spinner splms-icon-spin');
            },
            success: function (data) {
                var newData = $.parseJSON(data);
                $this.removeAttr('href').removeAttr('id').attr('href', newData.redirect);
                $this.html('<i class="splms-icon-check"></i> ' + newData.button_text);
            }
        });
    });

    // Remove from cart
    $(document).on('click', '.btn-remove-cart', function (event) {
        event.preventDefault();
        var request = {
            'option': 'com_splms',
            'task': 'cart.remove',
            'course': $(this).data('course')
        };

        $.ajax({
            type: 'POST',
            url: splms_url,
            data: request,
            success: function (data) {
                var newData = $.parseJSON(data);
                window.location = newData.redirect;
            }
        });
    });

    // Follow Teacher
    $(document).on('click', '[action-toggle-follow]', function (event) {
        event.preventDefault();
        var teacher = $(this).data('id');
        var $this = $(this);
        var $followers = $('[data-followers-count]');
        var followers_count = $followers.attr('data-count');

        var request = {
            'option': 'com_splms',
            'task': 'teachers.toggleFollow',
            'teacher': teacher
        };

        $.ajax({
            type: 'POST',
            url: splms_url,
            data: request,
            beforeSend: function () {
                $this.find('.splms-icon').addClass(' splms-icon-spinner splms-icon-spin');
            },
            success: function (response) {
                $this.find('.splms-icon').removeClass(' splms-icon-spinner splms-icon-spin');
                var data = $.parseJSON(response);
                if (data.success) {
                    $this.find('span').text(data.text);
                    if (data.status == 1) {
                        $this.find('.splms-icon').removeClass('splms-icon-add').addClass('splms-icon-remove');
                        $followers.attr('data-count', followers_count * 1 + 1).text(followers_count * 1 + 1);
                    } else {
                        $this.find('.splms-icon').removeClass('splms-icon-remove').addClass('splms-icon-add');
                        $followers.attr('data-count', followers_count * 1 - 1).text(followers_count * 1 - 1);
                    }
                } else {
                    alert(data.message);
                }
            }
        });
    });

    // Load followers
    $(document).on('click', '[action-load-followers]', function (event) {
        event.preventDefault();
        var $this = $(this);
        var teacher = $this.data('teacher');
        var start = $this.attr('data-loaded');
        var total = $this.data('total');

        var request = {
            'option': 'com_splms',
            'task': 'teachers.followers',
            'teacher': teacher,
            'start': start,
        };

        $.ajax({
            type: 'POST',
            url: splms_url,
            data: request,
            beforeSend: function () {
                $this.prepend('<i class="splms-icon-spinner splms-icon-spin"></i>');
            },
            success: function (response) {
                var data = $.parseJSON(response);
                $this.find('.splms-icon-spinner').remove();
                if (data.status) {
                    var loaded = $this.attr('data-loaded') * 1 + data.count;
                    console.log(loaded);
                    $('[data-teacher-follower-list]').append(data.html);
                    $this.attr('data-loaded', loaded);
                    if (loaded >= total) {
                        $this.remove();
                    }
                } else {
                    $this.remove();
                }
            }
        });
    });

    // Contagem Regressiva
    jQuery.fn.countDown = function (settings, to) {
        settings = jQuery.extend({
            FontSize: "inherit",
            duration: 1000,
            startNumber: 500,
            endNumber: 0,
            callBack: function () { }
        }, settings);

        function formatTime(seconds) {
            var m = Math.floor(seconds / 60);
            var s = seconds % 60;
            return (m < 10 ? '0' + m : m) + ":" + (s < 10 ? '0' + s : s);
        }

        return this.each(function () {

            //where do we start?
            if (!to && to != settings.endNumber) { to = settings.startNumber; }
            //set the countdown to the starting number
            jQuery(this).text(formatTime(to));
            //loopage
            jQuery(this).animate({
                fontSize: settings.FontSize
            }, settings.duration, "", function () {
                if (to > settings.endNumber + 1) {
                    jQuery(this).text(formatTime(to - 1)).countDown(settings, to - 1);
                }
                else {
                    settings.callBack(this);
                }

            });

        });
    }; // END:: Countdown

    // ratting
    $('.sp-lms-rating.can-rate').find('i.star').on('click', function (event) {
        event.preventDefault();
        var $ratings = $(this).parent().find('i.star');
        $ratings.removeClass('fa fa-star');
        $ratings.addClass('far fa-star');
        for (var i = $(this).data('rating_val') - 1; i >= 0; i--) {
            $ratings.eq(4 - i).removeClass('far fa-star');
            $ratings.eq(4 - i).addClass('fa fa-star');
        }
        $('#form-item-review').find('#input-rating').val($(this).data('rating_val'));
        $(this).parent().next('.splms-rating-summary').find('span').text($(this).data('rating_val'));
        $('#form-item-review').find('#input-review').focus();
    });

    // review
    $('#splms-my-review').on('click', function (event) {
        event.preventDefault();
        $('body').addClass('reviewers-form-popup-open')
        $('#reviewers-form-popup').show();
    });

    $('#reviewers-form-popup .close-popup').on('click', function (event) {
        event.preventDefault();
        $('body').removeClass('reviewers-form-popup-open');
        $('#reviewers-form-popup').hide();
        window.location.reload(true);
    });

    // submit review
    $('#form-item-review').on('submit', function (event) {
        event.preventDefault();

        var value = $(this).serializeArray();
        $.ajax({
            type: 'POST',
            url: splms_url + "index.php?option=com_splms&task=review.addreview",
            format: 'json',
            data: value,
            beforeSend: function () {
                $('.reviewers-form').addClass('sp-loader');
            },
            success: function (response) {
                var data = $.parseJSON(response);
                if (data.status) {
                    $('.reviewers-form').removeClass('sp-loader');
                    console.log(data);

                    if (data.update) {
                        $('#reviewers-form-popup').prepend($('<p class="alert alert-success text-center"><strong>' + Joomla.JText._('COM_SPLMS_REVIEW_UPDATED') + '</strong></p>').hide().fadeIn());
                        $('#submit-review').attr('disabled', 'disabled');

                        setInterval(function () {
                            $('#reviewers-form-popup').fadeOut(200, function () {
                                window.location.reload(true);
                            });
                        }, 1500);

                        $('.own-review').empty().html($(data.content).html());
                    } else {
                        $('.reviewers-form').fadeOut(200, function () {
                            $(this).remove();
                        });
                        $('.reviewers-form').after(data.content);
                    }
                } else {
                    alert(data.content);
                }

            }
        });
    });

    // multi teacher toogle
    $('ul.course-info>li.teacher-name').on('hover', function (event) {
        var that = $(this);
        var nextUl = that.find('.splms-course-multi-teachers');
        nextUl.slideToggle();
    });

    /* Load More */
    $('#splms-load-review').on('click', function (event) {
        event.preventDefault();
        var that = $(this);
        $.ajax({
            type: 'POST',
            url: splms_url + 'index.php?option=com_splms&task=review.reviews&item_id',
            format: 'json',
            data: { 'item_id': $(this).data('item_id'), 'start': $('#reviews').find('>.review-item').length },
            beforeSend: function () {
                that.find('.fa').removeClass('fa-refresh').addClass('fa-spinner fa-spin');
            },
            success: function (response) {
                var data = $.parseJSON(response);

                if (data.status) {
                    $('#reviews').append(data.content);
                    that.find('.fa').removeClass('fa-spinner fa-spin').addClass('fa-refresh');

                    if (!data.loadmore) {
                        that.remove();
                    }
                }
            }
        });
    });

    $(document).on('click', '#splms-completed-item', function (event) {
        event.preventDefault();
        var $self = $(this).parent('form#splms-completed-item-form');
        var value = $self.serializeArray();
        $.ajax({
            type: 'POST',
            url: splms_url + "index.php?option=com_splms&task=lesson.completeditem",
            format: 'json',
            data: value,
            beforeSend: function () {
                $self.find('.splms-icon').addClass(' splms-icon-spinner splms-icon-spin');
            },
            success: function (respose) {
                var data = $.parseJSON(respose);
                if (data.status) {
                    $self.find('.splms-icon').removeClass('splms-icon-spinner splms-icon-spin');
                    $self.find('#splms-completed-item').text(data.content);
                }
            }
        });
    });

    $(document).on('click', '.splms-payment-methods .payment-method-bank', function (event) {
        $('.splms-view-cart .splms-payment-methods-text-wrap .splms-payment-method-bank').fadeIn();
    });

    $(document).on('click', '.splms-payment-methods .payment-method-direct, .splms-payment-methods .payment-method-paypal', function (event) {
        $('.splms-view-cart .splms-payment-methods-text-wrap .splms-payment-method-bank').fadeOut();
    });

    $('#splms-payment-note').keyup(function () {
        var paymentNote = $(this).val(),
            paymentBtn = $('.btn-bankpayment').attr('href');
        $('.btn-bankpayment').attr('href', paymentBtn + '&payment_note=' + paymentNote);
        //console.log(paymentBtn);
    });

    //payment method select
    $("input[name$='payment-method']").click(function () {
        var paymentValue = $(this).val();

        $(".splms-cart .splms-payment-method").hide();
        $(".payment-method-" + paymentValue).show();
    });

    /*********** New Update JS(YOGA)  ***********/
    //lightbox gallery
    jQuery(function ($) {
        $(document).on('click', '.lightboxgallery-gallery-item', function (event) {
            event.preventDefault();
            $(this).lightboxgallery({
                showCounter: true,
                showTitle: true,
                showDescription: true
            });
        });
    });

    //Teacher PieChart
    if ($('.splms-pie-chart').length > 0) {
        $(function () {
            $('.splms-pie-chart').easyPieChart({
                barColor: $(this).data('barcolor'),
                trackColor: $(this).data('trackcolor'),
                scaleColor: false,
                lineWidth: 7,
                size: $(this).data('size')
            });
        });
    }

    // Custom Select Box
    if ($('select.sp-select').length > 0) {
        $(document).on('click', function (e) {
            var selector = $('.sp-select');
            if (!selector.is(e.target) && selector.has(e.target).length === 0) {
                selector.find('ul').slideUp();
            }
        });

        $('select.sp-select').each(function (event) {
            $(this).hide();
            var $self = $(this);
            var spselect = '<div class="sp-select">';
            spselect += '<div class="sp-select-result">';
            spselect += '<span class="sp-select-text">' + $self.find('option:selected').text() + '</span>';
            spselect += ' <i class="arrow-down"></i>';
            spselect += '</div>';
            spselect += '<ul class="sp-select-dropdown">';

            $self.children().each(function (event) {
                if ($self.val() == $(this).val()) {
                    spselect += '<li class="active" data-val="' + $(this).val() + '">' + $(this).text() + '</li>';
                } else {
                    spselect += '<li data-val="' + $(this).val() + '">' + $(this).text() + '</li>';
                }
            });

            spselect += '</ul>';
            spselect += '</div>';
            $(this).after($(spselect));
        });

        $(document).on('click', '.sp-select', function (event) {
            $('.sp-select').not(this).find('ul').slideUp();
            $(this).find('ul').slideToggle();
        });

        $(document).on('click', '.sp-select ul li', function (event) {
            var $select = $(this).closest('.sp-select').prev('select');
            $(this).parent().prev('.sp-select-result').find('span').html($(this).text());
            $(this).parent().find('.active').removeClass('active');
            $(this).addClass('active');
            $select.val($(this).data('val'));
            $select.change();
        }); // End Select
    }

    //Ajax Teacher contact form
    $('#splms-teacher-contact-form').on('submit', function (event) {
        event.preventDefault();
        var $self = $(this);
        var value = $(this).serializeArray();
        var request = {
            'option': 'com_splms',
            'task': 'ajax',
            'data': value
        };

        $.ajax({
            type: 'POST',
            url: splms_url + "index.php?option=com_splms&task=teachers.contact",
            data: value,
            beforeSend: function () {
                $self.addClass('contact-proccess');
                $self.find('#contact-submit').prepend('<i class="fa fa-spinner fa-spin"></i>');
            },
            success: function (response) {
                var data = $.parseJSON(response);
                if (data.status) {
                    $self.removeClass('contact-proccess').addClass('sent');
                    $self.find('#contact-submit').children('.fa-spinner').remove();
                    $self.find('#contact-submit').prop('disabled', true);
                    $self.next('.splms-cont-status').html('<p class="contact-sent">' + data.content + '</p>').fadeIn().delay(7000).fadeOut(500);
                    var getClass = $self.find('.splms-cont-status').attr('class');
                } else {
                    $self.next('.splms-cont-status').html('<p class="contact-error">' + data.content + '</p>').fadeIn().delay(7000).fadeOut(500);
                }
            }
        });
    });

    if ($('input[name="date"]').length > 0) {
        jQuery(function ($) {
            var date_input = $('input[name="date"]'); //our date input has the name "date"
            var options = {
                format: 'mm/dd/yyyy',
                todayHighlight: true,
                autoclose: true,
            };
            date_input.datepicker(options);
        });
    }

    //SmoothScroll
    if ($('.smoothScroll').length > 0) {
        $(function () {
            $('.smoothScroll').click(function () {
                if (location.pathname.replace(/^\//, '') == this.pathname.replace(/^\//, '') && location.hostname == this.hostname) {
                    var target = $(this.hash);
                    target = target.length ? target : $('[name=' + this.hash.slice(1) + ']');
                    if (target.length) {
                        $('html,body').animate({
                            scrollTop: target.offset().top
                        }, 700);
                        return false;
                    }
                }
            });
        });
    }

    // splms courses filter
    $('#splms-courses-filters-form').on('submit', function (event) {
        event.preventDefault();
        var $this = $(this);

        var params = {};
        var action = $(this).attr('action');

        if ($this.find('input[name="terms"]').val()) {
            params.terms = $this.find('input[name="terms"]').val();
        }

        var categories = $this.find('input[name="category[]"]:checked').map(function () {
            return this.value;
        }).get().join(",");

        if (categories) {
            params.category = categories;
        }

        var levels = $this.find('input[name="level[]"]:checked').map(function () {
            return this.value;
        }).get().join(",");

        if (levels) {
            params.level = levels;
        }

        if ($this.find('input[name="course_type"]:checked').val()) {
            params.course_type = $this.find('input[name="course_type"]:checked').val();
        }

        params.filtered = 1;

        if (params) {
            window.location.href = action + (action.indexOf('?') === -1 ? '?' : '&') + $.param(params);
        }

    });

    // splms courses filter reset
    $('#splms-courses-filters-form,#splms-coursescategories-filters-form').on('reset', function (event) {
        event.preventDefault();
        var action = $(this).attr('action');
        window.location = action;
    });

    // Course filters
    if ($(".splms-courses-filters-top").length > 0) {
        $('.filter-toggler').on('click', function () {

            //$(this).siblings().slideToggle()          
            let element = $(this).nextAll('.splms-filter-items:lt(1)')
            element.slideToggle();
            $('.splms-filter-items').not(element).hide();
        })

        $(".splms-filter-items").each(function (e) {
            $(this).find('input[type="radio"]').click(function () {
                if ($(this).prop('checked') == true) {
                    $(this).closest(".splms-filter-items").slideUp()
                }

            })
        })
    }


    // Category and SubCategory filter button

    if ($('.view-splms-courses').is(':visible') || $('.splms-view-category-courses').is(':visible')) {

        let course_list = document.querySelector('.splms-courses-list');
        let shuffle_class = $(course_list).find('.splms-shuffle');
        let splms_row = document.querySelector('.splms-row');

        if (course_list) {
            if (shuffle_class.length == 0) {
                $(splms_row).addClass('splms-shuffle');
            }
        }

        if ($(this).find('.splms-shuffle').length == 1) {
            var Shuffle = window.Shuffle;
            var element = document.querySelector('.splms-shuffle');
            var shuffleInstance = new Shuffle(element, {
                itemSelector: 'div'
            });
            $('.splms-filter-option li').on('click', function (e) {
                e.preventDefault();
                $('.splms-filter-option li').removeClass('selected');
                $(this).addClass('selected');
                var keyword = $(this).attr('data-target');
                shuffleInstance.filter(keyword);
            });
        }
    }


    // Countdown and redirection of payment
    if ($('#countdown-number').is(':visible')) {
        var countdownNumber = document.getElementById('countdown-number');
        var countdown = 25;

        countdownNumber.textContent = countdown + ' s';

        setInterval(function () {
            countdown--;

            if (countdown <= 0) {
                window.location = splms_url + "index.php?option=com_splms&view=courses";
            }
            else {
                countdownNumber.textContent = countdown + ' s';
            }
        }, 1000);
    }


    // For double canonical link issue. 
    $(window).on('load', function () {
        let rel = "canonical";
        let found = false;
        let links = document.getElementsByTagName("link");

        for (let i = 0; i < links.length; i++) {
            if (links[i].rel === rel) {
                found = true;
                break;
            }
        }

        if (!found) {
            let link = document.createElement("link");
            link.rel = rel;
            link.href = window.location.href;

            document.head.appendChild(link);
        }
    })

});


