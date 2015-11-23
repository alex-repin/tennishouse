{include file="common/letter_header.tpl"}

{__("dear")} <b>{$order_info.firstname|fn_convert_case}</b>,<br />

{$order_status.email_header nofilter}
{if $order_info.destination_delivery_info}
    <p>
        {$order_info.destination_delivery_info}
    </p>
{/if}
{if $order_info.office_info}
    <p>
    <table cellpadding="1" cellspacing="1" border="0" width="100%">
        <tr>
            <td colspan="2" class="form-title">{__("destination_point")}<hr size="1" noshade></td>
        </tr>
        <tr>
            <td style="font-style: italic;" nowrap>{__("address")}:&nbsp;</td>
            <td >{$order_info.office_info.Address}</td>
        </tr>
        <tr>
            <td style="font-style: italic;" nowrap>{__("worktime")}:&nbsp;</td>
            <td >{$order_info.office_info.WorkTime}</td>
        </tr>
        <tr>
            <td style="font-style: italic;" nowrap>{__("short_phone")}:&nbsp;</td>
            <td >{$order_info.office_info.Phone}</td>
        </tr>
    </table>
    </p>
{/if}
{if $order_info.user_id && $order_info.status == 'A' && $order_info.shipping.0.module == 'sdek'}
    <p>
    {__("order_status_tracking_info", ["[order_page_link]" => "orders.details?order_id=`$order_info.order_id`&selected_section=sdek_information"|fn_url:"C"])}
    </p>
{/if}
<br />


{assign var="order_header" value=__("invoice")}
{if $status_settings.appearance_type == "C" && $order_info.doc_ids[$status_settings.appearance_type]}
    {assign var="order_header" value=__("credit_memo")}
{elseif $status_settings.appearance_type == "O"}
    {assign var="order_header" value=__("order_details")}
{/if}

<b>{$order_header}:</b><br />

{include file="orders/invoice.tpl"}

{include file="common/letter_footer.tpl"}