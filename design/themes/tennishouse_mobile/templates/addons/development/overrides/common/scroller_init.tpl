{script src="js/lib/owlcarousel/owl.carousel.min.js"}
{$items_count = $items_count|default:$block.properties.item_quantity}
<script type="text/javascript">
(function(_, $) {
    $.ceEvent('on', 'ce.commoninit', function(context) {
        var elm = context.find('#scroll_list_{$block.block_id}{$suf}');

        if (elm.length) {
            elm.owlCarousel({
                items: {$items_count|default:1},
                {if $block.properties.scroll_per_page == "Y"}
                scrollPerPage: true,
                {/if}
                {if $block.properties.not_scroll_automatically == "Y"}
                autoPlay: false,
                {else}
                autoPlay: '{$block.properties.pause_delay * 1000|default:0}',
                {/if}
                slideSpeed: {$block.properties.speed|default:400},
                stopOnHover: true,
                navigation: true,
                navigationText: ['', ''],
                pagination: false,
                margin: 12
            });
        }
    });
}(Tygh, Tygh.$));
</script>