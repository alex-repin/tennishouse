{if $brand.image_pair}
{if !$img_height}
    {if $brand_variant_id == $smarty.const.KIRSCHBAUM_BRAND_ID}
        {$img_height = "30"}
    {else}
        {$img_height = "19"}
    {/if}
{/if}
<div class="ty-brand-image" style="height: {$img_height}px;background-image: url('{$brand.image_pair.icon.image_path}');">
</div>
{/if}