{$obj_prefix = "force_"}
<div class="ty-product-add-review-wrapper ty-force-review-wrapper" id="add_review_{$obj_prefix}{$obj_id}">
    <div class="ty-product-review-title">{if $addons.development.product_review > 0}{capture name="reward_amount"}{include file="common/price.tpl" value=$addons.development.product_review}{/capture}{__("force_review_reward_text", ["[amount]" => $smarty.capture.reward_amount])}{else}{__("force_review_text")}{/if}</div>
    {include file="addons/discussion/views/discussion/components/quick_post.tpl"}
</div>
<script type="text/javascript">
(function(_, $) {
    $(function() {
        $.processForms($('#add_review_{$obj_prefix}{$obj_id}'));
    });
}(Tygh, Tygh.$));
</script>
