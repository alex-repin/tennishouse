{if $auth.user_id}
    <li class="ty-menu__item">
        <div class="ty-menu__submenu-item-header ty-reward-icon">
            <a href="{"reward_points.userlog"|fn_url}" rel="nofollow" class="ty-menu__item-link">{__("reward_points_log")}</a>
        </div>
    </li>
{/if}