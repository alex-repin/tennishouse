<div class="ty-add-cpair" id="add_pair_{$product.product_id}">
    <div class="ty-cp-cell ty-cp-item-id">
        <input type="text" name="{$input}[obj_id]" size="55" value="{if $product.main_competitor.pair_id && $c_id && $c_id == $product.main_competitor.competitor_id}{$product.main_competitor.item_id}{elseif $pair.competitive_id}{$pair.competitive_id}{/if}" class="ty-cp-item-id-input" id="cp_item_id_{$product.product_id}"/>
    </div>
    <div class="ty-cp-cell ty-cp-c-id">
        <select name="{$input}[c_id]" onchange="fn_check_pair({$product.product_id}, $(this).val(), '{$input}');" style="width: 100%">
            <option value=""> - </option>
            {foreach from=""|fn_get_competitors_list item="comp"}
                <option value="{$comp.competitor_id}" {if $c_id && $c_id == $comp.competitor_id}selected="selected"{/if}>{$comp.name}</option>
            {/foreach}
        </select>
    </div>
    <div class="ty-cp-cell ty-cp-input" id="dv_block_{$product.product_id}">
        <input type="text" size="55" value="{if $pair.name}{$pair.name}{elseif $product.main_competitor.pair_id && $c_id && $c_id == $product.main_competitor.competitor_id }{$product.main_competitor.name}{else}{$product.product}{/if}" data-product-id="{$product.product_id}" class="input-large cm-competitor-products" id="input_{$product.product_id}" {if $c_id}data-competitor-id="{$c_id}"{/if}/>
        {include file="addons/dv_competitors/views/competitors/prices_results.tpl" product_id=$product.product_id}
    </div>
    <script type="text/javascript">
        {literal}
            (function (_, $) {
                fn_init_cmp_products_search();
            }(Tygh, Tygh.$));
        {/literal}
        $('#dv_block_' + '{$product.product_id}').hover(function(){ldelim}
            $(this).addClass('is-hover');
        {rdelim}, function(){ldelim}
            $(this).removeClass('is-hover');
        {rdelim});
    </script>
<!--add_pair_{$product.product_id}--></div>
