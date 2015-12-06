{script src="js/lib/owlcarousel/owl.carousel.js"}
<script type="text/javascript">
(function(_, $) {
    var elm = $('#scroll_list_{$id}');

    if (elm.length) {
        elm.owlCarousel({
            items: {$items_count|default:1},
            autoPlay: false,
            slideSpeed: 400,
            stopOnHover: true,
            navigation: true,
            navigationText: ['', ''],
            pagination: false
        });
    }
}(Tygh, Tygh.$));
</script>