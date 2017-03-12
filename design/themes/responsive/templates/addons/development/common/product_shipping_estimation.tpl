<div id="product_shipping_estimation">
    {if $smarty.session.approx_shipping.city && $smarty.session.approx_shipping.time}
        {capture name="user_location_popup"}
        <form name="user_location_form" action="{""|fn_url}" method="post" class="cm-ajax cm-ajax-force" id="user_location_block">
            <input type="hidden" name="result_ids" value="product_shipping_estimation" />
            <input type="hidden" x-autocompletetype="city_id" name="city_id" value="" />
            <input type="hidden" x-autocompletetype="state" name="state" value="" />
            <input type="hidden" name="user_city" value="" />
            <div class="ty-product-shipping-section">
                {$cities = ""|fn_get_big_cities}
                {$c_columns = "3"}
                <div class="ty-product-shipping-title">{__("select_other_city")}</div>
                {$biggest = $cities|array_shift}
                <div class="ty-select-city-block">
                    <div class="ty-select-city-row">
                        <div class="ty-select-city-item" user-city="{$biggest.city}" user-state="{$biggest.state}"><strong>{$biggest.city}</strong></div>
                    </div>
                    {split data=$cities size=$c_columns assign="splitted_cities"}
                    {foreach from=$splitted_cities item="scities"}
                        <div class="ty-select-city-row">
                        {foreach from=$scities item="city"}
                            <div class="ty-select-city-item" user-city="{$city.city}" user-state="{$city.state}">{$city.city}</div>
                        {/foreach}
                        </div>
                    {/foreach}
                </div>
            </div>
            <div class="ty-product-shipping-section">
                <div class="ty-product-shipping-title">{__("enter_another_city")}</div>
                <div class="ty-product-shipping-input">
                    <input x-autocompletetype="city" type="text" name="city" size="32" value="" class="ty-input-text "/>
                </div>
                {include file="buttons/button.tpl" but_text=__("select") but_name="dispatch[development.update_user_city]" but_meta="ty-btn__secondary cm-form-dialog-closer" but_id="user_location_submit"}
            </div>
            <script type="text/javascript">
            {literal}
            (function(_, $) {
                $(function() {
                    $('#user_location_block').each(function() {
                        fn_init_autocomplete($(this));
                        $('.ty-select-city-item').click(function() {
                            $("[name='user_city']").val($(this).attr('user-city'));
                            $("[name='state']").val($(this).attr('user-state'));
                            $('#user_location_submit').click();
                        });
                    });
                });
            }(Tygh, Tygh.$));
            {/literal}
            </script>
        </form>
        {/capture}

        {capture name="user_location_link"}
            {$id = "user_location"}
            {include file="common/popupbox.tpl"
                content=$smarty.capture.user_location_popup
                link_text=$smarty.session.approx_shipping.city
                text=__("your_city", ['[city]' => $smarty.session.approx_shipping.city])
                id=$id
                link_meta="ty-product-shipping-link"
            }        
        {/capture}
        <div class="ty-product-shipping-estimation">{__("product_shipping_estimation_text", ['[city]' => $smarty.capture.user_location_link, '[ship_time]' => $smarty.session.approx_shipping.time])}</div>
    {elseif !$smarty.session.approx_shipping.is_complete}
        {literal}
        <script type="text/javascript">
            $.ceAjax('request', fn_url('development.product_shipping_estimation'), {
                hidden: true,
                result_ids: 'product_shipping_estimation',
                method: 'get'
            });
        </script>
        {/literal}
    {/if}
<!--product_shipping_estimation--></div>
