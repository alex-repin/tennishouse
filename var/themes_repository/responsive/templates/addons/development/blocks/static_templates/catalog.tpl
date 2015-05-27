<p class="ty-footer-menu__header">
    <span>{__("catalog")}</span>
</p>
<ul class="ty-footer-menu__items">
    {$categories = "0"|fn_get_categories_tree}
    {foreach from=$categories item="category"}
        <li class="ty-footer-menu__item"><a href="{"categories.view?category_id={$category.category_id}"|fn_url}">{$category.category}</a></li>
    {/foreach}
    <li class="ty-footer-menu__item"><a href="{"players.list"|fn_url}">{__("players")}</a></li>
    <li class="ty-footer-menu__item"><a href="{"pages.view?page_id=53"|fn_url}">{__("learning_center")}</a></li>
</ul>