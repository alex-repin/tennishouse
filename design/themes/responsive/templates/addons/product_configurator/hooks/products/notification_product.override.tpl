{if $product.extra.configuration}
<div class="ty-product-notification__item clearfix">
    {include file="common/image.tpl" image_width="70" image_height="70" images=$product.main_pair no_ids=true class="ty-product-notification__image"}
    <div class="ty-product-notification__content clearfix">
        <div class="ty-product-notification__content-item">
            <a href="{"products.view?product_id=`$product.product_id`"|fn_url}" class="ty-product-notification__product-name">{$product.product_id|fn_get_product_name nofilter}</a>
            {if !($settings.General.allow_anonymous_shopping == "hide_price_and_add_to_cart" && !$auth.user_id)}
                <div class="ty-product-notification__price">
                    {if !$hide_amount}
                        <span class="none">{$product.amount}</span>&nbsp;x&nbsp;{include file="common/price.tpl" value=$product.display_price span_id="price_`$key`" class="none"}
                    {/if}
                </div>
            {/if}
            {if $product.product_option_data}
                {include file="common/options_info.tpl" product_options=$product.product_option_data}
            {/if}
        </div>
        <div class="ty-pc-product-notification">
            {foreach from=$product.configuration item="_product" key="_key"}
                <div class="ty-product-notification__item clearfix">
                    {if $_key == 'CONSULT_STRINGING'}
                        <div class="ty-image-wrapper"><div class="ty-product-notification__image-consult-stringing"></div></div>
                    {else}
                        {include file="common/image.tpl" image_width="50" image_height="50" images=$_product.main_pair no_ids=true class="ty-product-notification__image"}
                    {/if}
                    <div class="ty-product-notification__content clearfix">
                        {if $_product.product_id}<a href="{"products.view?product_id=`$_product.product_id`"|fn_url}" class="ty-product-notification__product-name">{/if}{if $_product.product}{$_product.product}{else}{$_product.product_id|fn_get_product_name nofilter}{/if}{if $_product.product_id}</a>{/if}
                        {if !($settings.General.allow_anonymous_shopping == "hide_price_and_add_to_cart" && !$auth.user_id)}
                            <div class="ty-product-notification__price">
                                {if !$hide_amount}
                                    <span class="none">{if $_product.extra.step}{$_product.extra.step}{else}{$_product.amount}{/if}</span>&nbsp;x&nbsp;{include file="common/price.tpl" value=$_product.price span_id="price_`$_key`" class="none"}
                                {/if}
                            </div>
                        {/if}
                        {if $_product.product_option_data}
                            {include file="common/options_info.tpl" product_options=$_product.product_option_data}
                        {/if}
                    </div>
                </div>
            {/foreach}
        </div>
    </div>
</div>
{elseif $product.extra.parent}
&nbsp;
{/if}