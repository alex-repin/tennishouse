{if !$features}
    {$features = $product|fn_get_product_features_list}
{/if}
{$brand_id = $smarty.const.BRAND_FEATURE_ID}
{$brand_variant_id = $features.$brand_id.variant_id}

<img src="{$features.$brand_id.variants.$brand_variant_id.image_pair.icon.image_path}" alt="{$features.$brand_id.variants.$brand_variant_id.variant}" />
