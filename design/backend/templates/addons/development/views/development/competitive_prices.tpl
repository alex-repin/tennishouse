{capture name="mainbox"}

<form action="{""|fn_url}" method="post" name="c_prices_form" class="form-edit">
<input type="hidden" name="mode" value="{$mode}">

<div class="cm-j-tabs cm-track tabs tabs-with-conf">
    <ul class="nav nav-tabs">
        <li id="group_different" class="{if $mode == 'D'} active extra-tab{/if}">
            <a href="{"development.competitive_prices?mode=D"|fn_url}">{__("cprices_differ")}</a>
        </li>
        <li id="group_no_competitor" class="{if $mode == 'N'} active extra-tab{/if}">
            <a href="{"development.competitive_prices?mode=N"|fn_url}">{__("cprices_no_competitor")}</a>
        </li>
        <li id="group_no_competitor_all" class="{if $mode == 'A'} active extra-tab{/if}">
            <a href="{"development.competitive_prices?mode=A"|fn_url}">{__("cprices_no_competitor_all")}</a>
        </li>
    </ul>
</div>

{if $competitive_prices}
    {foreach from=$competitive_prices item=category}
        <div class="center ty-c-prices-title">{$category.category_id|fn_get_category_name}</div>
        <div class="">
            {foreach from=$category.products item=product}
                {if $mode == 'D'}
                    <div class="ty-cp-row">
                        <div class="ty-cp-cell ty-cp-checkbox">
                            <input type="checkbox" name="product_ids[]" value="{$product.product_id},{$product.c_item_id}" class="checkbox cm-item" /></div>
                        <div class="ty-cp-cell ty-cp-org-name"><a href="{"products.update?product_id=`$product.product_id`"|fn_url}">{$product.product}</a></div>
                        <div class="ty-cp-cell ty-cp-code">{$product.product_code}</div>
                        <div class="ty-cp-cell ty-cp-price {if $product.price < $product.c_price}ty-c-price-lower{else}ty-c-price-higher{/if}">{include file="common/price.tpl" value=$product.price}</div>
                        <div class="ty-cp-cell ty-cp-price">{include file="common/price.tpl" value=$product.c_price}</div>
                        <div class="ty-cp-cell ty-cp-code">{$product.c_code}</div>
                        <div class="ty-cp-cell ty-cp-c-name"><a target="_blank" href="{$product.c_link}">{$product.c_name}</a></div>
                        <div class="ty-cp-cell ty-cp-stock">{if $product.c_in_stock != 'Y'}{__("cprices_out_of_stock")}{/if}</div>
                    </div>
                {else}
                    <div class="ty-cp-row">
                        <div class="ty-cp-cell ty-cp-name"><a href="{"products.update?product_id=`$product.product_id`"|fn_url}">{$product.product}</a></div>
                        <div class="ty-cp-cell ty-cp-code">{$product.product_code}</div>
                        <div class="ty-cp-cell ty-cp-price">{include file="common/price.tpl" value=$product.price}</div>
                        <div class="ty-cp-cell ty-cp-item-id">
                            <input type="text" name="pairs[{$product.product_id}]" size="55" value="{if $product.c_item_id}{$product.c_item_id}{/if}" class="ty-cp-item-id-input" id="cp_item_id_{$product.product_id}"/>
                        </div>
                        <div class="ty-cp-cell ty-cp-input">
                            {$product_id = $product.product_id}
                            <input type="text" size="55" value="{$product.product}" data-product-id="{$product.product_id}" class="input-large cm-competitor-products" />
                            {include file="addons/development/views/development/competitive_prices_results.tpl" product_id=$product.product_id}
                        </div>
                    </div>
                {/if}
            {/foreach}
        </div>
    {/foreach}

<script type="text/javascript">
{literal}
    (function (_, $) {
        $('.ty-cp-input').hover(function(){
            $(this).addClass('is-hover');
        }, function(){
            $(this).removeClass('is-hover');
        });
        $('.cm-competitor-products').each(function(){
            $(this).focus(function(){
                $('#cp_variants_' + $(this).data('productId')).show();
            }).blur(function(){
                if (!$(this).parents('.ty-cp-input').hasClass('is-hover')) {
                    $('#cp_variants_' + $(this).data('productId')).hide();
                }
            }).keyup(function(){
                if ($(this).val().length > 2) {
                    function fn_ajax_search(obj, val)
                    {
                        if (val == obj.val()) {
                            $.ceAjax('request', fn_url('development.autocomplete_cproducts'), {
                                method: 'post',
                                hidden: true,
                                force_exec: true,
                                result_ids: 'cp_variants_' + obj.data('productId'),
                                data: {q: obj.val(), id: obj.data('productId')}
                            });
                        }
                    }
                    var val = $(this).val();
                    setTimeout(fn_ajax_search, 500, $(this), val);
                }
            });
        });
    }(Tygh, Tygh.$));
{/literal}
</script>
{else}
    <p class="no-items">{__("no_data")}</p>
{/if}


{capture name="buttons"}
    {if $competitive_prices}
        {capture name="tools_list"}
            <li>{btn type="list" text=__("equalize_prices") dispatch="dispatch[development.eqialize_prices]" form="c_prices_form"}</li>
        {/capture}
        {dropdown content=$smarty.capture.tools_list}
    {/if}
    {include file="buttons/save.tpl" but_role="submit-link" but_name="dispatch[development.add_competitive_pairs]" but_target_form="c_prices_form"}
{/capture}
</form>
{/capture}

{include file="common/mainbox.tpl" title=__("competitive_prices") content=$smarty.capture.mainbox buttons=$smarty.capture.buttons}
