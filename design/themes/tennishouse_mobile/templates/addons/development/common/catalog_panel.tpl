{strip}
{assign var="foreach_name" value="item_`$iid`"}

<div class="top-menu">

<div class="top-menu ty-menu-vertical">

    <div class="ty-menu__header">{__("products_catalog")}</div>
    <ul class="ty-menu__items">
        {assign var="categories_tree" value=""|fn_get_catalog_panel_categoies}
        {include file="addons/development/common/catalog_panel_menu.tpl" categories_tree=$categories_tree separated=true submenu=true iid=$category.$category_id level=$level+1}
    </ul>
    
    <div class="ty-menu__header">{__("my_account")}</div>
    <ul class="ty-menu__items">
        {include file="addons/development/common//my_account.tpl"}
    </ul>
</div>

</div>
{/strip}