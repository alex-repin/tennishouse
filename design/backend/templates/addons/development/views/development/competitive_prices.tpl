{capture name="mainbox"}

<form action="{""|fn_url}" method="post" name="c_prices_form">

{if $competitive_prices}
    {foreach from=$competitive_prices item=category}
        <div class="center ty-c-prices-title">{$category.category}</div>
        <table width="100%" class="table table-middle">
            {foreach from=$category.products item=product}
                <tr class="">
                    <td width="1%" class="center">
                        <input type="checkbox" name="product_ids[]" value="{$product.product_id},{$product.item_id}" class="checkbox cm-item" /></td>
                    <td width="20%"><a href="{"products.update?product_id=`$product.product_id`"|fn_url}">{$product.product}</a></td>
                    <td width="5%">{$product.product_code}</td>
                    <td width="7%" class="{if $product.price < $product.c_price}ty-c-price-lower{else}ty-c-price-higher{/if}">{include file="common/price.tpl" value=$product.price}</td>
                    <td width="7%">{include file="common/price.tpl" value=$product.c_price}</td>
                    <td width="5%">{$product.code}</td>
                    <td width="40%"><a target="_blank" href="{$product.link}">{$product.name}</a></td>
                    <td width="2%">{if $product.in_stock != 'Y'}{__("cprices_out_of_stock")}{/if}</td>
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
            <li>{btn type="list" text=__("eqialize_prices") dispatch="dispatch[development.eqialize_prices]" form="c_prices_form"}</li>
        {/capture}
        {dropdown content=$smarty.capture.tools_list}
    {/if}
{/capture}
</form>
{/capture}

{include file="common/mainbox.tpl" title=__("competitive_prices") content=$smarty.capture.mainbox buttons=$smarty.capture.buttons}
