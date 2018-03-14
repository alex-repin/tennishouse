<div id="content_cross_categories">
    {include file="pickers/categories/picker.tpl" data_id="added_products" input_name="category_data[cross_categories]" no_item_text=__("text_no_items_defined", ["[items]" => __("categories")]) type="links" placement="right" item_ids=$category_data.cross_categories multiple=true}
</div>
<div id="content_qty_discounts">
    {include file="addons/development/components/category_qty_discounts.tpl"}
    <div class="control-group">
        <label class="control-label" for="elm_category_apply_qty_discounts">{__("apply_qty_discounts")}:</label>
        <div class="controls">
            <label class="checkbox">
                <input type="hidden" name="category_data[apply_qty_discounts]" value="N" />
                <input type="checkbox" name="category_data[apply_qty_discounts]" id="elm_category_apply_qty_discounts" value="Y" />
            </label>
        </div>
    </div>
</div>