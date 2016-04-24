{foreach from=$items_tree item="item"}
    <li class="ty-menu__item {if $item.subitems}dropdown-vertical__dir{/if}{if $item.active || $item|fn_check_is_active_menu_item:$block.type} ty-menu__item-active{/if} menu-level-{$level}">
        {assign var="item_url" value=$item|fn_form_dropdown_object_link:"menu"}
        <div class="ty-menu__submenu-item-header {if $type == 'C'}ty-category-icon-{$item.object_id}{elseif $type == 'A'}ty-menu-icon-{$item.param_id}{/if}">
            <a{if $item_url} href="{$item_url}"{/if} {if $item.new_window}target="_blank"{/if} class="ty-menu__item-link">{$item.item}</a>
        </div>
        {if $item.subitems}
            <div class="ty-menu__item-toggle">
                <i class="ty-icon-right-open"></i>
                <i class="ty-icon-down-open"></i>
            </div>
        {/if}

        {if $item.subitems}
            <ul class="ty-menu__submenu-items">
                {include file="addons/development/common/catalog_panel_menu.tpl" items_tree=$item.subitems level=$level+1}
            </ul>
        {/if}
    </li>
{/foreach}
<script type="text/javascript">
    Tygh.$(document).ready(function() {$ldelim}
        Tygh.$('.ty-menu__submenu-item-header').click(function(e){$ldelim}
            if(Tygh.$(this).siblings('.ty-menu__submenu-items').length && Tygh.$(this).siblings('.ty-menu__submenu-items').is(':visible') == false) {$ldelim}
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
