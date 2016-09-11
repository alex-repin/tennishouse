{if $product.amount > 0}
    {include file="addons/development/blocks/product_templates/tennishouse_template_in_stock.tpl"}
{else}
    {include file="addons/development/blocks/product_templates/tennishouse_template_out_of_stock.tpl"}
{/if}