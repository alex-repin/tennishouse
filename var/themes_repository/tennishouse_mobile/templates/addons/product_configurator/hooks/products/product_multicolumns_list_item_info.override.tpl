{if $product.product_type == "C"}
<div class="ty-grid-list__item-name">
    {if $mode == 'R'}
        <div class="ty-product-series">{$product.subtitle}</div>
    {/if}
    <div class="ty-grid-list__item-title">
        {assign var="name" value="name_`$obj_id`"}
        {$smarty.capture.$name nofilter}
    </div>
</div>

<div class="ty-grid-list__price">
<div class="ty-grid-list__pc">{__("choose_your_package")}</div>
</div>

<div class="ty-grid-list__control">
    {if $settings.Appearance.enable_quick_view == 'Y'}
        {include file="views/products/components/quick_view_link.tpl" quick_nav_ids=$quick_nav_ids}
    {/if}

    {if $show_add_to_cart}
        <div class="button-container">
            {assign var="add_to_cart" value="add_to_cart_`$obj_id`"}
            {$smarty.capture.$add_to_cart nofilter}
        </div>
    {/if}
</div>
{/if}