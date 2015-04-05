{** block-description:tmpl_copyright **}
<p class="bottom-copyright">&copy; {if $smarty.const.TIME|date_format:"%Y" != $settings.Company.company_start_year}{$settings.Company.company_start_year}-{/if}{$smarty.const.TIME|date_format:"%Y"} <a href="{""|fn_url}"><img src="{$images_dir}/addons/development/bottom_logo.png" /></a>
</p>