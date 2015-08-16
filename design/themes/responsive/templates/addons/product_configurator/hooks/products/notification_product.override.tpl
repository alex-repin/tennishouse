{if $product.extra.configuration}
<div class="ty-product-notification__item clearfix">
    {include file="common/image.tpl" image_width="50" image_height="50" images=$product.main_pair no_ids=true class="ty-product-notification__image"}
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
            {foreach from=$added_products item="_product" key="_key"}
                {if $_product.extra.parent.configuration == $key}
                    <div class="ty-product-notification__item clearfix">
                        {include file="common/image.tpl" image_width="50" image_height="50" images=$_product.main_pair no_ids=true class="ty-product-notification__image"}
                        <div class="ty-product-notification__content clearfix">
                            <a href="{"products.view?product_id=`$_product.product_id`"|fn_url}" class="ty-product-notification__product-name">{$_product.product_id|fn_get_product_name nofilter}</a>
                            {if !($settings.General.allow_anonymous_shopping == "hide_price_and_add_to_cart" && !$auth.user_id)}
                                <div class="ty-product-notification__price">
                                    {if !$hide_amount}
                                        <span class="none">{$_product.amount}</span>&nbsp;x&nbsp;{include file="common/price.tpl" value=$_product.display_price span_id="price_`$_key`" class="none"}
                                    {/if}
                                </div>
                            {/if}
                            {if $_product.product_option_data}
                                {include file="common/options_info.tpl" product_options=$_product.product_option_data}
                            {/if}
                        </div>
                    </div>
                {/if}
            {/foreach}
        </div>
    </div>
</div>
{elseif $product.extra.parent.configuration}
    &nbsp;
{/if}