<script type="text/javascript">
{literal}

    function fn_mouseleave_tooltip(trigger)
    {
        trigger.parents('.ty-menu__item-parent .ty-menu__item_full').each(function(){
            if (!$(this).hasClass('is-hover')) {
                fn_hide_top_menu($(this));
            }
        });
    }

    function fn_stick_element()
    {
        var top_margin = 115;
        var bottom_margin = 20;
        var topmenu_offset = $('.ty-sticky').parent().offset();
        if (!$('.ty-sticky').hasClass('sticky') && $(this).scrollTop() + top_margin >= $('.ty-sticky').offset().top && $('.ty-sticky').outerHeight(true) < $('#tygh_footer').offset().top - $('.ty-sticky').offset().top - 50) {
            init_position = $('.ty-sticky').offset().top;
            $('.ty-sticky').addClass('sticky');
        }
        if ($('.ty-sticky').hasClass('sticky')) {
            $('.ty-sticky').css('left', topmenu_offset.left - $(this).scrollLeft() + 'px');
            if ($('.ty-sticky').outerHeight(true) > $('#tygh_footer').offset().top - init_position) {
                $('.ty-sticky').removeClass('sticky');
            } else if ($('.ty-sticky').outerHeight(true) + top_margin - bottom_margin > $('#tygh_footer').offset().top - $(this).scrollTop()) {
                $('.ty-sticky').css('top', $('#tygh_footer').offset().top - $('.ty-sticky').outerHeight(true) - $(this).scrollTop() + bottom_margin + 'px');
            } else {
                if ($(this).scrollTop() + top_margin < init_position) {
                    $('.ty-sticky').css('top', init_position - $(this).scrollTop() + 'px');
                } else {
                    $('.ty-sticky').css('top', top_margin + 'px');
                }
            }
        }
    }
    function fn_fix_width()
    {
        width = $(window).width() < 1200 ? "1200px" : "100%";
        $('.tygh-top-panel').css({"left": - $(this).scrollLeft() + "px", "width": width});
        $('#tygh_main_container').css({"width": width});
    }
    
    (function(_, $) {
        Tygh.$(document).ready(function() {
            if (!$('#tygh_main_container').hasClass('touch')) {
                $(document).ready(function() {
                    fn_fix_width();
                    $(window).resize(function() {
                        fn_fix_width();
                        if ($(".ty-sticky").length > 0) {
                            var init_position = 0;
                            fn_stick_element();
                        }
                    });
                    $(window).scroll(function() {
                        fn_fix_width();
                        if ($(".ty-sticky").length > 0) {
                            var init_position = 0;
                            fn_stick_element();
                        }
                    });
                });
                $('.cm-parallax').each(function(){
                    var bgobj = $(this); // создаем объект
                    $(window).scroll(function() {
                        var yPos = 109 -($(window).scrollTop() / bgobj.data('speed')); // вычисляем коэффициент 
                        // Создаем эффект Parallax Scrolling
                        bgobj.css({ backgroundPosition: 'center '+ yPos + 'px' });
                    });
                });
            }
        });
    }(Tygh, Tygh.$));
{/literal}
</script>