{** block-description:description **}

{if $product.full_description}
    <div {live_edit name="product:full_description:{$product.product_id}"} class="ty-product-description" {if $product.prices}style="margin-bottom: 30px;"{/if}>{$product.full_description nofilter}</div>
{elseif $product.short_description}
    <div {live_edit name="product:short_description:{$product.product_id}"} class="ty-product-description" {if $product.prices}style="margin-bottom: 30px;"{/if}>{$product.short_description nofilter}</div>
{/if}

{if $product.product_features|count <= 6 && (!$product.prices || !($product.full_description || $product.short_description))}
    <div class="ty-product-features" {if $product.prices}style="margin-bottom: 30px;"{/if}>
        {include file="views/products/components/product_features.tpl" product_features=$product.product_features details_page=true}
    </div>
{/if}
{if $product.prices}
    {include file="views/products/components/products_qty_discounts.tpl"}
{/if}