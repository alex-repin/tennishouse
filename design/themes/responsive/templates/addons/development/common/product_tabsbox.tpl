{if $content|trim}
{if !$active_tab}
    {assign var="active_tab" value=$smarty.request.selected_section}
{/if}

{if $navigation.tabs}

{assign var="empty_tab_ids" value=$content|empty_tabs}
{assign var="_tabs" value=false}
{if $top_order_actions}{$top_order_actions nofilter}{/if}
{script src="js/tygh/tabs.js"}
<div class="ty-tabs cm-j-tabs{if $track} cm-track{/if} clearfix">
    <ul class="ty-tabs__list" {if $tabs_section}id="tabs_{$tabs_section}"{/if}>
    {foreach from=$navigation.tabs item=tab key=key name=tabs}
        {if ((!$tabs_section && !$tab.section) || ($tabs_section == $tab.section)) && !$key|in_array:$empty_tab_ids && $tab.display}
        {if !$active_tab && ($key != 'description' || ($key == 'description' && (($product.full_description || $product.short_description) || ($product.product_features && (!$product.prices || !($product.full_description || $product.short_description))))))}
            {assign var="active_tab" value=$key}
        {/if}
        {assign var="_tabs" value=true}
        <li id="{$key}" class="ty-tabs__item{if $tab.js} cm-js{elseif $tab.ajax} cm-js cm-ajax{/if}{if $key == $active_tab} active{/if}"><a class="ty-tabs__a" {if $tab.href} href="{$tab.href|fn_url}"{/if}>{$tab.title}</a></li>
        {/if}
    {/foreach}
    </ul>
</div>

{if $_tabs}
<div class="cm-tabs-content ty-tabs__content clearfix" id="tabs_content">
    {$content nofilter}
</div>
<script type="text/javascript">
(function(_, $) {
    var max_height = 120;
    {if $product.image_pairs}
        max_height += 50;
    {/if}
    {if $product.players}
        max_height += 175;
    {/if}
    {if $smarty.capture.block_product_cross_sales}
        max_height += 190;
    {/if}
    var mx_hght = 0;
    $('#tabs_content').children().each(function(){
        if (mx_hght < $(this).outerHeight(true)) {
            mx_hght = $(this).outerHeight(true);
        }
    });
    if (max_height < mx_hght) {
        diff = mx_hght - max_height;
        max_height += (diff > 100) ? 100 : diff;
    }
    if (max_height) {
        $('#tabs_content').css("max-height", max_height + 'px');
        $('#tabs_content').children().each(function(){
            $(this).css("height", max_height + 'px');
        });
    }
}(Tygh, Tygh.$));
</script>
{/if}

{if $onclick}
<script type="text/javascript">
    var hndl = {$ldelim}
        'tabs_{$tabs_section}': {$onclick}
    {$rdelim}
</script>
{/if}
{else}
    {$content nofilter}
{/if}
{/if}