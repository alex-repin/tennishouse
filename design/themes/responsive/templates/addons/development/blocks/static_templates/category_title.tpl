{if !$category_data.parent_id}
<div class="ty-category-title">
    {if $category_data.main_pair.detailed.image_path}
        <img src="{$category_data.main_pair.detailed.image_path}" alt="{$category_data.category}" width="1015"/>
    {/if}
    <div class="ty-category-title__name">
        {$category_data.category}
    </div>
</div>
{/if}