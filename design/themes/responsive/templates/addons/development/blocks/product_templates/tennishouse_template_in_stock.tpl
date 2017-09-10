{script src="js/tygh/exceptions.js"}
<div class="ty-product-block">
    <div itemscope itemtype="http://schema.org/Product" class="ty-product-block__wrapper clearfix">
    {hook name="products:view_main_info"}
        {if $product}
            {assign var="obj_id" value=$product.product_id}
            {include file="common/product_data.tpl" product=$product but_role="big" but_text=__("add_to_cart") hide_qty_label=true}
            <div class="ty-product-block__img-wrapper" id="product_page_left">
                {hook name="products:image_wrap"}
                    {if !$no_images}
                        <div class="ty-product-block__img cm-reload-{$product.product_id}" id="product_images_{$product.product_id}_update">

                            <div class="ty-product-tags">
                                {assign var="discount_label" value="discount_label_`$obj_prefix``$obj_id`"}
                                {$smarty.capture.$discount_label nofilter}
                                {if $product.tags.new}
                                    <div class="ty-new-item-tag"></div>
                                {/if}
                            </div>

                            {include file="views/products/components/product_images.tpl" product=$product show_detailed_link="Y" image_width=$settings.Thumbnails.product_details_thumbnail_width image_height=$settings.Thumbnails.product_details_thumbnail_height}
                        <!--product_images_{$product.product_id}_update--></div>
                    {/if}
                {/hook}
                {if $smarty.capture.block_product_cross_sales}
                <div class="ty-product-cross-block">
                    <div class="ty-product-plus">+</div>
                    <div class="ty-product-cross-discounts">{__("strings_overgrips_discount_text")}</div>
                    {$smarty.capture.block_product_cross_sales nofilter}
                </div>
                {/if}
                {if $product.players}
                    {if "RASBST"|strpos:$product.category_type !== false}
                        {$type = $product.category_type}
                    {else}
                        {$type = 'C'}
                    {/if}
                    {if $product.players|count == '1'}
                        {$title = __("`$product.category_type`_played_by_single")}
                    {else}
                        {$title = __("`$product.category_type`_played_by")}
                    {/if}
                    <div class="ty-product-block__players">
                        <div class="ty-product-block__players-title">
                            {$title}
                        </div>
                        <div>
                            {foreach from=$product.players item="player" name="plrs"}
                                {if $smarty.foreach.plrs.iteration < 5}
                                    <div class="ty-product-list__player_image">
                                        <a href="{"players.view?player_id=`$player.player_id`"|fn_url}">
                                            {include file="common/image.tpl" obj_id=$obj_id_prefix images=$player.main_pair image_width="90" image_height="90"}
                                        </a>
                                        <div>{$player.player}</div>
                                    </div>
                                {/if}
                            {/foreach}
                        </div>
                    </div>
                {/if}
                {hook name="products:left_block"}
                {/hook}
            </div>
            <div class="ty-product-block__left" id="product_page_right">
                {if $product.discussion.posts || $product.header_features}
                    <div class="ty-product-detail__before-title">
                    {hook name="products:brand"}
                        <div class="ty-product-detail__brand-image">
                            {$brand_id = $smarty.const.BRAND_FEATURE_ID}
                            {$brand_variant_id = $product.header_features.$brand_id.variant_id}
                            {include file="addons/development/common/brand_logo.tpl" brand=$product.header_features.$brand_id.variants.$brand_variant_id brand_variant_id=$brand_variant_id itemprop="brand"}
                        </div>
                    {/hook}
                    {hook name="products:main_info_title"}{/hook}
                    </div>
                {/if}
                {if !$hide_title}
                    <div class="ty-product-block-title-wrapper">
                        <h1 itemprop="name" class="ty-product-block-title" {live_edit name="product:product:{$product.product_id}"}>
                            {$product.product nofilter}
                        </h1>
                        {if $product.price > $addons.development.free_shipping_cost}
                        <div class="ty-product-free-shipping">{__("free_shipping_product_text")}</div>
                        {/if}
                        {include file="addons/development/common/product_shipping_estimation.tpl"}
                    </div>
                {/if}

                <div class="ty-product-detail__buy-section">
                {assign var="form_open" value="form_open_`$obj_id`"}
                {$smarty.capture.$form_open nofilter}
                {assign var="old_price" value="old_price_`$obj_id`"}
                {assign var="price" value="price_`$obj_id`"}
                {assign var="clean_price" value="clean_price_`$obj_id`"}
                {assign var="list_discount" value="list_discount_`$obj_id`"}

                {hook name="products:promo_text"}
                {if $product.promo_text}
                <div class="ty-product-block__note">
                    {$product.promo_text nofilter}
                </div>
                {/if}
                {/hook}

                <div class="ty-prices-container-wrap">
                    <div class="ty-prices-container-left">
                        <div class="{if $smarty.capture.$old_price|trim || $smarty.capture.$clean_price|trim || $smarty.capture.$list_discount|trim}prices-container {/if}">
                            {if $smarty.capture.$old_price|trim || $smarty.capture.$clean_price|trim || $smarty.capture.$list_discount|trim}
                                <div class="ty-product-prices">
                                    {if $smarty.capture.$old_price|trim}{$smarty.capture.$old_price nofilter}{/if}
                            {/if}

                            {if $smarty.capture.$price|trim}
                                <div class="ty-product-block__price-actual">
                                    {$smarty.capture.$price nofilter}
                                    {assign var="qty_discounts" value="qty_discounts_`$obj_id`"}
                                    {if $smarty.capture.$qty_discounts|trim}
                                        <div class="ty-ti-price-wrap">
                                            {$smarty.capture.$qty_discounts nofilter}
                                        </div>
                                    {/if}
                                    <span class="cm-reload-{$obj_prefix}{$product.product_id} ty-pc-zero-price-note" id="pc_note_{$obj_prefix}{$product.product_id}">
                                        {if $product.product_type == 'C' && !$product.price|floatval}{__("pc_zero_price_note")}{/if}
                                    <!--pc_note_{$obj_prefix}{$product.product_id}--></span>
                                </div>
                            {/if}

                            {if $smarty.capture.$old_price|trim || $smarty.capture.$clean_price|trim || $smarty.capture.$list_discount|trim}
                                    {$smarty.capture.$clean_price nofilter}
                                    {$smarty.capture.$list_discount nofilter}
                                </div>
                            {/if}
                        </div>
                        <div class="ty-found-cheaper-tooltip">{include file="addons/development/common/tooltip.tpl" tooltip_title=__("found_cheaper") note_text=__("found_cheaper_offer") tooltipclass="ty-category-tooltip"}</div>
                        <div class="ty-product-block__advanced-option">
                            {if $capture_options_vs_qty}{capture name="product_options"}{$smarty.capture.product_options nofilter}{/if}
                            {assign var="advanced_options" value="advanced_options_`$obj_id`"}
                            {$smarty.capture.$advanced_options nofilter}
                            {if $capture_options_vs_qty}{/capture}{/if}
                        </div>
                    </div>
                    {if $product.offer_help}
                        <div class="ty-consultation">
                            <div class="ty-consultation-text">{__("expert_consultation")}</div>
                            <div class="ty-consultation-phone">{$settings.Company.company_phone}</div>
                        </div>
                    {/if}
                </div>

                <div class="hidden">
                    {assign var="sku" value="sku_`$obj_id`"}
                    {$smarty.capture.$sku nofilter}
                </div>

                <div class="ty-options-avail-container-wrap">
                    {hook name="products:options_avail_container"}
                    {if $product.product_options}
                    <div class="ty-options-container">
                        {if $capture_options_vs_qty}{capture name="product_options"}{$smarty.capture.product_options nofilter}{/if}
                        <div class="ty-product-block__option">
                            {assign var="product_options" value="product_options_`$obj_id`"}
                            {$smarty.capture.$product_options nofilter}
                        </div>
                        {if $capture_options_vs_qty}{/capture}{/if}
                        
                        {*<div class="ty-product-block__advanced-option">
                            {if $capture_options_vs_qty}{capture name="product_options"}{$smarty.capture.product_options nofilter}{/if}
                            {assign var="advanced_options" value="advanced_options_`$obj_id`"}
                            {$smarty.capture.$advanced_options nofilter}
                            {if $capture_options_vs_qty}{/capture}{/if}
                        </div>*}
                    </div>
                    {/if}
                    {/hook}
                    
                    <div class="ty-avail-container">
                        {if $capture_options_vs_qty}{capture name="product_options"}{$smarty.capture.product_options nofilter}{/if}
                        <div class="ty-product-block__field-group">
                            {assign var="product_amount" value="product_amount_`$obj_id`"}
                            {$smarty.capture.$product_amount nofilter}
                        </div>
                        {if $capture_options_vs_qty}{/capture}{/if}
                    </div>
                </div>

                {if $capture_buttons}{capture name="buttons"}{/if}
                <div class="ty-product-block__button">
                    {assign var="follow" value="follow_`$obj_id`"}
                    {$smarty.capture.$follow nofilter}
                    
                    {if $show_details_button}
                        {include file="buttons/button.tpl" but_href="products.view?product_id=`$product.product_id`" but_text=__("view_details") but_role="submit"}
                    {/if}

                    <div class="ty-product-qty">
                        {assign var="qty" value="qty_`$obj_id`"}
                        {$smarty.capture.$qty nofilter}

                        {assign var="min_qty" value="min_qty_`$obj_id`"}
                        {$smarty.capture.$min_qty nofilter}
                    </div>

                    {assign var="add_to_cart" value="add_to_cart_`$obj_id`"}
                    {$smarty.capture.$add_to_cart nofilter}

                    {assign var="list_buttons" value="list_buttons_`$obj_id`"}
                    {$smarty.capture.$list_buttons nofilter}
                </div>
                {if $capture_buttons}{/capture}{/if}
                {assign var="form_close" value="form_close_`$obj_id`"}
                {$smarty.capture.$form_close nofilter}
                {hook name="products:product_detail_bottom"}
                {/hook}
                </div>

                {if $show_product_tabs}
                    <div class="ty-product-tabs">
                        {include file="views/tabs/components/product_popup_tabs.tpl"}
                        {$smarty.capture.popupsbox_content nofilter}
                        {include file="views/tabs/components/product_tabs.tpl"}

                        {if $blocks.$tabs_block_id.properties.wrapper}
                            {include file=$blocks.$tabs_block_id.properties.wrapper content=$smarty.capture.tabsbox_content title=$blocks.$tabs_block_id.description}
                        {else}
                            {$smarty.capture.tabsbox_content nofilter}
                        {/if}
                        {if $product.feature_comparison == "Y"}
                            <div class="ty-add-to-compare-block-details">
                            {include file="buttons/add_to_compare_list.tpl" product_id=$product.product_id}
                            </div>
                        {/if}
                    </div>
                {/if}
            </div>
        {/if}
    {/hook}
    </div>

    {if $smarty.capture.hide_form_changed == "Y"}
        {assign var="hide_form" value=$smarty.capture.orig_val_hide_form}
    {/if}

</div>

{if $block_tabs.tabs}
    <div class="ty-tennishouse-container ty-products-scroller">
    {$_block_tabs = []}
    {capture name="block_tabsbox"}
        {foreach from=$block_tabs.tabs item="block_data" key="block_tab_id"}
            {if $smarty.capture.$block_tab_id}
                {$_block_tabs.tabs.$block_tab_id = $block_data}
                {$smarty.capture.$block_tab_id nofilter}
            {/if}
        {/foreach}
    {/capture}
    {include file="addons/development/common/tabsbox.tpl" content=$smarty.capture.block_tabsbox navigation=$_block_tabs}
    </div>
{/if}
{include file="addons/development/common/share_buttons.tpl" short=true title=$product.product description=$product.full_description|strip_tags|truncate:160 image=$product.main_pair.detailed.image_path}
[-similar_products-]

{capture name="mainbox_title"}{assign var="details_page" value=true}{/capture}