{** block-description:features **}

{if $product.prices || ($product.full_description || $product.short_description)}
    <div class="ty-product-features">
        {include file="views/products/components/product_features.tpl" product_features=$product.product_features details_page=true}
    </div>
{/if}