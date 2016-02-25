{foreach from=$categories_tree item="category"}
    <li class="ty-menu__item {if $category.subitems}dropdown-vertical__dir{/if}{if $category.active || $category|fn_check_is_active_menu_item:$block.type} ty-menu__item-active{/if} menu-level-{$level}">
        {assign var="item_url" value=$category|fn_form_dropdown_object_link:"menu"}
        <div class="ty-menu__submenu-item-header" id="header_{$category.object_id}">
            <a{if $item_url} href="{$item_url}"{/if} {if $category.new_window}target="_blank"{/if} class="ty-menu__item-link">{$category.item}</a>
        </div>
        {if $category.subitems}
            <div class="ty-menu__item-toggle">
                <i class="ty-icon-right-open"></i>
                <i class="ty-icon-down-open"></i>
            </div>
        {/if}

        {if $category.subitems}
            <ul class="ty-menu__submenu-items">
                {include file="addons/development/common/catalog_panel_menu.tpl" categories_tree=$category.subitems separated=true submenu=true iid=$category.$category_id level=$level+1}
            </ul>
            <script type="text/javascript">
                Tygh.$(document).ready(function() {$ldelim}
                    Tygh.$('#header_' + '{$category.object_id}').click(function(e){$ldelim}
                        if(Tygh.$(this).siblings('.ty-menu__submenu-items').is(':visible') == false) {$ldelim}
                            Tygh.$(this).parent().siblings('.ty-menu__item').removeClass('ty-menu__item-expanded').find('.ty-menu__submenu-items').slideUp();
                            Tygh.$(this).parent().siblings('.ty-menu__item').find('.ty-menu__item-toggle').find('.ty-icon-down-open').show();
                            Tygh.$(this).parent().siblings('.ty-menu__item').find('.ty-menu__item-toggle').find('.ty-icon-right-open').hide();
                            
                            Tygh.$(this).siblings('.ty-menu__submenu-items').slideToggle();
                            Tygh.$(this).parent().addClass('ty-menu__item-expanded');
                            Tygh.$(this).siblings('.ty-menu__item-toggle').find('.ty-icon-down-open').hide();
                            Tygh.$(this).siblings('.ty-menu__item-toggle').find('.ty-icon-right-open').show();
                            return false;
                        {$rdelim}
                    {$rdelim});
                {$rdelim});
            </script>
        {/if}
    </li>

{/foreach}