{if $product.discussion && $product.discussion.type != "D" && "CRB"|strpos:$product.discussion.type !== false && !$product.discussion.disable_adding}
    <div class="ty-product-add-review-wrapper" id="product_review_block">
        <div class="ty-product-add-review">
        <div class="ty-product-review-title">{__("used_this_product_`$product.category_type`")}</div>
        <form action="{""|fn_url}" method="post" class="{if !$post_redirect_url}cm-ajax cm-form-dialog-closer{/if} posts-form" name="add_post_form" id="add_post_form_{$obj_prefix}{$obj_id}">
            <input type="hidden" name="result_ids" value="posts_list,new_post,average_rating*">
            <input type ="hidden" name="post_data[thread_id]" value="{$product.discussion.thread_id}" />
            <input type ="hidden" name="redirect_url" value="{$post_redirect_url|default:$config.current_url}" />
            <input type="hidden" name="selected_section" value="" />

            <div id="new_post_{$obj_prefix}{$obj_id}">

            {$user_info = $auth.user_id|fn_get_user_info:true}

            {if $product.discussion.type == "C" || $product.discussion.type == "B"}
                <div class="ty-control-group">
                    <label for="dsc_message_{$obj_prefix}{$obj_id}" class="ty-control-group__title cm-required">{__("your_message")}</label>
                    <textarea id="dsc_message_{$obj_prefix}{$obj_id}" name="post_data[message]" class="ty-input-textarea" placeholder="{__('write_product_review')}">{$product.discussion.post_data.message}</textarea>
                </div>
            {/if}
            <div class="ty-flicker-input">
                <div class="ty-control-group ty-inline-block" style="width: 49%;margin-right: 1px;">
                    <label for="dsc_name_{$obj_prefix}{$obj_id}" class="ty-control-group__title cm-required">{__("reviewer_name")}</label>
                    <input type="text" id="dsc_name_{$obj_prefix}{$obj_id}" name="post_data[name]" value="{if $auth.user_id}{$user_info.firstname} {$user_info.lastname}{elseif $product.discussion.post_data.name}{$product.discussion.post_data.name}{/if}" size="25" class="ty-input-text" placeholder="{__("reviewer_name")}" />
                </div>
                <div class="ty-control-group ty-inline-block" style="width: 50.7%;">
                    <label for="dsc_city_{$obj_prefix}{$obj_id}" class="ty-control-group__title" style="padding-bottom: 7px;">{__("city")}</label>
                    <input type="text" id="dsc_city_{$obj_prefix}{$obj_id}" name="post_data[city]" value="{if $user_info.s_city}{$user_info.s_city}{elseif $product.discussion.post_data.city}{$product.discussion.post_data.city}{/if}" size="25" class="ty-input-text" placeholder="{__("city")}" />
                </div>
            </div>
            <div class="ty-flicker-input">
                {*if $product.discussion.type == "R" || $product.discussion.type == "B"}
                    <div class="ty-control-group ty-inline-block ty-product-review-rate">
                        {$rate_id = "rating_`$obj_prefix``$obj_id`"}
                        <label class="ty-control-group__title cm-required cm-multiple-radios">{__("your_rating")}</label>
                        {include file="addons/discussion/views/discussion/components/rate.tpl" rate_id=$rate_id rate_name="post_data[rating_value]"}
                    </div>
                {/if*}
                <div class="ty-product-review-submit">
                    {include file="buttons/button.tpl" but_text=__("submit") but_meta="ty-btn__review-product" but_role="submit" but_name="dispatch[discussion.add]"}
                </div>
            </div>

            {include file="common/image_verification.tpl" option="use_for_discussion"}

            <!--new_post_{$obj_prefix}{$obj_id}--></div>

        </form>
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
