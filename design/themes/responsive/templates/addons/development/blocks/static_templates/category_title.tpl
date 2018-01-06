{if $image_title}
    <div class="ty-category-title cm-parallax" style="background: url({$category_data.main_pair.detailed.image_path}) center 109px no-repeat fixed;" data-speed="1.5">
        {*<img src="{$category_data.main_pair.detailed.image_path}" alt="{$category_data.category}" width="1015"/>*}
        <h1 class="ty-image-title__name">
            {$category_data.category}
        </h1>
    </div>
    {if $tb_feature.variants || $stb_feature.variants}
        <div class="ty-categorization clearfix" id="tabs_categorization">
            {assign var="ajax_div_ids" value="product_filters_*,products_search_*,category_products_*,product_features_*,breadcrumbs_*,currencies_*,languages_*,tabs_categorization"}
            {if $tb_feature.variants}
                {if $smarty.server.QUERY_STRING|strpos:"dispatch=" !== false}
                    {assign var="filter_qstring" value=$config.current_url|fn_query_remove:"result_ids":"full_render":"filter_id":"view_all":"req_range_id":"advanced_filter":"subcats":"page":"tc_id"}
                {else}
                    {assign var="filter_qstring" value="products.search"}
                {/if}
                <div class="ty-categorization-tabs clearfix">
                    <div class="ty-categorization-tabs-bg"></div>
                    <div class="ty-categorization-tabs__list-wrapper">
                    <ul class="ty-categorization-tabs__list" {if $tb_feature.variants|count < 4}style="width:{$tb_feature.variants|count * 325.5}px"{else}style="width:100%"{/if}>
                    {$tc_width = 100 / $tb_feature.variants|count}
                    {foreach from=$tb_feature.variants item=tab key=key}
                        {if !$active_tab}
                            {assign var="active_tab" value=$key}
                        {/if}
                        <li class="ty-categorization-tabs__item {if $key == $active_tab} active{/if}" style="width: {$tc_width}%;"><a class="ty-categorization-tabs__a {if $category_data.ajax_pagination == 'Y'}cm-ajax-force cm-ajax cm-ajax-full-render cm-history{/if}" data-ca-scroll=".cm-pagination-container" data-ca-target-id="{$ajax_div_ids}" {if $key != $active_tab}href="{$filter_qstring|fn_link_attach:"tc_id=`$key`"|fn_url}"{/if}>{if $key == $active_tab && $key != 'all'}<h2>{else}<span>{/if}{$tab.variant}{if $key == $active_tab && $key != 'all'}</h2>{else}</span>{/if}</a></li>
                    {/foreach}
                    </ul>
                    </div>
                </div>
            {/if}
        <!--tabs_categorization--></div>
    {/if}
{/if}