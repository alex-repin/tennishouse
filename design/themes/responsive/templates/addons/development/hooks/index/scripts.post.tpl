{script src="js/addons/development/jquery.mCustomScrollbar.concat.min.js"}
{script src="js/addons/development/share_buttons.js"}

<script type="text/javascript">
{literal}

    function fn_stick_element()
    {
        var top_margin = 115;
        var bottom_margin = 20;
        if (!$('.ty-sticky').hasClass('sticky') && $(this).scrollTop() + top_margin >= $('.ty-sticky').offset().top && $('.ty-sticky').outerHeight(true) < $('#tygh_footer').offset().top - $('.ty-sticky').offset().top - 50) {
            init_position = $('.ty-sticky').offset().top;
            $('.ty-sticky').addClass('sticky');
        }
        if ($('.ty-sticky').hasClass('sticky')) {
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
    
(function(_, $) {

    $(document).ready(function() {
        if ($(".ty-sticky").length) {
            var init_position = 0;
            $(window).scroll(function() {
                fn_stick_element();
            });
        }
    });
    
}(Tygh, Tygh.$));
{/literal}
</script>