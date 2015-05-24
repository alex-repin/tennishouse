<div id="breadcrumbs_{$block.block_id}">

{if $breadcrumbs && $breadcrumbs|@sizeof > 1}
    <div class="ty-breadcrumbs {if $image_title}ty-image-title-breadcrumbs{/if} clearfix">
        {strip}
            {foreach from=$breadcrumbs item="bc" name="bcn" key="key"}
                {if !$smarty.foreach.bcn.last}
                    {if $bc.link}
                        {if $key != "0"}
                            <span class="ty-breadcrumbs__slash">/</span>
                        {/if}
                        <a {if $bc.link}href="{$bc.link|fn_url}"{/if} class="ty-breadcrumbs__a{if $additional_class} {$additional_class}{/if}"{if $bc.nofollow} rel="nofollow"{/if}>{$bc.title|strip_tags|escape:"html" nofilter}</a>
                    {/if}
                {else}
                    {if $key != "0"}
                        <span class="ty-breadcrumbs__slash">/</span>
                    {/if}
                    <span class="ty-breadcrumbs__current">{$bc.title|strip_tags|escape:"html" nofilter}</span>
                {/if}
            {/foreach}
            {include file="common/view_tools.tpl"}
        {/strip}
    </div>
{/if}

<!--breadcrumbs_{$block.block_id}--></div>
