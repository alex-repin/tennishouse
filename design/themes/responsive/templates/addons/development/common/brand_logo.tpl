{if !$features}
    {$features = $product|fn_get_product_features_list}
{/if}
{$brand_id = $smarty.const.BRAND_FEATURE_ID}
{$brand_variant_id = $features.$brand_id.variant_id}

{if $brand_variant_id == $smarty.const.KIRSCHBAUM_BRAND_ID}
    {$img_height = "30"}
{else}
    {$img_height = "19"}
{/if}
{include file="common/image.tpl"
show_detailed_link=false
images=$features.$brand_id.variants.$brand_variant_id.image_pair
no_ids=true
image_height=$img_height
keep_transparent=true}