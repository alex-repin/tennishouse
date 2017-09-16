{include file="common/letter_header.tpl"}

{__("dear")},<br><br>

{__("text_new_subscriber")}:<br /><br />

{if $promo}
<div style="display: inline-block; text-align: center; width: 100%;">

</div><br /><br /><br />
{/if}

<div>
{__("text_decline_subscription_email")} <a href="{"newsletters.decline_email?ekey=`$ekey`"|fn_url:$zone:'http'}">{__("not_my_account")}</a>
</div>

{include file="common/letter_footer.tpl"}