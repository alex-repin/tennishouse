{if $warehouse_data}
    {assign var="id" value=$warehouse_data.warehouse_id}
{else}
    {assign var="id" value=0}
{/if}

{** warehouses section **}

{capture name="mainbox"}

{capture name="tabsbox"}
<form action="{""|fn_url}" method="post" class="form-horizontal form-edit" name="warehouses_form">
<input type="hidden" name="fake" value="1" />
<input type="hidden" name="warehouse_id" value="{$id}" />
<input type="hidden" name="selected_section" value="{$smarty.request.selected_section}" />

<div id="content_detailed">
    <div class="control-group">
        <label for="elm_warehouse_name" class="control-label cm-required">{__("warehouse_name")}</label>
        <div class="controls">
        <input type="text" name="warehouse_data[name]" id="elm_warehouse_name" value="{$warehouse_data.name}" size="25" class="input-long" /></div>
    </div>

    <div class="control-group">
        <label class="control-label" for="elm_warehouse_priority">{__("priority")}:</label>
        <div class="controls">
            <input type="text" name="warehouse_data[priority]" id="elm_warehouse_priority" size="10" value="{$warehouse_data.priority}" class="input-text-short" />
        </div>
    </div>

</div>

<div id="content_products">
    {include file="pickers/products/picker.tpl" data_id="added_products" input_name="warehouse_data[products]" no_item_text=__("text_no_items_defined", ["[items]" => __("products")]) type="links" placement="right" item_ids=$warehouse_data.products}
</div>

<div id="content_addons">
{hook name="warehouses:detailed_content"}
{/hook}
</div>

{capture name="buttons"}
    {if !$id}
        {include file="buttons/save_cancel.tpl" but_name="dispatch[warehouses.update]" but_role="submit-link" but_target_form="warehouses_form" but_meta="cm-save-buttons"}
    {else}
        {include file="buttons/save_cancel.tpl" but_name="dispatch[warehouses.update]" but_role="submit-link" but_target_form="warehouses_form" save=$id but_meta="cm-save-buttons"}
    {/if}
{/capture}
    
</form>

<div class="cm-hide-save-button hidden" id="content_import_inventory">
    {include file="addons/development/components/supplier_stocks.tpl" warehouse_id=$warehouse_data.warehouse_id dispatch="development.update_warehouse_stocks"}
</div>

{/capture}
{include file="common/tabsbox.tpl" content=$smarty.capture.tabsbox active_tab=$smarty.request.selected_section track=true}

{/capture}

{if !$id}
    {assign var="title" value=__("warehouses.new_warehouse")}
{else}
    {assign var="title" value="{__("warehouses.editing_warehouse")}: `$warehouse_data.name`"}
{/if}
{include file="common/mainbox.tpl" title=$title content=$smarty.capture.mainbox buttons=$smarty.capture.buttons}

{** warehouse section **}
