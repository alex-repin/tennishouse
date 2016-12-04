<div id="update_stocks_section">
{$dispatch = $dispatch|default:"development.update_stocks"}
<form action="{""|fn_url}" method="post" name="update_stocks" id="update_stocks_form" enctype="multipart/form-data" class="cm-ajax form-horizontal form-edit">
<input type="hidden" name="calculate" value="Y">
<input type="hidden" name="warehouse_id" value="{$warehouse_id|default:0}">
<input type="hidden" name="result_ids" value="update_stocks_section" />

<div class="control-group">
    <label class="control-label">{__("brand")}:</label>
    <div class="controls">
        <select name="brand_id">
            <option value="{$brand.variant_id}" > - {__("select")} - </option>
            {foreach from=$brands item=brand}
                <option value="{$brand.variant_id}" >{$brand.variant}</option>
            {/foreach}
        </select>
    </div>
</div>
<div class="control-group">
    <label class="control-label">{__("select_file")}:</label>
    <div class="controls">{include file="common/fileuploader.tpl" var_name="csv_file[0]"}</div>
</div>
<div class="control-group">
    <label class="control-label">{__("debug_id_code")}:</label>
    <div class="controls">
        <input type="text" name="debug" size="10" value="" class="input-text-short" onkeyup="if ($(this).val() != '') $('#update_stocks_form').removeClass('cm-ajax'); else $('#update_stocks_form').addClass('cm-ajax');" />
    </div>
</div>
<div class="cm-tab-tools">
    {include file="buttons/button.tpl" but_text=__("import") but_name="dispatch[`$dispatch`]" but_role="submit-link" but_target_form="update_stocks" but_meta="cm-tab-tools"}
</div>
</form>

{if $calculate}
    {include file="common/subheader.tpl" title="{__("results")}. {__("total_product_codes")} - {$total}. {__("products_in_stock")} - {$in_stock}. {__("products_out_of_stock")} - {$out_of_stock}" target="#res_total"}
    <div id="res_total" class="collapse in" style="margin-left: 20px;">
        {include file="common/subheader.tpl" title="{__("ignore_list")}: `$ignore_list|count`" target="#res_ignore_list"}
        <div id="res_ignore_list" class="collapse">
            {if $ignore_list}
                <form action="{""|fn_url}" method="post" name="watch_products" class="cm-ajax form-horizontal form-edit">
                <input type="hidden" name="brand_id" value="{$brand_id}">
                {foreach from=$ignore_list item=product key="pcode"}
                    <div class="control-group">
                        <label class="control-label">{$product.product} - {$pcode}:</label>
                        <div class="controls">
                            <input type="checkbox" name="product_codes[]" value="{$pcode}" class="checkbox cm-item" />
                        </div>
                    </div>
                {/foreach}
                <div class="btn-group btn-hover dropleft">
                    {include file="buttons/button.tpl" but_role="submit" but_text=__("delete_selected_from_ignore_list") but_name="dispatch[development.watch_products]"}
                </div>
                </form>
            {/if}
        </div>
        {include file="common/subheader.tpl" title="{__("update_stock_updated")}: `$updated_products|count`" target="#res_updated"}
        <div id="res_updated" class="collapse">
            {if $updated_products}
                {$updated_products|fn_print_r}
            {/if}
        </div>
        {include file="common/subheader.tpl" title="{__("update_stock_missed")}: `$missing_products|count`" target="#res_missed"}
        <div id="res_missed" class="collapse">
            {if $missing_products}
                <form action="{""|fn_url}" method="post" name="ignore_products" class="cm-ajax form-horizontal form-edit">
                <input type="hidden" name="brand_id" value="{$brand_id}">
                {foreach from=$missing_products item=product key="pcode"}
                    <div class="control-group">
                        <label class="control-label">{$product.product} - {$pcode}:</label>
                        <div class="controls">
                            <input type="checkbox" name="product_codes[]" value="{$pcode}" class="checkbox cm-item" />
                        </div>
                    </div>
                {/foreach}
                <div class="btn-group btn-hover dropleft">
                    {include file="buttons/button.tpl" but_role="submit" but_text=__("add_selected_to_ignore_list") but_name="dispatch[development.ignore_products]"}
                </div>
                </form>
            {/if}
        </div>
        {include file="common/subheader.tpl" title="{__("update_stock_broken_options")}: `$broken_options_products|count`" target="#res_broken_options"}
        <div id="res_broken_options" class="collapse">
            {if $broken_options_products}
                {$broken_options_products|fn_print_r}
            {/if}
        </div>
        {include file="common/subheader.tpl" title="{__("update_stock_broken_net_cost")}: `$broken_net_cost_products|count`" target="#res_broken_net_cost"}
        <div id="res_broken_net_cost" class="collapse">
            {if $broken_net_cost_products}
                {$broken_net_cost_products|fn_print_r}
            {/if}
        </div>
        {include file="common/subheader.tpl" title="{__("update_stock_trash")}: `$trash|count`" target="#res_trash"}
        <div id="res_trash" class="collapse">
            {if $trash}
                {$trash|fn_print_r}
            {/if}
        </div>
    </div>
{/if}
<!--update_stocks_section--></div>