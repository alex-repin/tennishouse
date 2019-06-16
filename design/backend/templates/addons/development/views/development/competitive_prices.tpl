{capture name="mainbox"}

<form action="{""|fn_url}" method="post" name="c_prices_form">

<div class="cm-j-tabs cm-track tabs tabs-with-conf">
    <ul class="nav nav-tabs">
        <li id="group_different" class="{if $competition == 'D'} active extra-tab{/if}">
            <a href="{"development.competitive_prices?mode=D"|fn_url}">{__("cprices_differ")}</a>
        </li>
        <li id="group_no_competitor" class="{if $competition == 'N'} active extra-tab{/if}">
            <a href="{"development.competitive_prices?mode=N"|fn_url}">{__("cprices_no_competitor")}</a>
        </li>
    </ul>
</div>

{if $competitive_prices}
    {foreach from=$competitive_prices item=category}
        <div class="center ty-c-prices-title">{$category.category_id|fn_get_category_name}</div>
        <table width="100%" class="table table-middle">
            {foreach from=$category.products item=product}
                <tr class="">
                    <td width="1%" class="center">
                        <input type="checkbox" name="product_ids[]" value="{$product.product_id},{$product.c_item_id}" class="checkbox cm-item" /></td>
                    <td width="20%"><a href="{"products.update?product_id=`$product.product_id`"|fn_url}">{$product.product}</a></td>
                    <td width="5%">{$product.product_code}</td>
                    <td width="7%" class="{if $product.price < $product.c_price}ty-c-price-lower{else}ty-c-price-higher{/if}">{include file="common/price.tpl" value=$product.price}</td>
                    <td width="7%">{include file="common/price.tpl" value=$product.c_price}</td>
                    <td width="5%">{$product.c_code}</td>
                    <td width="40%"><a target="_blank" href="{$product.c_link}">{$product.c_name}</a></td>
                    <td width="2%">{if $product.c_in_stock != 'Y'}{__("cprices_out_of_stock")}{/if}</td>
                </tr>
            {/foreach}
        </table>
    {/foreach}
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
{/capture}
</form>
{/capture}

{include file="common/mainbox.tpl" title=__("competitive_prices") content=$smarty.capture.mainbox buttons=$smarty.capture.buttons}
