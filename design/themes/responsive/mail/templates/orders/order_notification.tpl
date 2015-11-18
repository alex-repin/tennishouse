{include file="common/letter_header.tpl"}

{__("dear")} <b>{$order_info.firstname}</b>,<br /><br />

{$order_status.email_header nofilter}
{if $order_info.user_id && $order_info.status == 'A'}
    {__("order_status_tracking_info", ["[order_page_link]" => "orders.details?order_id=`$order_info.order_id`"|fn_url:"C"])}
{/if}
<br /><br />


{assign var="order_header" value=__("invoice")}
{if $status_settings.appearance_type == "C" && $order_info.doc_ids[$status_settings.appearance_type]}
    {assign var="order_header" value=__("credit_memo")}
{elseif $status_settings.appearance_type == "O"}
    {assign var="order_header" value=__("order_details")}
{/if}

<b>{$order_header}:</b><br />

{include file="orders/invoice.tpl"}

{include file="common/letter_footer.tpl"}