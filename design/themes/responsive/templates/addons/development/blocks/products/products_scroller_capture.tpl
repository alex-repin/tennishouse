{** block-description:tmpl_scroller_capture **}

{capture name="block_tab_`$block.block_id`"}
{if $block.properties.enable_quick_view == "Y"}
    {$quick_nav_ids = $items|fn_fields_from_multi_level:"product_id":"product_id"}
{/if}

{if $block.properties.hide_add_to_cart_button == "Y"}
        {assign var="_show_add_to_cart" value=false}
    {else}
        {assign var="_show_add_to_cart" value=true}
    {/if}
    {if $block.properties.show_price == "Y"}
        {assign var="_hide_price" value=false}
    {else}
        {assign var="_hide_price" value=true}
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

{assign var="obj_prefix" value="`$block.block_id`000"}
<div id="content_block_tab_{$block.block_id}" class="ty-wysiwyg-content">
    <div id="scroll_list_{$block.block_id}" class="owl-carousel ty-scroller-list">
        {$type_id = $smarty.const.TYPE_FEATURE_ID}
        {foreach from=$items item="product" name="for_products"}
            {include file="addons/development/common/products_list_item.tpl"
            show_trunc_name=true
            show_old_price=true
            show_price=true
            show_rating=true
            show_clean_price=true
            show_list_discount=true
            show_add_to_cart=$show_add_to_cart|default:false
            but_role="action"
            show_discount_label=true}
        {/foreach}
    </div>
    {if $all_items_url}
        <div class="ty-check-all__block-link">
            <a href="{"`$all_items_url`"|fn_url}">{__("check_all_items")|upper}</a>
        </div>
    {/if}
</div>

{include file="common/scroller_init.tpl"}
{/capture}