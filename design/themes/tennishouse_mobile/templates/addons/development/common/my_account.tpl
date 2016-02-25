{if !$auth.user_id}
    {assign var="return_current_url" value=$config.current_url|escape:url}
    <li class="ty-menu__item">
        <div class="ty-menu__submenu-item-header">
            <a href="{"auth.login_form?return_url=`$return_current_url`"|fn_url}" rel="nofollow" class="ty-menu__item-link">{__("sign_in")}</a>
        </div>
    </li>
    <li class="ty-menu__item">
        <div class="ty-menu__submenu-item-header">
            <a href="{"profiles.add?return_url=`$return_current_url`"|fn_url}" rel="nofollow" class="ty-menu__item-link">{__("register")}</a>
        </div>
    </li>
{else}
    <li class="ty-menu__item">
        <div class="ty-menu__submenu-item-header" id="header_{$category.object_id}">
            <a href="{"auth.logout?redirect_url=`$return_current_url`"|fn_url}" rel="nofollow" class="ty-menu__item-link">{__("sign_out")}</a>
        </div>
    </li>
    <a href="{if $runtime.controller == "auth" && $runtime.mode == "login_form"}{$config.current_url|fn_url}{else}{"auth.login_form?return_url=`$return_current_url`"|fn_url}{/if}" {if $settings.Security.secure_auth != "Y"} data-ca-target-id="login_block{$block.snapping_id}" class="cm-dialog-opener cm-dialog-auto-size ty-btn ty-btn__secondary"{else} class="ty-btn ty-btn__primary"{/if} rel="nofollow">{__("sign_in")}</a><a href="{"profiles.add"|fn_url}" rel="nofollow" class="ty-btn ty-btn__primary">{__("register")}</a>
    {if $settings.Security.secure_auth != "Y"}
        <div  id="login_block{$block.snapping_id}" class="hidden" title="{__("sign_in")}">
            <div class="ty-login-popup">
                {include file="views/auth/login_form.tpl" style="popup" id="popup`$block.snapping_id`"}
            </div>
        </div>
    {/if}
{/if}
