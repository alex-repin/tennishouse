{script src="js/lib/owlcarousel/owl.carousel.min.js"}
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
            pagination: false,
            margin: 10
        });
    }
}(Tygh, Tygh.$));
</script>