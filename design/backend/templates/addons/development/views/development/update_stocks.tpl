{capture name="mainbox"}

<form action="{""|fn_url}" method="post" name="update_stocks" enctype="multipart/form-data" class="{*cm-ajax cm-comet*} form-horizontal form-edit">
<input type="hidden" name="calculate" value="Y">

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
{capture name="buttons"}
    <div class="cm-tab-tools" id="tools_{$p_id}">
        {include file="buttons/button.tpl" but_text=__("import") but_name="dispatch[development.update_stocks]" but_role="submit-link" but_target_form="update_stocks" but_meta="cm-tab-tools"}
        <!--tools_{$p_id}--></div>
{/capture}
</form>
{/capture}

{include file="common/mainbox.tpl" title={__("update_stocks")} content=$smarty.capture.mainbox content_id="update_stocks" buttons=$smarty.capture.buttons}
