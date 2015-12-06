{if $oi.extra.configuration_data}
    <tr>
        <td style="padding: 5px 10px; background-color: #ffffff; font-size: 12px; font-family: Arial;">
            {$oi.product|default:__("deleted_product") nofilter}
            {hook name="orders:product_info"}
            {if $oi.product_code}<p style="margin: 2px 0px 3px 0px;">{__("sku")}: {$oi.product_code}</p>{/if}
            {/hook}
            {if $oi.product_options}{include file="common/options_info.tpl" product_options=$oi.product_options}{/if}
        </td>
        <td style="padding: 5px 10px; background-color: #ffffff; text-align: center; font-size: 12px; font-family: Arial;">{$oi.amount}</td>
        <td style="padding: 5px 10px; background-color: #ffffff; text-align: right; font-size: 12px; font-family: Arial;">{if $oi.extra.exclude_from_calculate}{__("free")}{else}{include file="common/price.tpl" value=$oi.original_price}{/if}</td>
        {if $order_info.use_discount}
        <td style="padding: 5px 10px; background-color: #ffffff; text-align: right; font-size: 12px; font-family: Arial;">{if $oi.extra.discount|floatval}{include file="common/price.tpl" value=$oi.extra.discount}{else}&nbsp;-&nbsp;{/if}</td>
        {/if}
        {if $order_info.taxes && $settings.General.tax_calculation != "subtotal"}
            <td style="padding: 5px 10px; background-color: #ffffff; text-align: right; font-size: 12px; font-family: Arial;">{if $oi.tax_value}{include file="common/price.tpl" value=$oi.tax_value}{else}&nbsp;-&nbsp;{/if}</td>
        {/if}

        <td style="padding: 5px 10px; background-color: #ffffff; text-align: right; white-space: nowrap; font-size: 12px; font-family: Arial;"><b>{if $oi.extra.exclude_from_calculate}{__("free")}{else}{include file="common/price.tpl" value=$oi.display_subtotal}{/if}</b>&nbsp;</td>
    </tr>
    <tr>
        {assign var="_colspan" value="4"}
        {if $order_info.use_discount}{assign var="_colspan" value=$_colspan+1}{/if}
        {if $order_info.taxes}{assign var="_colspan" value=$_colspan+1}{/if}
        <td style="padding: 5px 10px; background-color: #ffffff;" colspan="{$_colspan}">
            <p>{__("configuration")}:</p>


        <table width="100%" cellpadding="0" cellspacing="1" style="background-color: #dddddd;">
        <tr>
            <th width="70%" style="background-color: #eeeeee; padding: 6px 10px; white-space: nowrap; font-size: 12px; font-family: Arial;">{__("product")}</th>
            <th style="background-color: #eeeeee; padding: 6px 10px; white-space: nowrap; font-size: 12px; font-family: Arial;">{__("quantity")}</th>
            <th style="background-color: #eeeeee; padding: 6px 10px; white-space: nowrap; font-size: 12px; font-family: Arial;">{__("unit_price")}</th>
            {if $order_info.use_discount}
                <th style="background-color: #eeeeee; padding: 6px 10px; white-space: nowrap; font-size: 12px; font-family: Arial;">{__("discount")}</th>
            {/if}
            {if $order_info.taxes && $settings.General.tax_calculation != "subtotal"}
                <th style="background-color: #eeeeee; padding: 6px 10px; white-space: nowrap; font-size: 12px; font-family: Arial;">{__("tax")}</th>
            {/if}
            <th style="background-color: #eeeeee; padding: 6px 10px; white-space: nowrap; font-size: 12px; font-family: Arial;">{__("subtotal")}</th>
        </tr>
        {foreach from=$oi.extra.configuration_data item="_product"}
        <tr>
            <td style="padding: 5px 10px; background-color: #ffffff; font-size: 12px; font-family: Arial;">
                {$_product.product|default:__("deleted_product") nofilter}
                {hook name="orders:product_info"}
                {if $_product.product_code}<p style="margin: 2px 0px 3px 0px;">{__("sku")}: {$_product.product_code}</p>{/if}
                {/hook}
                {if $_product.product_options}{include file="common/options_info.tpl" product_options=$_product.product_options}{/if}
            </td>
            <td style="padding: 5px 10px; background-color: #ffffff; text-align: center; font-size: 12px; font-family: Arial;">{$_product.extra.step}</td>
            <td style="padding: 5px 10px; background-color: #ffffff; text-align: right; font-size: 12px; font-family: Arial;">{if $_product.extra.exclude_from_calculate}{__("free")}{else}{include file="common/price.tpl" value=$_product.original_price}{/if}</td>
            {if $order_info.use_discount}
            <td style="padding: 5px 10px; background-color: #ffffff; text-align: right; font-size: 12px; font-family: Arial;">{if $_product.extra.discount|floatval}{include file="common/price.tpl" value=$_product.extra.discount}{else}&nbsp;-&nbsp;{/if}</td>
            {/if}
            {if $order_info.taxes && $settings.General.tax_calculation != "subtotal"}
                <td style="padding: 5px 10px; background-color: #ffffff; text-align: right; font-size: 12px; font-family: Arial;">{if $_product.tax_value}{include file="common/price.tpl" value=$_product.tax_value}{else}&nbsp;-&nbsp;{/if}</td>
            {/if}

            <td style="padding: 5px 10px; background-color: #ffffff; text-align: right; white-space: nowrap; font-size: 12px; font-family: Arial;"><b>{if $_product.extra.exclude_from_calculate}{__("free")}{else}{include file="common/price.tpl" value=$_product.display_subtotal}{/if}</b>&nbsp;</td>
        </tr>
        {/foreach}
        </table>
    </tr>
{/if}