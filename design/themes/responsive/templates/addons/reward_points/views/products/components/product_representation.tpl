{*if $product.points_info.price}
    <div class="ty-control-group">
        <span class="ty-control-group__label product-list-field">{__("price_in_points")}:</span>
        <span class="ty-control-group__item" id="price_in_points_{$obj_prefix}{$obj_id}">{$product.points_info.price}</span>
    </div>
{/if*}
<div class="ty-control-group product-list-field product-list-field-rp{if !$product.points_info.reward.amount} hidden{/if}">
    <label class="ty-control-group__label">{__("reward_points")}{include file="addons/development/common/tooltip.tpl" note_text=__("reward_points_note", ["[amount]" => $product.points_info.reward.amount]) tooltipclass="ty-reward-tooltip"}:</label>
    <span class="ty-control-group__item" id="reward_points_{$obj_prefix}{$obj_id}">{*if !$auth.user_id}{__("from_lower")} {/if*}{include file="common/price.tpl" value=$product.points_info.reward.amount}</span>
</div>