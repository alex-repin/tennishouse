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
        <li id="{$key}" class="ty-tabs__item{if $tab.js} cm-js{elseif $tab.ajax} cm-js cm-ajax{/if}{if $key == $active_tab} active{/if}"><h2><a class="ty-tabs__a" {if $tab.href} href="{$tab.href|fn_url}"{/if}>{$tab.title}</a></h2></li>
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
    $(document).ready(function(){
        if ($('#product_page_left').length && $('#product_page_right').length) {
            var left = $('#product_page_left').outerHeight(true) + 90;
            var right = $('#product_page_right').outerHeight(true);
            var tabs_height = $('#tabs_content').outerHeight(true);
            var mx_hght = 0;
            $('#tabs_content').children().each(function(){
                if ($(this).attr('id') != 'content_technologies' && mx_hght < $(this).outerHeight(true)) {
                    mx_hght = $(this).outerHeight(true);
                }
            });
            if (left > right) {
                $('#tabs_content').css("max-height", mx_hght - 24 + left - right + 'px');
                $('#tabs_content').children().each(function(){
                    $(this).css("height", mx_hght + left - right + 'px');
                });
            } else {
                $('#tabs_content').css("max-height", tabs_height - right + left + 100 + 'px');
                $('#tabs_content').children().each(function(){
                    $(this).css("height", tabs_height - right + left + 100 + 'px');
                });
            }
        } else if ($('#tabs_content').length) {
            var mx_hght = 0;
            $('#tabs_content').children().each(function(){
                if ($(this).attr('id') != 'content_technologies' && mx_hght < $(this).outerHeight(true)) {
                    mx_hght = $(this).outerHeight(true);
                }
            });
            $('#tabs_content').css("max-height", mx_hght + 30 + 'px');
            $('#tabs_content').children().each(function(){
                $(this).css("height", mx_hght + 'px');
            });
        }
    });
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