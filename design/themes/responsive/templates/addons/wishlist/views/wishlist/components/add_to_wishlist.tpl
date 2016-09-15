<div class="ty-add-to-wish-block" id="block_{$but_id}">
    {$wishlist_id=$product_id|fn_check_wishlist}
    {if $wishlist_id}
        {$link_href="wishlist.unlike?product_id=`$product_id`"}
    {else}
        {$link_href="wishlist.like?product_id=`$product_id`"}
    {/if}
    {include file="buttons/button.tpl" but_id=$but_id but_meta="{if $wishlist_id}ty-added-to-wish{else}ty-add-to-wish{/if} {if $state_changed}ty-wishlist-change-state{/if} cm-ajax cm-ajax-hidden" but_text="" but_role="text" but_onclick=$but_onclick but_href=$link_href but_target_id="block_`$but_id`" but_name=""}{if $likes > 0}<span class="ty-wishlist-likes">{$likes}</span>{/if}
<!--block_{$but_id}--></div>
{*
but_onclick="if (!$('#`$but_id`').hasClass('ty-added-to-wishlist')) {$ldelim}$('#`$but_id`').addClass('ty-change-state');$('#`$but_id`').removeClass('ty-change-state');$('#`$but_id`').addClass('ty-added-to-wishlist'){$rdelim} else {$ldelim}$('#`$but_id`').addClass('ty-change-state');$('#`$but_id`').removeClass('ty-change-state');$('#`$but_id`').removeClass('ty-added-to-wishlist'){$rdelim};"
*}