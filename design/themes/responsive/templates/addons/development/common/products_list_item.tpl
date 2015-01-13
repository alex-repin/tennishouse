<div class="ty-grid-list__item">
    {assign var="obj_id" value=$product.product_id}
    {include file="common/product_data.tpl" product=$product}

    {$features = $product|fn_get_product_features_list}
    {$series_feature = $features|fn_get_subtitle_feature:$product.type}
    {$series_variant_id = $series_feature.variant_id}
    {$type_variant_id = $features.$type_id.variant_id}
    {assign var="form_open" value="form_open_`$obj_id`"}
    {$smarty.capture.$form_open nofilter}
    <div class="ty-grid-list__image">
        {include file="views/products/components/product_icon.tpl" product=$product show_gallery=false}

        {assign var="discount_label" value="discount_label_`$obj_id`"}
        {$smarty.capture.$discount_label nofilter}
        
        <div class="ty-grid-list__brand-image">
            <a href="{"products.view?product_id=`$product.product_id`"|fn_url}">
            {include file="addons/development/common/brand_logo.tpl" product=$product features=$features}
            </a>
        </div>
        
        {if $small_mode != 'Y'}
        {assign var="rating" value="rating_`$obj_id`"}
        {if $smarty.capture.$rating}
            <div class="grid-list__rating">
                {$smarty.capture.$rating nofilter}
            </div>
        {/if}
        {/if}
    </div>
    
    <div class="ty-grid-list__item-info">
        <div class="ty-grid-list__item-name">
            {if $item_number == "Y"}
                <span class="item-number">{$cur_number}.&nbsp;</span>
                {math equation="num + 1" num=$cur_number assign="cur_number"}
            {/if}
            
            {if $small_mode != 'Y'}
            <div class="ty-product-series">
                {if $product.type == 'R'}
                    {if $series_feature.variants.$series_variant_id}
                        {__("series")} - {$series_feature.variants.$series_variant_id.variant}
                    {else}
                        {__("type")} - {$features.$type_id.variants.$type_variant_id.variant}
                    {/if}
                {elseif $product.type == 'A'}
                    {$brand_id = $smarty.const.BRAND_FEATURE_ID}
                    {$brand_variant_id = $features.$brand_id.variant_id}
                    {$series_feature.variants.$series_variant_id.variant} - {$features.$brand_id.variants.$brand_variant_id.variant}
                {elseif $product.type == 'S'}
                        {__("surface")} - {$series_feature.variants.$series_variant_id.variant}
                {elseif $product.type == 'B'}
                    {$brand_id = $smarty.const.BRAND_FEATURE_ID}
                    {$brand_variant_id = $features.$brand_id.variant_id}
                    {__("bag")} - {$features.$brand_id.variants.$brand_variant_id.variant}
                {elseif $product.type == 'ST'}
                    {__("structure")} - {$series_feature.variants.$series_variant_id.variant}
                {elseif $product.type == 'BL'}
                    {__("type")} - {$series_feature.variants.$series_variant_id.variant}
                {elseif $product.type == 'OG'}
                    {__("type")} - {$series_feature.variants.$series_variant_id.variant}
                {elseif $product.type == 'BG'}
                    {__("material")} - {$series_feature.variants.$series_variant_id.variant}
                {/if}
            </div>
            {/if}
            <div class="ty-grid-list__item-title">
                {assign var="name" value="name_`$obj_id`"}
                {$smarty.capture.$name nofilter}
            </div>
        </div>

        <div class="ty-grid-list__price {if $product.price == 0}ty-grid-list__no-price{/if}">
            {assign var="old_price" value="old_price_`$obj_id`"}
            {if $smarty.capture.$old_price|trim}{$smarty.capture.$old_price nofilter}{/if}

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
    </div>
    {assign var="form_close" value="form_close_`$obj_id`"}
    {$smarty.capture.$form_close nofilter}
</div>
