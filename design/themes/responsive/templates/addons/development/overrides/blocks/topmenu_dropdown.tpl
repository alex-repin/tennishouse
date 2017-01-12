{hook name="blocks:topmenu_dropdown"}

{if $items}
    <div class="ty-menu__items">
    <ul class="cm-responsive-menu">
        {hook name="blocks:topmenu_dropdown_top_menu"}
            <li class="ty-menu__item ty-menu__menu-btn visible-phone">
                <a class="ty-menu__item-link">
                    <i class="ty-icon-short-list"></i>
                    <span>{__("menu")}</span>
                </a>
            </li>

        {$item_width = 100 / $items|count}
        {foreach from=$items item="item1" name="item1"}
            {if $item1.type == 'C'}
                {include file="addons/development/common/catalog_top_menu.tpl"}
            {else}
                {assign var="item1_url" value=$item1|fn_form_dropdown_object_link:$block.type}
                {assign var="unique_elm_id" value=$item1_url|md5}
                {assign var="unique_elm_id" value="topmenu_`$block.block_id`_`$unique_elm_id`"}

                <li class="ty-menu__item {if !$item1.$childs} ty-menu__item-nodrop{else} ty-menu__item-parent cm-menu-item-responsive{/if}" style="width: {$item_width}%;">
                    <div class="ty-menu__item_full">
                    {if $item1.$childs}
                        <a class="ty-menu__item-toggle visible-phone cm-responsive-menu-toggle">
                            <i class="ty-menu__icon-open ty-icon-down-open"></i>
                            <i class="ty-menu__icon-hide ty-icon-up-open"></i>
                        </a>
                    {/if}
                    <a {if $item1_url} href="{$item1_url}"{/if} class="ty-menu__item-link">
                        {if $item1.href == 'index.php'}<div class="ty-menu__homepage-link">{/if}{if $item1.href != 'index.php'}{$item1.$name}{else}{/if}{if $item1.href == 'index.php'}</div>{/if}
                    </a>
                    {if $item1.$childs}

                        {if !$item1.$childs|fn_check_second_level_child_array:$childs}
                        {* Only two levels. Vertical output *}
                            <div class="ty-menu__submenu">
                                <ul class="ty-menu__submenu-items ty-menu__submenu-items-simple cm-responsive-menu-submenu">
                                    {hook name="blocks:topmenu_dropdown_2levels_elements"}

                                    {foreach from=$item1.$childs item="item2" name="item2"}
                                        {assign var="item_url2" value=$item2|fn_form_dropdown_object_link:$block.type}
                                        <li class="ty-menu__submenu-item">
                                            <a class="ty-menu__submenu-link" {if $item_url2} href="{$item_url2}"{/if}>{$item2.$name}</a>
                                        </li>
                                    {/foreach}
                                    {if $item1.show_more && $item1_url}
                                        <li class="ty-menu__submenu-item ty-menu__submenu-alt-link">
                                            <a href="{$item1_url}"
                                            class="ty-menu__submenu-alt-link">{__("text_topmenu_view_more")}</a>
                                        </li>
                                    {/if}

                                    {/hook}
                                </ul>
                            </div>
                        {else}
                            <div class="ty-menu__submenu" id="{$unique_elm_id}">
                                {hook name="blocks:topmenu_dropdown_3levels_cols"}
                                    {$submenu_width = 250 * $item1.$childs|count}
                                    {$col_with = 99 / $item1.$childs|count}
                                    <ul class="ty-menu__submenu-items cm-responsive-menu-submenu ty-menu__page-items" style="display: none;width: {$submenu_width}px;">
                                        {foreach from=$item1.$childs item="item2" name="item2"}
                                            <li class="ty-top-mine__submenu-col" style="width: {$col_with}%;">
                                                {assign var="item2_url" value=$item2|fn_form_dropdown_object_link:$block.type}
                                                <div class="ty-menu__submenu-item-header {if $item2.object_id == $smarty.const.SPORTS_NUTRITION_CATEGORY_ID}ty-menu__sports-nutrition{/if}">
                                                    <a{if $item2_url} href="{$item2_url}"{/if} class="ty-menu__submenu-link">{$item2.$name|upper nofilter}</a>
                                                </div>
                                                {if $item2.$childs}
                                                    <a class="ty-menu__item-toggle visible-phone cm-responsive-menu-toggle">
                                                        <i class="ty-menu__icon-open ty-icon-down-open"></i>
                                                        <i class="ty-menu__icon-hide ty-icon-up-open"></i>
                                                    </a>
                                                {/if}
                                                <div class="ty-menu__submenu">
                                                    <ul class="ty-menu__submenu-list cm-responsive-menu-submenu">
                                                        {if $item2.$childs}
                                                            {hook name="blocks:topmenu_dropdown_3levels_col_elements"}
                                                            {if $item2.object_id|fn_display_subheaders}
                                                                <div id="submenu_{$item2.object_id}">
                                                                {foreach from=$item2.$childs item="item3" name="item3"}
                                                                    {assign var="item3_url" value=$item3|fn_form_dropdown_object_link:$block.type}
                                                                    <div class="ty-menu__submenu-item-subheader">
                                                                        <a{if $item3_url} href="{$item3_url}"{/if} class="ty-menu__submenu-link">
                                                                            {$item3.$name nofilter}
                                                                        </a>
                                                                        {include file="addons/development/common/tooltip.tpl" note_url=$item3.note_url note_text=$item3.note_text}
                                                                        <div class="ty-icon-down-open {if (!$item2.expand && $smarty.foreach.item3.first) || ($item2.expand && $item2.expand == $item3.object_id)}hidden{/if}"></div>
                                                                        <div class="ty-icon-right-open {if (!$item2.expand && !$smarty.foreach.item3.first) || ($item2.expand && $item2.expand != $item3.object_id)}hidden{/if}"></div>
                                                                    </div>
                                                                    <div class="ty-menu__menu-subheader-items {if (!$item2.expand && !$smarty.foreach.item3.first) || ($item2.expand && $item2.expand != $item3.object_id)}hidden{/if}">
                                                                    {foreach from=$item3.$childs item="item4" name="item4"}
                                                                        {assign var="item4_url" value=$item4|fn_form_dropdown_object_link:$block.type}
                                                                        <li class="ty-menu__submenu-item">
                                                                            <a{if $item4_url} href="{$item4_url}"{/if} class="ty-menu__submenu-link">{$item4.$name}</a>
                                                                            {include file="addons/development/common/tooltip.tpl" note_url=$item4.note_url note_text=$item4.note_text}
                                                                        </li>
                                                                    {/foreach}
                                                                    {if $item2.show_more && $item2_url}
                                                                        <li class="ty-menu__submenu-item ty-menu__submenu-alt-link">
                                                                            <a href="{$item2_url}" class="ty-menu__submenu-link">{__("text_topmenu_view_more")}</a>
                                                                        </li>
                                                                    {/if}
                                                                    </div>
                                                                {/foreach}
                                                                </div>
                                                            {else}
                                                                {foreach from=$item2.$childs item="item3" name="item3"}
                                                                    {assign var="item3_url" value=$item3|fn_form_dropdown_object_link:$block.type}
                                                                    <li class="ty-menu__submenu-item">
                                                                        <a{if $item3_url} href="{$item3_url}"{/if} class="ty-menu__submenu-link">{$item3.$name}</a>
                                                                    </li>
                                                                {/foreach}
                                                                {if $item2.show_more && $item2_url}
                                                                    <li class="ty-menu__submenu-item ty-menu__submenu-alt-link">
                                                                        <a href="{$item2_url}" class="ty-menu__submenu-link">{__("text_topmenu_view_more")}</a>
                                                                    </li>
                                                                {/if}
                                                            {/if}
                                                            {/hook}
                                                        {/if}
                                                    </ul>
                                                </div>
                                            </li>
                                        {/foreach}
                                        {if $item1.show_more && $item1_url}
                                            <a href="{$item1_url}"><li class="ty-menu__submenu-dropdown-bottom">
                                                <div class="ty-menu__submenu-dropdown-bottom-block">{if $item1.show_more_text}{$item1.show_more_text}{else}{__("text_topmenu_more", ["[item]" => $item1.$name])}{/if}</div>
                                            </li></a>
                                        {/if}
                                    </ul>
                                {/hook}
                            </div>
                        {/if}
                    {/if}
                    </div>
                </li>
            {/if}
        {/foreach}
        {/hook}
    </ul>
    </div>
{/if}
{/hook}
