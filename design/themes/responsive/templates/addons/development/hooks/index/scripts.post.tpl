{script src="js/addons/development/jquery.selectbox-0.2.js"}
{script src="js/addons/development/jquery.magnific-popup.js"}

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

    function fn_stick_element(sticky, footer)
    {
        if (sticky.length > 0 && footer.length > 0) {
            var top_margin = 115;
            var bottom_margin = 20;
            var topmenu_offset = sticky.parent().offset();
            if (!sticky.hasClass('sticky') && $(this).scrollTop() + top_margin >= sticky.offset().top && sticky.outerHeight(true) < footer.offset().top - sticky.offset().top - 50) {
                init_position = sticky.offset().top;
                sticky.addClass('sticky');
            }
            if (sticky.hasClass('sticky')) {
                sticky.css('left', topmenu_offset.left - $(this).scrollLeft() + 'px');
                if (sticky.outerHeight(true) > footer.offset().top - init_position) {
                    sticky.removeClass('sticky');
                } else if (sticky.outerHeight(true) + top_margin - bottom_margin > footer.offset().top - $(this).scrollTop()) {
                    sticky.css('top', footer.offset().top - sticky.outerHeight(true) - $(this).scrollTop() + bottom_margin + 'px');
                } else {
                    if ($(this).scrollTop() + top_margin < init_position) {
                        sticky.css('top', init_position - $(this).scrollTop() + 'px');
                    } else {
                        sticky.css('top', top_margin + 'px');
                    }
                }
            }
        }
    }
    function fn_fix_width()
    {
        width = $(window).width() < 1200 ? "1200px" : "100%";
        $('.tygh-top-panel').css({"left": - $(this).scrollLeft() + "px", "width": width});
        $('#tygh_container').css({"width": width});
    }
    
    (function(_, $) {
        $(function() {
            if (!$('#tygh_main_container').hasClass('touch')) {
                fn_fix_width();
                var sticky = $(".ty-sticky");
                var footer = $('#tygh_footer');
                fn_stick_element(sticky, footer);
                $(window).resize(function() {
                    fn_fix_width();
                    var init_position = 0;
                    fn_stick_element(sticky, footer);
                });
                $(window).scroll(function() {
                    fn_fix_width();
                    var init_position = 0;
                    fn_stick_element(sticky, footer);
                    $('.cm-parallax').each(function(){
                        if ($(window).scrollTop() < $(this).offset().top + $(this).outerHeight() - 115) {
                            $(this).css({ backgroundPosition: 'center '+ (109 - $(window).scrollTop() / $(this).data('speed')) + 'px' });
                        }
                    });
                });
            }
        });
    }(Tygh, Tygh.$));
{/literal}
</script>