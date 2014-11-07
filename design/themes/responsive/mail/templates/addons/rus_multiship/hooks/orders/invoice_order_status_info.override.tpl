{foreach from=$order_info.shipping item="shipping_method"}
    {if $shipping_method.service_code == 'multiship'}
        {assign var="multiship_shipping" value=true}
    {/if}
{/foreach}

{if $multiship_shipping}
<td style="padding-top: 14px;">
    <h2 style="font: bold 17px Tahoma; margin: 0px;">{if $doc_id_text}{$doc_id_text} <br />{/if}{__("order")}&nbsp;#{$order_info.order_id}</h2>
    <table cellpadding="0" cellspacing="0" border="0">
        <tr valign="top">
            <td style="font-size: 12px; font-family: verdana, helvetica, arial, sans-serif; text-transform: uppercase; color: #000000; padding-right: 10px; white-space: nowrap;">{__("status")}:</td>
            <td width="100%" style="font-size: 12px; font-family: Arial;">{$order_status.description}</td>
        </tr>
        <tr valign="top">
            <td style="font-size: 12px; font-family: verdana, helvetica, arial, sans-serif; text-transform: uppercase; color: #000000; padding-right: 10px; white-space: nowrap;">{__("date")}:</td>
            <td style="font-size: 12px; font-family: Arial;">{$order_info.timestamp|date_format:"`$settings.Appearance.date_format`, `$settings.Appearance.time_format`"}</td>
        </tr>
        <tr valign="top">
            <td style="font-size: 12px; font-family: verdana, helvetica, arial, sans-serif; text-transform: uppercase; color: #000000; padding-right: 10px; white-space: nowrap;">{__("payment_method")}:</td>
            <td style="font-size: 12px; font-family: Arial;">{$payment_method.payment|default:" - "}</td>
        </tr>
        {if $order_info.shipping}
            <tr valign="top">
                <td style="font-size: 12px; font-family: verdana, helvetica, arial, sans-serif; text-transform: uppercase; color: #000000; padding-right: 10px; white-space: nowrap;">{__("shipping_method")}:</td>
                <td style="font-size: 12px; font-family: Arial;">
                    {foreach from=$order_info.shipping item="shipping" name="f_shipp"}
                        {$shipping_method.shipping_extra.delivery_name}{if !$smarty.foreach.f_shipp.last}, {/if}
                        {if !$smarty.foreach.f_shipp.last}, {/if}
                        {if $shipments[$shipping.group_key].tracking_number}{assign var="tracking_number_exists" value="Y"}{/if}
                    {/foreach}</td>
            </tr>
            {if $tracking_number_exists && !$use_shipments}
                <tr valign="top">
                    <td style="font-size: 12px; font-family: verdana, helvetica, arial, sans-serif; text-transform: uppercase; color: #000000; padding-right: 10px; white-space: nowrap;">{__("tracking_number")}:</td>
                    <td style="font-size: 12px; font-family: Arial;">
                        {foreach from=$order_info.shipping item="shipping" name="f_shipp"}
                            {include file="common/carriers.tpl" carrier=$shipments[$shipping.group_key].carrier tracking_number=$shipments[$shipping.group_key].tracking_number}
                            {if !empty($smarty.capture.carrier_url)}
                                <a {if $smarty.capture.carrier_url|strpos:"://"}target="_blank"{/if} href="{$smarty.capture.carrier_url nofilter}">{$shipments[$shipping.group_key].tracking_number}</a>
                            {else}
                                {$shipments[$shipping.group_key].tracking_number}
                            {/if}
                            {if !$smarty.foreach.f_shipp.last}, {/if}
                        {/foreach}</td>
                </tr>
            {/if}
        {/if}
    </table>
</td>
{/if}