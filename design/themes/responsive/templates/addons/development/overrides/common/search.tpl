<div class="ty-search-block">
    <form action="{""|fn_url}" name="search_form" method="post" class="cm-ajax">
        <input type="hidden" name="result_ids" value="top_search" />
        <input type="hidden" name="match" value="all" />
        <input type="hidden" name="subcats" value="Y" />
        <input type="hidden" name="status" value="A" />
        <input type="hidden" name="pshort" value="Y" />
        <input type="hidden" name="pfull" value="Y" />
        <input type="hidden" name="pname" value="Y" />
        <input type="hidden" name="pkeywords" value="Y" />
        <input type="hidden" name="search_performed" value="Y" />

        {hook name="search:additional_fields"}{/hook}

        {strip}
            {if $settings.General.search_objects}
                {assign var="search_title" value=__("search")}
            {else}
                {assign var="search_title" value=__("search_products")}
            {/if}
            <input type="text" name="q" value="{$search.q}" id="search_input{if $smarty.capture.search_input_id}_{$smarty.capture.search_input_id}{/if}" title="{$search_title}" class="ty-search-block__input cm-hint" />
            {*if $settings.General.search_objects}
                {include file="buttons/magnifier.tpl" but_name="search.results" alt=__("search")}
            {else}
                {include file="buttons/magnifier.tpl" but_name="products.ajax_search" alt=__("search")}
            {/if*}
            {include file="buttons/magnifier.tpl" but_name="products.ajax_search" alt=__("search")}
        {/strip}

        {capture name="search_input_id"}
            {math equation="x + y" x=$smarty.capture.search_input_id|default:1 y=1 assign="search_input_id"}
            {$search_input_id}
        {/capture}
    </form>
</div>
<div id="top_search">
    <div class="ty-search-results {if !$results}hidden{/if}">
    {if $results}
        {foreach from=$results item="product"}
            <a href="{"products.view?product_id=`$product.product_id`{if $product.ohash}&`$product.ohash`{/if}"|fn_url}"><div class="ty-search-results-item">
                {include file="common/image.tpl" show_detailed_link=false images=$product.main_pair.detailed no_ids=true image_height="40"}
                <span class="ty-search-results-item-title">{$product.product}</span>
            </div></a>
        {/foreach}
    {/if}
    </div>
<!--top_search--></div>