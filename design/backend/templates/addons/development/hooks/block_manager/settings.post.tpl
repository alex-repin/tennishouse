<div class="control-group">
    <label for="block_{$html_id}_ajax_content" class="control-label">{__("ajax_block_content")}</label>
    <div class="controls">
        <label class="checkbox">
            <input type="hidden" name="block_data[properties][ajax_content]" value="N" />
            <input type="checkbox" name="block_data[properties][ajax_content]" id="block_{$html_id}_ajax_content" value="Y" {if $block.properties.ajax_content == "Y"}checked="checked"{/if}/>
        </label>
    </div>
</div>
<div class="control-group">
    <label for="block_{$html_id}_capture_content" class="control-label">{__("capture_block_content")}</label>
    <div class="controls">
        <label class="checkbox">
            <input type="hidden" name="block_data[properties][capture_content]" value="N" />
            <input type="checkbox" name="block_data[properties][capture_content]" id="block_{$html_id}_capture_content" value="Y" {if $block.properties.capture_content == "Y"}checked="checked"{/if}/>
        </label>
    </div>
</div>
