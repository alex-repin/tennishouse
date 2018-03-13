{assign var="result_ids" value="cart_items,checkout_totals,checkout_steps,cart_status*,checkout_cart"}

<form name="checkout_form" class="cm-ajax cm-check-changes" action="{""|fn_url}" method="post" enctype="multipart/form-data">
<input type="hidden" name="redirect_mode" value="cart" />
<input type="hidden" name="result_ids" value="{$result_ids}" />

<h1 class="ty-mainbox-title">{__("cart_contents")}</h1>

{include file="views/checkout/components/cart_items.tpl" disable_ids="button_cart"}
        {include file="buttons/update_cart.tpl" but_id="button_cart" but_name="dispatch[checkout.update]" but_meta="hidden"}

</form>

{include file="views/checkout/components/checkout_totals.tpl" location="cart"}

<div class="buttons-container ty-cart-content__bottom-buttons clearfix">
    <div class="ty-float-right ty-cart-content__right-buttons">
        {*include file="buttons/update_cart.tpl" but_external_click_id="button_cart" but_meta="cm-external-click"*}
        {if $payment_methods}
            {assign var="m_name" value="checkout"}
            {assign var="link_href" value="checkout.checkout"}
            {include file="buttons/proceed_to_checkout.tpl" but_href=$link_href}
        {/if}
    </div>
</div>
{if $checkout_add_buttons}
    <div class="ty-cart-content__payment-methods payment-methods" id="payment-methods">
        <span class="ty-cart-content__payment-methods-title payment-metgods-or">{__("or_use")}</span>
        <table class="ty-cart-content__payment-methods-block">
            <tr>
                {foreach from=$checkout_add_buttons item="checkout_add_button"}
                    <td class="ty-cart-content__payment-methods-item">{$checkout_add_button nofilter}</td>
                {/foreach}
            </tr>
    </table>
    <!--payment-methods--></div>
{/if}
