<script type="text/javascript">
{literal}

    function fn_stick_element()
    {
        if (!$('.ty-sticky').hasClass('sticky') && $(this).scrollTop() + 135 >= $('.ty-sticky').offset().top && $('.ty-sticky').outerHeight(true) < $('#tygh_footer').offset().top - $('.ty-sticky').offset().top - 50) {
            init_position = $('.ty-sticky').offset().top;
            $('.ty-sticky').addClass('sticky');
        }
        if ($('.ty-sticky').hasClass('sticky')) {
            if ($('.ty-sticky').outerHeight(true) > $('#tygh_footer').offset().top - init_position) {
                $('.ty-sticky').removeClass('sticky');
            } else if ($('.ty-sticky').outerHeight(true) + 115 > $('#tygh_footer').offset().top - $(this).scrollTop()) {
                $('.ty-sticky').css('top', $('#tygh_footer').offset().top - $('.ty-sticky').outerHeight(true) - $(this).scrollTop() + 20 + 'px');
            } else {
                if ($(this).scrollTop() + 135 < init_position) {
                    $('.ty-sticky').css('top', init_position - $(this).scrollTop() + 'px');
                } else {
                    $('.ty-sticky').css('top', 135 + 'px');
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