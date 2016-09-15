<div class="ty-grid-list__item-wrapper">
{hook name="products:product_multicolumns_list"}
{/hook}
<a href="{"products.view?product_id=`$product.product_id`{if $product.ohash}&`$product.ohash`{/if}"|fn_url}">
<div class="ty-grid-list__item">
    {assign var="obj_id" value=$product.product_id}
    {include file="common/product_data.tpl" product=$product}

    {assign var="form_open" value="form_open_`$obj_id`"}
    {$smarty.capture.$form_open nofilter}
    <div class="ty-grid-list__image">
        {if $product.option_images && ($mode == 'R' || $mode  == 'S')}
            <div class="ty-list-options">
            {foreach from=$product.option_images item="opt_icon"}
                <div class="ty-list-options_box">
                    {if $opt_icon.icon}{include file="common/image.tpl" show_detailed_link=false images=$opt_icon.icon no_ids=true image_height="15"}{/if}
                </div>
            {/foreach}
            </div>
        {/if}
        <div class="ty-grid-list__image-product">
            {include file="views/products/components/product_icon.tpl" product=$product show_gallery=false}
        </div>

        {if $mode == 'R'}
            {assign var="discount_label" value="discount_label_`$obj_prefix``$obj_id`"}
            {$smarty.capture.$discount_label nofilter}
        {/if}
        
        <div class="ty-grid-list__brand-image">
            {include file="addons/development/common/brand_logo.tpl"  brand=$product.brand brand_variant_id=$product.brand.variant_id}
        </div>
        
        {if $mode == 'R'}
            {*assign var="rating" value="rating_`$obj_id`"}
            {if $smarty.capture.$rating}
                <div class="grid-list__rating">
                    {$smarty.capture.$rating nofilter}
                </div>
            {/if*}
            {if $product.likes > 0}
                <div class="ty-grid-list__likes">
                    <div class="{if $product.is_liked}ty-grid-list__likes-heart-full{else}ty-grid-list__likes-heart{/if}"></div><span class="ty-grid-list__likes-num">{$product.likes}</span>
                </div>
            {/if}
        {/if}
    </div>
    
    <div class="ty-grid-list__item-info">
        {hook name="products:product_multicolumns_list_item_info"}
        <div class="ty-grid-list__item-name">
            {if $mode == 'R'}
                <div class="ty-product-series">{$product.subtitle}</div>
            {/if}
            <div class="ty-grid-list__item-title">
                {assign var="name" value="name_`$obj_id`"}
                {$smarty.capture.$name nofilter}
            </div>
        </div>

        <div class="ty-grid-list__price {if $product.price == 0}ty-grid-list__no-price{/if}">
            {assign var="old_price" value="old_price_`$obj_id`"}
            {if $mode == 'R' && $smarty.capture.$old_price|trim}{$smarty.capture.$old_price nofilter}{/if}

            {assign var="price" value="price_`$obj_id`"}
            {$smarty.capture.$price nofilter}

            {assign var="clean_price" value="clean_price_`$obj_id`"}
            {$smarty.capture.$clean_price nofilter}

            {assign var="list_discount" value="list_discount_`$obj_id`"}
            {$smarty.capture.$list_discount nofilter}
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
        {/hook}
    </div>
    
    {assign var="form_close" value="form_close_`$obj_id`"}
    {$smarty.capture.$form_close nofilter}
</div>
</a>
</div>