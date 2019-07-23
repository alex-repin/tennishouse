<div class="ty-cp-search-results" id="cp_variants_{$product_id}">
    {if $results}
        {foreach from=$results item="result"}
            <div data-cp-value="{$result.item_id}" class="ty-cp-search-results-item">
                <div class="ty-cp-search-results-item-select {if $result.in_stock == 'N'}ty-cp-search-results-item-sold-out{/if}" data-item-id="{$result.item_id}" data-product-id="{$product_id}">{$result.name nofilter} - {include file="common/price.tpl" value=$result.price} - </div>
                <a href="{$result.link}" target="_blank" class="ty-cp-search-results-item-link">></a>
            </div>
        {/foreach}
    {/if}
    
<script type="text/javascript">
{literal}
    (function (_, $) {
        $('.ty-cp-search-results-item-select').click(function(){
            $('#cp_item_id_' + $(this).data('productId')).val($(this).data('itemId'));
            $('#cp_variants_' + $(this).data('productId')).hide();
        });
    }(Tygh, Tygh.$));
{/literal}
</script>
<!--cp_variants_{$product_id}--></div>
