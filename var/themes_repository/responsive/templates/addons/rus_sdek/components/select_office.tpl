<div class="ty-pickup-location-container">
    <div id="map" class="ty-pickup-location-container-map"></div>
    <div class="ty-pickup-location-container-list" id="ymaps_block">
        {assign var="office_count" value=$offices|count}
        <div class="ty-checkout-select-office" id="office_list">
            <div class="ty-checkout-select-office-city" id="ymaps_select_city" data-callback="fn_reload_city_offices(true)">
                <div>{__("locality")}</div>
                <div class="ty-product-shipping-input">
                    <input type="hidden" id="s_country" data-autocompletetype="country" value="{$country}" autocomplete="off"/>
                    <input type="hidden" id="s_state" data-autocompletetype="state" value="{$state}" autocomplete="off"/>
                    <input type="hidden" id="s_city_id" data-autocompletetype="city_id" value="{$city_id}" autocomplete="off"/>
                    <input data-autocompletetype="city" id="s_city" type="text" size="32" value="{$city}" class="ty-input-text" tabindex="-1" autocomplete="off"/>
                </div>
            </div>
            {foreach from=$offices item=office}
                <div class="ty-one-office" id="office_{$office.Code}" onclick="fn_ymaps_show_office($(this).data('ymapsCode'));" data-ymaps-coord-x="{$office.coordX}" data-ymaps-coord-y="{$office.coordY}" data-ymaps-code="{$office.Code}">
                    <div class="ty-one-office__name">{$office.Name}</div>
                    <div class="ty-one-office__description">
                        {$office.City}, {$office.Address}<br />
                        {if $office.MetroStation}{__('metro_station')}: {['Ст.', 'ст.', 'м.', 'М.']|str_replace:'':$office.MetroStation}<br />{/if}
                        {$office.WorkTime}<br />
                    </div>
                </div>
            {foreachelse}
                <div class="ty-checkout-select-office-empty">{__("no_offices_in_this_city")}</div>
            {/foreach}
            {if $offices}
                <div class="ty-checkout-select-office-zoom" id="no_offices_zoom_tip">{__("zoom_out_to_see_offices")}</div>
                <div class="ty-checkout-select-office-zoom" id="more_offices_zoom_tip">{__("zoom_out_to_see_more_offices")}</div>
            {/if}
        </div>
        {if $offices}
            <div class="ty-checkout-select-office-details" id="office_details_list">
                <div onclick="fn_ymaps_hide_office();" class="ty-office-details-back"><div>{__('back_to_list')}</div></div>
                <div id="office_details_list_block">
                    {foreach from=$offices item=office}
                        <div class="ty-one-office-details" id="office_{$office.Code}_details">
                            <div class="ty-one-office__name" id="office_name_{$office.Code}">{$office.Name}</div>
                            <div class="ty-one-office__description">
                                <div id="office_address_{$office.Code}">{$office.City}, {$office.Address}</div>
                                {if $office.MetroStation}<div id="office_metro_{$office.Code}" data-metro-station="м. {['Ст.', 'ст.', 'м.', 'М.']|str_replace:'':$office.MetroStation}">{__('metro_station')}: {['Ст.', 'ст.', 'м.', 'М.']|str_replace:'':$office.MetroStation}</div>{/if}
                                <div class="ty-one-office__description-title">{__("phone")}</div>
                                {$office.Phone}<br />
                                <div class="ty-one-office__description-title">{__("working_hours_text")}</div>
                                {$work_time = ','|fn_explode:$office.WorkTime}
                                {foreach from=$work_time item="time"}
                                    {$time}<br />
                                {/foreach}
                                {if $office.AddressComment}
                                    <div class="ty-one-office__description-title">{__("how_to_get")}</div>
                                    {$office.AddressComment}<br />
                                {/if}
                            </div>
                            {include file="buttons/button.tpl" but_text=__("select_this_office") but_meta="ty-btn__secondary cm-dialog-closer ty-select-office" but_id="select_this_office" but_onclick="fn_select_office('`$office.Code`');"}
                        </div>
                    {/foreach}
                </div>
            </div>
        {/if}
    <!--ymaps_block--></div>
</div>