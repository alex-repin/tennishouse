{if !$show_empty}
    {split data=$products size=$columns|default:"2" assign="splitted_products"}
{else}
    {split data=$products size=$columns|default:"2" assign="splitted_products" skip_complete=true}
{/if}

{math equation="100 / x" x=$columns|default:"2" assign="cell_width"}
{if $item_number == "Y"}
    {assign var="cur_number" value=1}
{/if}

{* FIXME: Don't move this file *}
{script src="js/tygh/product_image_gallery.js"}

{if $settings.Appearance.enable_quick_view == 'Y'}
    {$quick_nav_ids = $products|fn_fields_from_multi_level:"product_id":"product_id"}
{/if}
{if $block.properties.all_items_url}
    {$all_items_url = $block.properties.all_items_url}
{else}
    {if $block.content.items.filling == 'also_bought'}
        {$all_items_url = "products.search&search_performed=Y&also_bought_for_product_id=`$product.product_id`"}
    {elseif $block.content.items.filling == 'same_brand_products'}
        {$brand_id = $smarty.const.BRAND_FEATURE_ID}
        {$brand_type = $smarty.const.BRAND_FEATURE_TYPE}
        {if $smarty.session.product_features.$brand_id.variant_id && $brand_type}
            {$all_items_url = "products.search&search_performed=Y&features_hash=`$brand_type``$smarty.session.product_features.$brand_id.variant_id`"}
        {/if}
    {elseif $block.content.items.filling == 'similar_products'}
        {$all_items_url = "products.search&search_performed=Y&similar_pid=`$product.product_id`"}
    {*elseif $block.content.items.filling == 'bestsellers'}
        {$all_items_url = "products.search&search_performed=Y&bestsellers=1&sort_by=sales_amount&sort_order=desc"*}
    {/if}
{/if}
<div class="grid-list {if $block.properties.mode == 'S'}ty-small-mode{elseif $block.properties.mode == 'N'}ty-mini-mode{elseif $block.properties.mode == 'M'}ty-micro-mode{/if}">
    {strip}
        {$type_id = $smarty.const.TYPE_FEATURE_ID}
        {foreach from=$splitted_products item="sproducts" name="sprod"}
            {foreach from=$sproducts item="product" name="sproducts"}
                <div class="ty-column{$columns}">
                    {if $product}
                        {include file="addons/development/common/products_list_item.tpl" mode=$block.properties.mode|default:"R" hide_form=true}
                    {/if}
                </div>
            {/foreach}
            {if $show_empty && $smarty.foreach.sprod.last}
                {assign var="iteration" value=$smarty.foreach.sproducts.iteration}
                {capture name="iteration"}{$iteration}{/capture}
                {hook name="products:products_multicolumns_extra"}
                {/hook}
                {assign var="iteration" value=$smarty.capture.iteration}
                {if $iteration % $columns != 0}
                    {math assign="empty_count" equation="c - it%c" it=$iteration c=$columns}
                    {section loop=$empty_count name="empty_rows"}
                        <div class="ty-column{$columns}">
                            <div class="ty-product-empty">
                                <span class="ty-product-empty__text">{__("empty")}</span>
                            </div>
                        </div>
                    {/section}
                {/if}
            {/if}
        {/foreach}
    {/strip}
    {if $all_items_url}
        <div class="ty-check-all__block-link">
            <a href="{"`$all_items_url`"|fn_url}">{__("check_all_items")|upper}</a>
        </div>
    {/if}
</div>
