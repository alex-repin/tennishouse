{if $runtime.mode != 'add'}
<div id="content_players">
    {include file="addons/development/pickers/players/picker.tpl" data_id="added_players" input_name="product_data[players]" no_item_text=__("text_no_items_defined", ["[items]" => __("players")]) type="links" placement="right" item_ids=$product_data.players}
</div>
<div id="content_technologies">
    {include file="addons/development/pickers/technologies/picker.tpl" data_id="added_technologies" input_name="product_data[technologies]" no_item_text=__("text_no_items_defined", ["[items]" => __("technologies")]) type="links" placement="right" item_ids=$product_data.technologies}
</div>
<div id="content_warehouses">
    {include file="addons/development/pickers/warehouses/picker.tpl" data_id="added_warehouses" input_name="product_data[warehouses]" no_item_text=__("text_no_items_defined", ["[items]" => __("warehouses")]) type="links" placement="right" item_ids=$product_data.warehouses}
</div>
{/if}