{if $cart.chosen_shipping.$group_key == $shipping.shipping_id && $shipping.module == 'sdek' && $display == "radio"}

    {if $shipping.data.offices}
        {assign var="shipping_id" value=$shipping.shipping_id}
        {$selected_code = $select_office.$group_key.$shipping_id}

        <input type="hidden" id="elm_s_city_id" name="user_data[s_city_kladr_id]" value="{$user_data.s_city_kladr_id}" />
        <input type="hidden" id="elm_s_city" name="user_data[s_city]" value="{$user_data.s_city}" />
        <input type="hidden" id="elm_s_state" name="user_data[s_state]" value="{$user_data.s_state}" />
        
        <input type="hidden" name="select_office[{$group_key}][{$shipping_id}]" value="{$selected_code}" id="office_id" >
    
        <div class="ty-get-additional-shipping-info">
            <div class="ty-selected-office-data {if !$selected_code || !$shipping.data.offices.$selected_code}hidden{/if}" id="selected_office_data">{if $selected_code && $shipping.data.offices.$selected_code}{$shipping.data.offices.$selected_code.Name} | {if $shipping.data.offices.$selected_code.MetroStation}м. {['Ст.', 'ст.', 'м.', 'М.']|str_replace:'':$shipping.data.offices.$selected_code.MetroStation} | {/if}{$shipping.data.offices.$selected_code.City}, {$shipping.data.offices.$selected_code.Address}{/if}</div>
            {capture name="pickup_location_select"}
                {include file="addons/rus_sdek/components/select_office.tpl" offices=$shipping.data.offices country=$cart.user_data.s_country city=$cart.user_data.s_city city_id=$cart.user_data.s_city_kladr_id state=$cart.user_data.s_state}
            {/capture}

            {$id = "user_location"}
            {if $selected_code && $shipping.data.offices.$selected_code}
                {$link_meta = ""}
                {$link_text = __("change_pickup_location")}
            {else}
                {$link_text = __("select_pickup_location")}
                {$link_meta = "ty-btn"}
            {/if}
            {include file="common/popupbox.tpl"
                content=$smarty.capture.pickup_location_select
                link_text=$link_text
                text=__("pickup_location_selection")
                id="pickup_location"
                link_meta=$link_meta
                full_mode="true"
                edit_onclick="fn_init_ymaps($('#office_list'))"
            }
        </div>
    {/if}

{/if}
