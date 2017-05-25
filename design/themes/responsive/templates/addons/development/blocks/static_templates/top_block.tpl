<div class="ty-top-block-wrapper">
<div class="ty-logo-container">
    <a href="{""|fn_url}" title="{$logos.theme.image.alt}">
        <div class="ty-logo-wrapper"><img src="{$logos.theme.image.image_path}" width="{$logos.theme.image.image_x}" height="{$logos.theme.image.image_y}" alt="{$logos.theme.image.alt}" class="ty-logo-container__image" /></div>
    </a>
</div>
<div class="ty-store-phone-block">
    <div class="ty-store-phone"><a href="tel:{$company_phone}">{$settings.Company.company_phone}</a></div>
    <div class="ty-store-phone__call_back"><a id="opener_store_call" class="cm-dialog-opener cm-dialog-auto-size ty-btn ty-btn__text" data-ca-target-id="content_store_call">{__("call_back")}</a></div>
    <div class="hidden" id="content_store_call" title="{__("order_call_back")}">
        {include file="addons/call_requests/views/call_requests/components/call_requests_content.tpl" id="store_call"}
    </div>
</div>
<div class="ty-top-block_top_left-wrapper">
    <div class="ty-top-block_top-wrapper">
        <div class="ty-top-block__search">
            {include file="common/search.tpl"}
        </div>
        <div class="top-my-account">
            {$class = ""|fn_get_my_account_title_class}
            {assign var="dropdown_id" value="my_account"}
            {capture name="my_account_content"}
                {include file="blocks/my_account.tpl" title={__("my_account")} block = ['snapping_id' => 'my_account']}
            {/capture}
            <div class="ty-dropdown-box $class ty-float-right">
                <div id="sw_dropdown_{$dropdown_id}" class="ty-dropdown-box__title cm-combination">
                    {$smarty.capture.title nofilter}
                </div>
                <div id="dropdown_{$dropdown_id}" class="cm-popup-box ty-dropdown-box__content hidden">
                    {$smarty.capture.my_account_content nofilter}
                </div>
            </div>
        </div>
        <div class="top-cart-content">
            {include file="blocks/cart_content.tpl" dropdown_id="cart_content" block=['snapping_id' => 'cart_content', 'properties' => ['products_links_type' => 'thumb', 'display_delete_icons' => 'Y', 'display_bottom_buttons' => 'Y']]}
        </div>
    </div>
    <div class="ty-top-block_bottom-wrapper">
        {*<a class="ty-benefits-guarantees__a" href="{"pages.view?page_id=2"|fn_url}">*}
            <div class="ty-benefits-guarantees__top">
                <div class="ty-benefits-guarantees__icon-block"><div class="ty-benefits-low-price"></div></div>
                <div class="ty-benefits-guarantees__text ty-benefits-guarantees__text-single-line">{__("only_original_goods")}</div>
            </div>
        {*</a>*}
        {*<a class="ty-benefits-guarantees__a" href="{"pages.view?page_id=5"|fn_url}">*}
        <div class="ty-benefits-guarantees__top">
            <div class="ty-benefits-guarantees__icon-block"><div class="ty-benefits-free-shipping"></div></div>
            <div class="ty-benefits-guarantees__text">{__("free_delivery_text")}</div>
        </div>
        {*</a>*}
        {*
        <a class="ty-benefits-guarantees__a" href="{"pages.view?page_id=4"|fn_url}">
        <div class="ty-benefits-guarantees__top">
            <div class="ty-benefits-guarantees__icon-block"><div class="ty-benefits-free-returns"></div></div>
            <div class="ty-benefits-guarantees__text ty-benefits-guarantees__text-single-line">{__("free_returns_text")}</div>
        </div>
        </a>
        *}
        {*<a class="ty-benefits-guarantees__a" href="{"pages.view?page_id=73"|fn_url}">*}
        <div class="ty-benefits-guarantees__top">
            <div class="ty-benefits-guarantees__icon-block"><div class="ty-benefits-paymenton-delivery"></div></div>
            <div class="ty-benefits-guarantees__text">{__("payment_on_delivery_text")}</div>
        </div>
        {*</a>*}
    </div>
</div>
</div>