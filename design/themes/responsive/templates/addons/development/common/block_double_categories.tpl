<div class="{$class_name} ty-block-categories-wrapper">
    <div class="ty-block-caategories__overlay"></div>
    <div class="ty-block-categories-first-wrapper">
        <div class="ty-block-categories">
            <a href="{"categories.view?category_id=$first_category_id"|fn_url}"><div class="ty-block-categories__item ty-block-categories__title">{$first_title}</div></a>
            {$categories = $first_category_id|fn_get_block_categories}
            {foreach from=$categories item="category"}
                <div class="ty-block-categories__item"><a href="{"categories.view?category_id=`$category.category_id`"|fn_url}"> - {$category.category}</a></div>
            {/foreach}
            <div class="ty-block-categories__item"><a href="{"categories.view?category_id=$first_category_id"|fn_url}"> - {__("check_all_items")}</a></div>
        </div>
    </div>
    <div class="ty-block-categories-second-wrapper">
        <div class="ty-block-categories">
            <a href="{"categories.view?category_id=$second_category_id"|fn_url}"><div class="ty-block-categories__item ty-block-categories__title">{$second_title}</div></a>
            {$categories = $second_category_id|fn_get_block_categories}
            {foreach from=$categories item="category"}
                <div class="ty-block-categories__item"><a href="{"categories.view?category_id=`$category.category_id`"|fn_url}"> - {$category.category}</a></div>
            {/foreach}
            <div class="ty-block-categories__item"><a href="{"categories.view?category_id=$second_category_id"|fn_url}"> - {__("check_all_items")}</a></div>
        </div>
    </div>
</div>
<a href="{"categories.view?category_id=$first_category_id"|fn_url}"  class="{$class_name}-link-first"></a>
<a href="{"categories.view?category_id=$second_category_id"|fn_url}"  class="{$class_name}-link-second"></a>
<script type="text/javascript">
    Tygh.$(document).ready(function() {$ldelim}
        $('.ty-block-categories-first-wrapper').click(function(){$ldelim}
            $('.{$class_name}-link-first').click();
        {$rdelim});
        $('.ty-block-categories-second-wrapper').click(function(){$ldelim}
            $('.{$class_name}-link-second').click();
        {$rdelim});
    {$rdelim});
</script>