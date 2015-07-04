<div class="ty-wysiwyg-content">
    {hook name="pages:page_content"}
    <div class="ty-page-description" {live_edit name="page:description:{$page.page_id}"}>
        {$page.description|fn_check_vars|fn_render_page_blocks:$smarty.capture nofilter}
    </div>
    {/hook}
</div>

{if $page.vk_comments == 'Y'}
    
    {script src="js/addons/development/open_api.js"}
    <div id="vk_comments"></div>
    <script type="text/javascript">
    var app_id = "{$addons.development.application_id}";
    {literal}
    VK.init({
        apiId: app_id,
        onlyWidgets: true
    });
    VK.Widgets.Comments('vk_comments');
    {/literal}
    </script>

{/if}

{capture name="mainbox_title"}{if !$image_title_text}<span {live_edit name="page:page:{$page.page_id}"}>{$page.page}</span>{/if}{/capture}
    
{hook name="pages:page_extra"}
{/hook}