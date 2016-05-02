{** block-description:tmpl_copyright **}
<div class="ty-bottom-contact-info">
    {assign var="mobile_url" value=$config.current_url|fn_query_remove:"dmode"|fn_link_attach:"dmode=F"}
    <a href="{"`$mobile_url`"|fn_url}"><div class="ty-bottom-cell ty-desktop-icon">{__("full_version")}</div></a>
    <a href="tel:{$company_phone}"><div class="ty-bottom-cell ty-phone-icon">{__("short_phone")}: {$settings.Company.company_phone}</div></a>
    <a href="mailto:{$settings.Company.company_users_department}"><div class="ty-bottom-cell  ty-mail-icon">{__("email")}: {$settings.Company.company_users_department}</div></a>
    <a href="{"pages.view?page_id=`$smarty.const.ABOUT_US_PAGE_ID`"|fn_url}"><div class="ty-bottom-cell ty-logo-icon">{__("about_us")}</div></a>
</div>
<div class="ty-bottom-copyright"><p class="bottom-copyright">&copy; {if $smarty.const.TIME|date_format:"%Y" != $settings.Company.company_start_year}{$settings.Company.company_start_year}-{/if}{$smarty.const.TIME|date_format:"%Y"} <a href="{""|fn_url}"><img src="{$images_dir}/addons/development/bottom_logo.png" /></a>
</div>