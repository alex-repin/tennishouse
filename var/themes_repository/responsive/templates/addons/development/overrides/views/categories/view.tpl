<div id="category_products_{$block.block_id}">

{if $category_data.note_text}
    <div class="ty-category-title__note-block">{if $category_data.note_url}<a href="{"`$category_data.note_url`"|fn_url}" target="_blank">{/if}<div class="ty-category-title__note"><div class="ty-category-title__note-text">{$category_data.note_text}</div><div class="ty-category-title__note-question"></div></div>{if $category_data.note_url}</a>{/if}</div>
{/if}

{*if $subcategories && !$category_data.category_id|fn_display_subheaders && !$category_data.parent_id}
    {math equation="ceil(n/c)" assign="rows" n=$subcategories|count c="2"}
    {split data=$subcategories size=$rows assign="splitted_subcategories"}
    <ul class="subcategories clearfix">
    {foreach from=$splitted_subcategories item="ssubcateg"}
        {foreach from=$ssubcateg item=category name="ssubcateg"}
            {if $category}
                <div class="ty-column2">
                    <div class="ty-category-block-wrapper" id="category_block_{$category.category_id}" style="background: url('{$category.main_pair.detailed.http_image_path}')">
                        <div class="ty-block-categories__overlay"></div>
                        <div class="ty-block-categories-bottom-right">
                            <a href="{"categories.view?category_id=`$category.category_id`"|fn_url}"><div class="ty-block-categories__item ty-block-categories__title">{$category.category}</div></a>
                        </div>
                    </div>
                    <a href="{"categories.view?category_id=`$category.category_id`"|fn_url}"  id="category_block_link_{$category.category_id}"></a>
                    <script type="text/javascript">
                        Tygh.$(document).ready(function() {$ldelim}
                            $('#category_block_' + '{$category.category_id}').click(function(){$ldelim}
                                $('#category_block_link_' + '{$category.category_id}').click();
                            {$rdelim});
                        {$rdelim});
                    </script>
                </div>
            {/if}
        {/foreach}
    {/foreach}
    </ul>
{/if*}

{if $smarty.request.advanced_filter}
    {include file="views/products/components/product_filters_advanced_form.tpl" separate_form=true}
{/if}

{if $stb_feature.variants}
    {assign var="ajax_div_ids" value="product_filters_*,products_search_*,category_products_*,product_features_*,breadcrumbs_*,currencies_*,languages_*,tabs_categorization,subtabs_categorization"}
    {if $smarty.server.QUERY_STRING|strpos:"dispatch=" !== false}
        {assign var="filter_qstring" value=$config.current_url|fn_query_remove:"result_ids":"full_render":"filter_id":"view_all":"req_range_id":"advanced_filter":"subcats":"page":"stc_id"}
    {else}
        {assign var="filter_qstring" value="products.search"}
    {/if}
    <div class="clearfix">
        {*<div class="ty-categorization-subtabs__title">{$stb_feature.description}:</div>*}
        <div class="ty-categorization-subtabs" id="subtabs_categorization">
            {$cst_width = 100 / $stb_feature.variants|count}
            {strip}
            {foreach from=$stb_feature.variants item=tab key=key}
                {*if !$active_subtab}
                    {assign var="active_subtab" value=$key}
                {/if*}
                {if $tab.variant_id == $smarty.const.KIRSCHBAUM_BRAND_ID}
                    {$img_ht = "50"}
                {else}
                    {$img_ht = "35"}
                {/if}
                <div class="ty-categorization-subtabs__item {if $key == $active_subtab}ty-categorization-subtabs__item-active{/if}" style="width: {$cst_width}%;">
                    <div class="ty-categorization-subtabs__item-logo">{include file="addons/development/common/brand_logo.tpl" brand=$tab brand_variant_id=$tab.variant_id img_height=$img_ht}</div>
                    <h3>
                    <a class="ty-categorization-subtabs__a text-hide {if $category_data.ajax_pagination == 'Y'}cm-ajax-force cm-ajax cm-ajax-full-render cm-history{/if}" data-ca-scroll=".cm-pagination-container" data-ca-target-id="{$ajax_div_ids}" {if $key != $active_subtab}href="{$filter_qstring|fn_link_attach:"stc_id=`$key`"|fn_url}"{else}href="{$filter_qstring|fn_link_attach:"stc_id=all"|fn_url}"{/if}>
                        {$tab.variant}
                    </a>
                    </h3>
                </div>
            {/foreach}
            {/strip}
        <!--subtabs_categorization--></div>
    </div>
{/if}

{if $products}
{assign var="layouts" value=""|fn_get_products_views:false:0}
{if $category_data.product_columns}
    {assign var="product_columns" value=$category_data.product_columns}
{else}
    {assign var="product_columns" value=$settings.Appearance.columns_in_products_list}
{/if}

{if $layouts.$selected_layout.template}
    <div itemscope itemtype="http://schema.org/ItemList">
        {include file="`$layouts.$selected_layout.template`" columns=$product_columns microdata=true category_grid=true ajax_pagination=$category_data.ajax_pagination}
    </div>
{/if}

{elseif !$subcategories || $show_no_products_block}
<p class="ty-no-items cm-pagination-container">{__("text_no_products")}</p>
{else}
<div class="cm-pagination-container"></div>
{/if}
{if ($category_data.full_description || $runtime.customization_mode.live_editor)}
    <div class="ty-wysiwyg-content ty-mb-s ty-no-select" {live_edit name="category:full_description:{$category_data.category_id}"}>{$category_data.full_description nofilter}</div>
{/if}
<!--category_products_{$block.block_id}--></div>

{capture name="mainbox_title"}
{if $category_data.brand.image_pair.icon.image_path}
    <div class="ty-category__title">
        <div class="ty-category__title-logo">
            {if $category.brand_id == $smarty.const.KIRSCHBAUM_BRAND_ID}
                {$img_height = "70"}
            {else}
                {$img_height = "50"}
            {/if}
            {include file="addons/development/common/brand_logo.tpl"  brand=$category_data.brand brand_variant_id=$category_data.brand_id img_height=$img_height}
        </div>
        {if $category_data.description}
            <div class="ty-wysiwyg-content ty-category__title-descr ty-no-select" {live_edit name="category:description:{$category_data.category_id}"}>{$category_data.description nofilter}</div>
        {/if}
    </div>
{/if}
{/capture}
