{if $products}

    {script src="js/tygh/exceptions.js"}
    {if !$no_pagination}
        {include file="common/pagination.tpl"}
    {/if}
    
    {if $feature_categorization && $feature_category.variants}
        {foreach from=$feature_category.variants item="f_category"}
            {$vt_id = $f_category.variant_id}
            {if $feature_categorization.$vt_id}
                {$products = $feature_categorization.$vt_id}
                <h2 class="ty-categorize__title">{$category_data.category} {$f_category.variant}</h2>
                <div class="ty-categorize__section">
                    {if !$show_empty}
                        {split data=$products size=$columns|default:"2" assign="splitted_products"}
                    {else}
                        {split data=$products size=$columns|default:"2" assign="splitted_products" skip_complete=true}
                    {/if}

                    {math equation="100 / x" x=$columns|default:"2" assign="cell_width"}
                    {if $item_number == "Y"}
                        {assign var="cur_number" value=1}
                    {/if}

                    {* FIXME: Don't move this file *}
                    {script src="js/tygh/product_image_gallery.js"}

                    {if $settings.Appearance.enable_quick_view == 'Y'}
                        {$quick_nav_ids = $products|fn_fields_from_multi_level:"product_id":"product_id"}
                    {/if}
                    <div class="grid-list {if $block.properties.mode == 'S'}ty-small-mode{elseif $block.properties.mode == 'N'}ty-mini-mode{elseif $block.properties.mode == 'M'}ty-micro-mode{/if}">
                        {strip}
                            {$type_id = $smarty.const.TYPE_FEATURE_ID}
                            {foreach from=$splitted_products item="sproducts" name="sprod"}
                                {foreach from=$sproducts item="product" name="sproducts"}
                                    <div class="ty-column{$columns}">
                                        {if $product}
                                            {include file="addons/development/common/products_list_item.tpl" mode=$block.properties.mode|default:"R"}
                                        {/if}
                                    </div>
                                {/foreach}
                                {if $show_empty && $smarty.foreach.sprod.last}
                                    {assign var="iteration" value=$smarty.foreach.sproducts.iteration}
                                    {capture name="iteration"}{$iteration}{/capture}
                                    {hook name="products:products_multicolumns_extra"}
                                    {/hook}
                                    {assign var="iteration" value=$smarty.capture.iteration}
                                    {if $iteration % $columns != 0}
                                        {math assign="empty_count" equation="c - it%c" it=$iteration c=$columns}
                                        {section loop=$empty_count name="empty_rows"}
                                            <div class="ty-column{$columns}">
                                                <div class="ty-product-empty">
                                                    <span class="ty-product-empty__text">{__("empty")}</span>
                                                </div>
                                            </div>
                                        {/section}
                                    {/if}
                                {/if}
                            {/foreach}
                        {/strip}
                    </div>
                </div>
            {/if}
        {/foreach}
    {else}
        {if !$show_empty}
            {split data=$products size=$columns|default:"2" assign="splitted_products"}
        {else}
            {split data=$products size=$columns|default:"2" assign="splitted_products" skip_complete=true}
        {/if}

        {math equation="100 / x" x=$columns|default:"2" assign="cell_width"}
        {if $item_number == "Y"}
            {assign var="cur_number" value=1}
        {/if}

        {* FIXME: Don't move this file *}
        {script src="js/tygh/product_image_gallery.js"}

        {if $settings.Appearance.enable_quick_view == 'Y'}
            {$quick_nav_ids = $products|fn_fields_from_multi_level:"product_id":"product_id"}
        {/if}
        <div class="grid-list {if $block.properties.mode == 'S'}ty-small-mode{elseif $block.properties.mode == 'N'}ty-mini-mode{elseif $block.properties.mode == 'M'}ty-micro-mode{/if}">
            {strip}
                {$type_id = $smarty.const.TYPE_FEATURE_ID}
                {foreach from=$splitted_products item="sproducts" name="sprod"}
                    {foreach from=$sproducts item="product" name="sproducts"}
                        <div class="ty-column{$columns}">
                            {if $product}
                                {include file="addons/development/common/products_list_item.tpl" mode=$block.properties.mode|default:"R"}
                            {/if}
                        </div>
                    {/foreach}
                    {if $show_empty && $smarty.foreach.sprod.last}
                        {assign var="iteration" value=$smarty.foreach.sproducts.iteration}
                        {capture name="iteration"}{$iteration}{/capture}
                        {hook name="products:products_multicolumns_extra"}
                        {/hook}
                        {assign var="iteration" value=$smarty.capture.iteration}
                        {if $iteration % $columns != 0}
                            {math assign="empty_count" equation="c - it%c" it=$iteration c=$columns}
                            {section loop=$empty_count name="empty_rows"}
                                <div class="ty-column{$columns}">
                                    <div class="ty-product-empty">
                                        <span class="ty-product-empty__text">{__("empty")}</span>
                                    </div>
                                </div>
                            {/section}
                        {/if}
                    {/if}
                {/foreach}
            {/strip}
        </div>
    {/if}
    
    {if !$no_pagination}
        {include file="common/pagination.tpl"}
    {/if}
{/if}

{capture name="mainbox_title"}{$title}{/capture}