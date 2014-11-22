
{assign var="obj_id" value=$player_data.player_id}
{assign var="obj_id_prefix" value="`$obj_prefix``$obj_id`"}
{include file="common/image.tpl" obj_id=$obj_id_prefix images=$player_data.main_pair.detailed}
<div class="ty-player-detail clearfix">

    <div id="block_player_{$player_data.player_id}" class="clearfix">
        <h1 class="ty-mainbox-title">{$player_data.player}</h1>

        <div class="ty-player-detail__info">
            <div class="ty-player-detail__logo">
                {assign var="capture_name" value="logo_`$obj_id`"}
                {$smarty.capture.$capture_name nofilter}
            </div>
            <div class="ty-player-detail__info-list ty-player-detail_info-first">
                <h5 class="ty-player-detail__info-title">{__("contact_information")}</h5>
                {if $player_data.email}
                    <div class="ty-player-detail__control-group">
                        <label class="ty-player-detail__control-lable">{__("email")}:</label>
                        <span><a href="mailto:{$player_data.email}">{$player_data.email}</a></span>
                    </div>
                {/if}
                {if $player_data.phone}
                    <div class="ty-player-detail__control-group">
                        <label class="ty-player-detail__control-lable">{__("phone")}:</label>
                        <span>{$player_data.phone}</span>
                    </div>
                {/if}
                {if $player_data.fax}
                    <div class="ty-player-detail__control-group">
                        <label class="ty-player-detail__control-lable">{__("fax")}:</label>
                        <span>{$player_data.fax}</span>
                    </div>
                {/if}
                {if $player_data.url}
                    <div class="ty-player-detail__control-group">
                        <label class="ty-player-detail__control-lable">{__("website")}:</label>
                        <span><a href="{$player_data.url}">{$player_data.url}</a></span>
                    </div>
                {/if}
            </div>
            <div class="ty-player-detail__info-list">
                <h5 class="ty-player-detail__info-title">{__("shipping_address")}</h5>

                <div class="ty-player-detail__control-group">
                    <span>{$player_data.address}</span>
                </div>
                <div class="ty-player-detail__control-group">
                    <span>{$player_data.city}
                        , {$player_data.state|fn_get_state_name:$player_data.country} {$player_data.zipcode}</span>
                </div>
                <div class="ty-player-detail__control-group">
                    <span>{$player_data.country|fn_get_country_name}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="ty-player-detail__categories">
        <h3 class="ty-subheader">{__("categories")}</h3>

        <table class="ty-player-detail__table ty-table">
            <thead>
                <tr>
                    <th>{__("name")}</th>
                    <th>{__("products_amount")}</th>
                </tr>
            </thead>
            <tbody>
                {foreach from=$player_categories item="category"}
                    <tr>
                        <td>
                            <a href="{"products.search?search_performed=Y&cid=`$category.category_id`&player_id=`$player_data.player_id`"|fn_url}">{$category.category}</a>
                        </td>
                        <td style="width: 10%" class="ty-right">{$category.count}</td>
                    </tr>
                {/foreach}
            </tbody>
        </table>
    </div>

    {capture name="tabsbox"}
        <div id="content_description"
                class="{if $selected_section && $selected_section != "description"}hidden{/if}">
            {if $player_data.player_description}
                <div class="ty-wysiwyg-content">
                    {$player_data.player_description nofilter}
                </div>
            {/if}
        </div>

    {/capture}
</div>
{include file="common/tabsbox.tpl" content=$smarty.capture.tabsbox active_tab=$smarty.request.selected_section}