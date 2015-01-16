{** block-description:tmpl_roundabout **}

{script src="js/addons/development/jquery.roundabout.min.js"}
{script src="js/addons/development/jquery.roundabout-shapes.min.js"}
<div class="ty-roundabout-wrapper">
    <div id="roundabout_images_{$block.block_id}" class="ty-rounabout-list">
        {foreach from=$items item="category"}
            {include file="common/image.tpl"
            show_detailed_link=false
            images=$category.main_pair
            no_ids=true
            image_width="250"
            keep_transparent=true}
        {/foreach}
    </div>
    <div id="roundabout_description_{$block.block_id}" class="ty-rounabout-description">
        {foreach from=$items item="category" name="roundabout_descr"}
            <div class="ty-roundabout-item-description" {if !$smarty.foreach.roundabout_descr.first}style="display: none;"{/if}>
                <h2>{$category.category}</h2>
                <div class="ty-wysiwyg-content ty-mb-s">{$category.description nofilter}</div>
                <a href="{"categories.view?category_id=`$category.category_id`"|fn_url}">
                    <div class="ty-roundabout-view-collection">{__("view_collection")}</div>
                </a>
            </div>
        {/foreach}
    </div>
</div>
<script type="text/javascript">
var block_id = '{$block.block_id}';
{literal}
(function(_, $) {

    $(document).ready(function() {
        var roundabout = $('#roundabout_images_' + block_id);
        roundabout.roundabout({
            childSelector: "img",
            duration: 1000,
            autoplay: true,
            autoplayDuration: 5000,
            autoplayPauseOnHover: true
        });
        roundabout.bind( 'animationStart', function() {
            $('#roundabout_description_' + block_id).find('.ty-roundabout-item-description').fadeOut('fast');
        });
        roundabout.bind( 'animationEnd', function() {
            $('#roundabout_description_' + block_id).find($('.ty-roundabout-item-description')[roundabout.roundabout('getChildInFocus')]).stop().fadeIn('fast');
        });
    });
    
}(Tygh, Tygh.$));
{/literal}
</script>