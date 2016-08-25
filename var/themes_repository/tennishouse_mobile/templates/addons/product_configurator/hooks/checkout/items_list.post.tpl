{if $product.configuration}
    <tr class="ty-pc-configuration"><td colspan="5">
    <div>
    <span class="ty-pc-title">{__("configuration")}:</span>
    <table class="ty-cart-content ty-table ty-pc-table">

    {assign var="prods" value=false}

    <tbody>
    {foreach from=$product.configuration item="_product" key="key_conf"}
    <tr>
        <td class=" ty-cart-content__image-block">
            {if $runtime.mode == "cart" || $show_images}
                <div class="ty-cart-content__image">
                    {if $key_conf == 'CONSULT_STRINGING'}
                        <div class="ty-cart-content__image-consult-stringing"></div>
                    {else}
                        <a href="{"products.view?product_id=`$_product.product_id`{if $_product.ohash}&`$_product.ohash`{/if}"|fn_url}">
                        {include file="common/image.tpl" obj_id=$key_conf images=$_product.main_pair image_width=$settings.Thumbnails.product_cart_thumbnail_width image_height=$settings.Thumbnails.product_cart_thumbnail_height}</a>
                    {/if}
                </div>
            {/if}
        </td>

        <td class=" ty-cart-content__description" style="width: 50%;">
            {if $_product.product_id}
            {strip}
                <a href="{"products.view?product_id=`$_product.product_id`{if $_product.ohash}&`$_product.ohash`{/if}"|fn_url}" class="ty-cart-content__product-title">
                    {$_product.product nofilter}
                </a>
            {/strip}
            {else}
            <div class="ty-cart-content__product-title">{$_product.product nofilter}</div>
            {/if}
            {if $_product.product_options}
                <div class="ty-cart-content__options">
                {include file="views/products/components/product_options.tpl" product_options=$_product.product_options product=$_product name="cart_products" id=$key_conf location="cart" disable_ids=$disable_ids form_name="checkout_form" disabled=true}
                </div>
            {/if}
                            {assign var="name" value="product_options_$key_conf"}
                            {capture name=$name}

                            {capture name="product_info_update"}
                                {hook name="checkout:product_info"}
                                    {if $_product.exclude_from_calculate}
                                        <strong><span class="price">{__("free")}</span></strong>
                                    {elseif $_product.discount|floatval || ($_product.taxes && $settings.General.tax_calculation != "subtotal")}
                                        {if $_product.discount|floatval}
                                            {assign var="price_info_title" value=__("discount")}
                                        {else}
                                            {assign var="price_info_title" value=__("taxes")}
                                        {/if}
                                        <div class="ty-product-extra-info">
                                            <a id="sw_product_discounts_{$key_conf}" class="cm-combination ty-product-extra-info-link detailed-link">{$price_info_title}</a>
                                            <div id="product_discounts_{$key_conf}" class="ty-product-extra-info-block hidden">
                                                <table class="ty-cart-content__more-info ty-table">
                                                    <tr>
                                                        <th class="ty-cart-content__more-info-title">{__("price")}</th>
                                                        {if $_product.discount|floatval}<th class="ty-cart-content__more-info-title">{__("discount")}</th>{/if}
                                                        {if $_product.taxes && $settings.General.tax_calculation != "subtotal"}<th>{__("tax")}</th>{/if}
                                                        <th class="ty-cart-content__more-info-title">{__("quantity")}</th>
                                                        <th class="ty-cart-content__more-info-title">{__("subtotal")}</th>
                                                    </tr>
                                                    <tr>
                                                        <td>{include file="common/price.tpl" value=$_product.original_price span_id="original_price_`$key_conf`" class="none"}</td>
                                                        {if $_product.discount|floatval}<td>{include file="common/price.tpl" value=$_product.discount span_id="discount_subtotal_`$key_conf`" class="none"}</td>{/if}
                                                        {if $_product.taxes && $settings.General.tax_calculation != "subtotal"}<td>{include file="common/price.tpl" value=$_product.tax_summary.total span_id="tax_subtotal_`$key_conf`" class="none"}</td>{/if}
                                                        <td class="ty-center">{if $_product.step}{$_product.step}{else}{$_product.amount}{/if}</td>
                                                        <td>{include file="common/price.tpl" span_id="product_subtotal_2_`$key_conf`" value=$_product.display_subtotal class="none"}</td>
                                                    </tr>
                                                </table>
                                            </div>
                                        </div>
                                    {/if}
                                    {include file="views/companies/components/product_company_data.tpl" company_name=$_product.company_name company_id=$_product.company_id}
                                {/hook}
                            {/capture}
                            {if $smarty.capture.product_info_update|trim}
                                <div class="cm-reload-{$key_conf}" id="product_info_update_{$key_conf}">
                                    {$smarty.capture.product_info_update nofilter}
                                <!--product_info_update_{$key_conf}--></div>
                            {/if}
                            {/capture}

                            {if $smarty.capture.$name|trim}
                            <div class="ty-cart-content__detailed"><a id="sw_options_{$key_conf}" class="cm-combination ty-cart-content__detailed-link detailed-link">{__("text_click_here")}</a></div>

                            <div id="options_{$key_conf}" class="ty-product-options ty-group-block hidden">
                                <div class="ty-group-block__arrow">
                                    <span class="ty-caret-info"><span class="ty-caret-outer"></span><span class="ty-caret-inner"></span></span>
                                </div>
                                {$smarty.capture.$name nofilter}
                            </div>
                            {/if}
        </td>

        <td class=" ty-cart-content__price">
        {include file="common/price.tpl" value=$_product.price span_id="product_price_`$key_conf`" class="ty-sub-price"}
        </td>

        <td class=" ty-cart-content__qty {if $_product.is_edp == "Y" || $_product.exclude_from_calculate} quantity-disabled{/if}">
            {if $use_ajax == true && $cart.amount != 1}
                {assign var="ajax_class" value="cm-ajax"}
            {/if}

            <div class="quantity">
                <label for="amount_{$key_conf}"></label>
                    {if $_product.step}{$_product.step}{else}{$_product.amount}{/if}
                <input type="hidden" name="cart_products[{$key_conf}][product_id]" value="{$_product.product_id}" />
            </div>
        </td>

        <td class=" ty-cart-content__price">
            {include file="common/price.tpl" value=$_product.display_subtotal span_id="product_subtotal_`$key_conf`" class="price"}
        </td>
    </tr>
    {/foreach}
    </tbody>
    </table>
    </div>
    </td></tr>
{/if}