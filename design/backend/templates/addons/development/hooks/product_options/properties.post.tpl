{if $option_data.inventory == 'Y' && "SR"|strpos:$option_data.option_type !== false && $filter_features}
<div class="control-group">
    <label class="control-label" for="elm_filter_filter_by_{$id}">{__("feature")}</label>
    <div class="controls">
        <select name="option_data[feature_id]">
            <option value="" > - {__("none")} - </option>
            {foreach from=$filter_features item=feature}
                <option value="{$feature.feature_id}" {if $option_data.feature_id == $feature.feature_id}selected="selected"{/if}>{if $feature.group_description}{$feature.group_description}: {/if}{$feature.description}</option>
            {/foreach}
        </select>
    </div>
</div>
{/if}
