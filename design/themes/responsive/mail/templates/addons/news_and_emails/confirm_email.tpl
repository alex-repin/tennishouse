{include file="common/letter_header.tpl"}

{__("dear")},<br><br>

{__("text_new_subscriber")}<br /><br />

{if $promo}
<div style="display: inline-block; text-align: center; width: 600px;position:relative;">
    <div style="position: absolute; color: rgb(255, 255, 255); border: 1px solid rgb(255, 255, 255); top: 30px; left: 30px; width: 260px; height: 40px; line-height: 40px;">{$promo.promo_code}</div>
    <a href="{""|fn_url:'C':'http'}" style="background-color: rgb(255, 255, 255); position: absolute; color: rgb(0, 0, 0); border: 1px solid rgb(255, 255, 255); top: 30px; right: 30px; width: 260px; text-transform: uppercase; font-size: 15px; height: 40px; line-height: 40px;">{__("choose_and_buy")}</div>
    <img width="100%" vspace="0" hspace="0" border="0" alt="Promo" style="max-width:600px;display:inline-block;" src="{$images_dir}/addons/development/subscriber_promo.png">
</div><br /><br /><br />
{/if}

<div>
{capture name="link"}
    <a href="{"newsletters.decline_email?ekey=`$ekey`"|fn_url:$zone:'http'}">{__("not_my_account")}</a>
{/capture}
{__("text_decline_subscription_email", ["[link]" => {$smarty.capture.link nofilter}])} 
</div>

{include file="common/letter_footer.tpl"}