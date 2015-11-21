{include file="common/letter_header.tpl"}

{__("dear")} <b>{if $user_data.firstname}{$user_data.firstname|fn_convert_case}{else}{$user_data.user_type|fn_get_user_type_description|lower|fn_convert_case}{/if}</b>,<br><br>

{__("create_profile_notification_header")} {$company_data.company_name}.<br><br>

{hook name="profiles:create_profile"}
{/hook}

{include file="profiles/profiles_info.tpl" created=true}

{include file="common/letter_footer.tpl"}