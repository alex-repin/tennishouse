{script src="js/lib/owlcarousel/owl.carousel.min.js"}
<script type="text/javascript">
(function(_, $) {
    $.ceEvent('on', 'ce.commoninit', function(context) {
        $('.cm-banner-carousel').each(function(){
            if ($(this).length) {
                $(this).owlCarousel({
                    items: 1,
                    singleItem : true,
                    slideSpeed: $(this).data('slideSpeed'),
                    autoPlay: $(this).data('autoPlay'),
                    stopOnHover: true,
                    pagination: ($(this).data('navigation') == 'P' || $(this).data('navigation') == 'D') ? true : false,
                    paginationNumbers: ($(this).data('navigation') == 'P') ? true : false,
                    navigation: ($(this).data('navigation') == 'A') ? true : false,
                    navigationText: ($(this).data('navigation') == 'A') ? ['{__("prev_page")}', '{__("next")}'] : false
                });
            }
        });
    });
}(Tygh, Tygh.$));
</script>