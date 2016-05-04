{if $mode == 'R'}
    {$iw = $settings.Thumbnails.product_lists_thumbnail_width}
    {$ih = $settings.Thumbnails.product_lists_thumbnail_height}
{elseif $mode == 'S'}
    {$iw = 130}
    {$ih = 130}
{elseif $mode == 'N'}
    {$iw = 103}
    {$ih = 103}
{elseif $mode == 'M'}
    {$iw = 70}
    {$ih = 70}
{/if}
{capture name="main_icon"}
    {if !$hide_links}<a href="{"products.view?product_id=`$product.product_id`{if $product.ohash}&`$product.ohash`{/if}"|fn_url}">{/if}
        {include file="common/image.tpl" obj_id=$obj_id_prefix images=$product.main_pair image_width=$iw image_height=$ih}
    {if !$hide_links}</a>{/if}
{/capture}

{if $product.image_pairs && $show_gallery}
<div class="ty-center-block">
    <div class="ty-thumbs-wrapper owl-carousel cm-image-gallery" data-ca-items-count="1" data-ca-items-responsive="true" id="icons_{$obj_id_prefix}">
        {if $product.main_pair}
            <div class="cm-gallery-item cm-item-gallery">
                {$smarty.capture.main_icon nofilter}
            </div>
        {/if}
        {foreach from=$product.image_pairs item="image_pair"}
            {if $image_pair}
                <div class="cm-gallery-item cm-item-gallery">
                    {if !$hide_links}<a href="{"products.view?product_id=`$product.product_id`{if $product.ohash}&`$product.ohash`{/if}"|fn_url}">{/if}
                        {include file="common/image.tpl" no_ids=true images=$image_pair image_width=$iw image_height=$ih}
                    {if !$hide_links}</a>{/if}
                </div>
            {/if}
        {/foreach}
    </div>
</div>
{else}
    {$smarty.capture.main_icon nofilter}
{/if}