{if $edit_configuration && (!$product.has_options || $show_product_options || $details_page)}
    {if $extra_button}{$extra_button nofilter}&nbsp;{/if}
    {include file="buttons/add_to_cart.tpl" but_id="button_cart_`$obj_prefix``$obj_id`" but_name="dispatch[checkout.add..`$obj_id`]" but_role=$but_role block_width=$block_width obj_id=$obj_id product=$product but_meta=$add_to_cart_meta but_text=__("save")}
    
    {if $auto_process}
    <script type="text/javascript">
    (function(_, $) {
        $(function() {
            $('#button_cart_{$obj_prefix}{$obj_id}').click();
        });
    }(Tygh, Tygh.$));
    </script>
    {/if}
    {capture name="cart_button_exists"}Y{/capture}
{/if}
