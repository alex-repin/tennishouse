<div class="ty-add-to-wish-block">
    {$wishlist_id=$product_id|fn_check_wishlist}
    {include file="buttons/button.tpl" but_id="unlike_`$but_id`" but_meta="{if !$wishlist_id}hidden{/if} ty-added-to-wish {if $state_changed}ty-wishlist-change-state{/if} cm-ajax cm-ajax-hidden" but_text="" but_role="text" but_onclick="fn_unlike();" but_href="wishlist.unlike?product_id=`$product_id`" but_name=""}
    {include file="buttons/button.tpl" but_id="like_`$but_id`" but_meta="{if $wishlist_id}hidden{/if} ty-add-to-wish {if $state_changed}ty-wishlist-change-state{/if} cm-ajax cm-ajax-hidden" but_text="" but_role="text" but_onclick="fn_like();" but_href="wishlist.like?product_id=`$product_id`" but_name=""}
    <span id="likes_num_{$but_id}" class="ty-wishlist-likes {if $likes <= 0}hidden{/if}">{$likes}</span>
    <script type="text/javascript">
        var likes = '{$likes}';
        function fn_unlike()
        {$ldelim}
            $('#unlike_{$but_id}').addClass('hidden');
            $('#like_{$but_id}').addClass('ty-wishlist-change-state').removeClass('hidden');
            likes--;
            $('#likes_num_{$but_id}').html(likes);
            if (likes > 0) {
                $('#likes_num_{$but_id}').removeClass('hidden');
            } else {
                $('#likes_num_{$but_id}').addClass('hidden');
            }
        {$rdelim}
        function fn_like()
        {$ldelim}
            $('#like_{$but_id}').addClass('hidden');
            $('#unlike_{$but_id}').addClass('ty-wishlist-change-state').removeClass('hidden');
            likes++;
            $('#likes_num_{$but_id}').html(likes);
            if (likes > 0) {
                $('#likes_num_{$but_id}').removeClass('hidden');
            } else {
                $('#likes_num_{$but_id}').addClass('hidden');
            }
        {$rdelim}
    </script>
</div>
