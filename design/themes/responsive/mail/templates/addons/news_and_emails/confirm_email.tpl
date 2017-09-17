{include file="common/letter_header.tpl"}

{__("dear")},<br><br>

{__("text_new_subscriber")}<br /><br />

{if $promo}
<div style="display: inline-block; text-align: center; width: 100%;">

</div><br /><br /><br />
{/if}

<div>
{capture name="link"}
    <a href="{"newsletters.decline_email?ekey=`$ekey`"|fn_url:$zone:'http'}">{__("not_my_account")}</a>
{/capture}
{__("text_decline_subscription_email", ["[link]" => {$smarty.capture.link nofilter}])} 
</div>

{include file="common/letter_footer.tpl"}