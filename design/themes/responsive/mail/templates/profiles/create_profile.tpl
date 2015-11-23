{include file="common/letter_header.tpl"}

{__("dear")},<br><br>

{__("create_profile_notification_header")} {$company_data.company_name}.<br><br>

{hook name="profiles:create_profile"}
{/hook}

{include file="profiles/profiles_info.tpl" created=true}<br /><br />

{__("text_confirm_email")}:<br />

<a href="{"profiles.confirm_email?ekey=`$ekey`"|fn_url:$zone:'http'}">{"profiles.confirm_email?ekey=`$ekey`"|fn_url:$zone:'http'}</a><br /><br />

{__("text_decline_email")}:<br />

<a href="{"profiles.decline_email?ekey=`$ekey`"|fn_url:$zone:'http'}">{"profiles.decline_email?ekey=`$ekey`"|fn_url:$zone:'http'}</a>

{include file="common/letter_footer.tpl"}