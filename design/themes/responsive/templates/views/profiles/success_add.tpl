{capture name="mainbox_title"}{__("successfully_confirmed")}{/capture}

<span class="ty-success-registration__text">
    {__("success_registration_text")}
    {if $mail_server}
        <a href="{$mail_server}" target="_blank" class="ty-btn ty-btn__primary">{__("check_email")}</a>
    {/if}
</span>
<ul class="success-registration__list">
    {hook name="profiles:success_registration"}
        <li class="ty-success-registration__item">
            <a href="{"profiles.update"|fn_url}" class="success-registration__a">{__("edit_profile")}</a>
            <span class="ty-success-registration__info">{__("edit_profile_note")}</span>
        </li>
        <li class="ty-success-registration__item">
            <a href="{"orders.search"|fn_url}" class="success-registration__a">{__("orders")}</a>
            <span class="ty-success-registration__info">{__("track_orders")}</span>
        </li>
        {*<li class="ty-success-registration__item">
            <a href="{"product_features.compare"|fn_url}" class="success-registration__a">{__("compare_list")}</a>
            <span class="ty-success-registration__info">{__("compare_list_note")}</span>
        </li>*}
    {/hook}
</ul>
