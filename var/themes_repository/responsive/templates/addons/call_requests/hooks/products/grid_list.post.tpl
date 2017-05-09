{if $category_grid && $addons.call_requests.buy_now_with_one_click == "Y"}

{$id = "call_request"}

<div class="hidden" id="content_{$id}" title="{__("call_requests.buy_now_with_one_click_`$category_data.type`")}">
    {include file="addons/call_requests/views/call_requests/components/call_requests_content.tpl" id=$id}
</div>

{/if}