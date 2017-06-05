{if $product.discussion && $product.discussion.type != "D" && "CRB"|strpos:$product.discussion.type !== false && !$product.discussion.disable_adding}
    <div class="ty-product-add-review-wrapper" id="product_review_block">
        <div class="ty-product-add-review">
            <div class="ty-product-review-title">{__("used_this_product_`$product.category_type`")}</div>
            {include file="addons/discussion/views/discussion/components/quick_post.tpl" discussion=$product.discussion}
        </div>
    </div>
    <script type="text/javascript">
    (function(_, $) {
        function fn_hide_form(event) {
            if (event.target.id != "product_review_block" && !$(event.target).parents("#product_review_block").size()) { 
                $('#product_review_block div').removeClass('ty-product-add-review-is-focus');
                $('#dsc_message_{$obj_prefix}{$obj_id}').focus(function(e){
                    fn_show_form(e);
                });
                $(this).unbind( event )
            }
        }
        function fn_show_form(event)
        {
            $('#product_review_block div').addClass('ty-product-add-review-is-focus');
            $("body").click(function(e) {
                fn_hide_form(e);
            });
            $(this).unbind( event )
        }
        $('#dsc_message_{$obj_prefix}{$obj_id}').focus(function(e){
            fn_show_form(e);
        });
    }(Tygh, Tygh.$));
    </script>
{/if}
