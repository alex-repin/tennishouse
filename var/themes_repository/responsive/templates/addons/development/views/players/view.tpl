{assign var="obj_id" value=$player_data.player_id}
{assign var="obj_id_prefix" value="`$obj_prefix``$obj_id`"}
<div class="ty-player-details">
    <div class="ty-player-detail_wrapper">
    <div class="ty-player-detail">
        <div class="ty-player-block__img-wrapper">
            {hook name="products:image_wrap"}
                {if !$no_images}
                    <div class="ty-product-block__img cm-reload-{$product.product_id}" id="product_images_{$product.product_id}_update">

                        {include file="common/image.tpl" obj_id=$obj_id_prefix images=$player_data.main_pair.detailed}
                    <!--product_images_{$product.product_id}_update--></div>
                {/if}
            {/hook}
        </div>
        <h1 class="ty-player-details-title">
            {if $player_data.ranking}
                {$font_size = 50 - ($player_data.ranking|strlen - 1) * 4}
                <span class="ty-player-ranking">
                    <span class="ty-player-ranking-hashtag">#</span>
                    <span class="ty-player-ranking-num" style="font-size: {$font_size}px;">{$player_data.ranking}</span>
                </span>
                <span class="ty-player-ranking-title">{if $player_data.gender == 'M'}{__("atp_ranking")}{else}{__("wta_ranking")}{/if}</span>
            {/if}
            {$player_data.player}
            {if $player_data.titles}<span class="ty-player-titles">{$player_data.titles}</span>{/if}
        </h1>
        <div class="ty-player-block__left">
            <div class="ty-player-data">
                <span class="ty-player-data__label">{__("date_of_birth")}:</span>
                <div class="ty-player-data__value">{$player_data.birthday|date_format:"`$settings.Appearance.date_format`"} ({$player_data.birthday|fn_get_age|fn_show_age})</div>
            </div>
            <div class="ty-player-data">
                <span class="ty-player-data__label">{__("birthplace")}:</span>
                <div class="ty-player-data__value">{$player_data.birthplace}</div>
            </div>
            <div class="ty-player-data">
                <span class="ty-player-data__label">{__("residence")}:</span>
                <div class="ty-player-data__value">{$player_data.residence}</div>
            </div>
            <div class="ty-player-data">
                <span class="ty-player-data__label">{__("player_height")}:</span>
                <div class="ty-player-data__value">{$player_data.height} {__("cm")}</div>
            </div>
            <div class="ty-player-data">
                <span class="ty-player-data__label">{__("weight")}:</span>
                <div class="ty-player-data__value">{$player_data.weight} {__("kg")}</div>
            </div>
            <div class="ty-player-data">
                <span class="ty-player-data__label">{__("player_plays")}:</span>
                <div class="ty-player-data__value">{if $player_data.plays == 'R'}{__("righty")}{else}{__("lefty")}{/if}</div>
            </div>
            <div class="ty-player-data">
                <span class="ty-player-data__label">{__("turned_pro")}:</span>
                <div class="ty-player-data__value">{$player_data.turned_pro}</div>
            </div>
            {if $player_data.coach}
            <div class="ty-player-data">
                <span class="ty-player-data__label">{__("coach")}:</span>
                <div class="ty-player-data__value">{$player_data.coach}</div>
            </div>
            {/if}
        </div>
    </div>
    {if $player_data.news_feed}
    <div class="ty-tennishouse-container ty-news-feed">
        <div class="ty-tennishouse-body">
            <div id="scroll_list_news_{$player_data.player_id}" class="owl-carousel ty-scroller-list">
                {foreach from=$player_data.news_feed item="news" name="for_news"}
                    <div class="ty-scroller-news-list__item">
                        <span class="ty-news-date">{$news.timestamp|date_format:"%e %B %Y %A, %H:%M"}</span>
                                                
                        <div class="ty-news-item-title">
                            <a target="_blank" href="{$news.link}">
                                <div class="ty-news-item-column-descritpion">
                                    <div class="ty-news-item-row-title">{$news.title}</div>
                                    <div class="ty-news-item-row-description">{$news.description}</div>
                                </div>
                            </a>
                        </div>
                    </div>
                {/foreach}
            </div>

            {script src="js/lib/owlcarousel/owl.carousel.min.js"}
            <script type="text/javascript">
            (function(_, $) {
                $.ceEvent('on', 'ce.commoninit', function(context) {
                    var elm = context.find('#scroll_list_news_{$player_data.player_id}');

                    if (elm.length) {
                        elm.owlCarousel({
                            items: 1,
                            scrollPerPage: false,
                            autoPlay: '15000',
                            slideSpeed: '400',
                            stopOnHover: true,
                            navigation: true,
                            navigationText: ['', ''],
                            pagination: false,
                            responsive: false
                        });
                    }
                });
            }(Tygh, Tygh.$));
            </script>
        </div>
    </div>
    {/if}
    <div class="ty-player__share-buttons">
        {include file="addons/development/common/share_buttons.tpl" title=$player_data.player description=__("player_share_buttons_description") image=$player_data.main_pair}
    </div>
    </div>
    {if $player_data.gear.R}
    <div class="ty-tp-block ty-player-rackets">
        <h2>{if $player_data.gender == 'M'}{__("his_choice")}{else}{__("her_choice")}{/if}</h2>
        {include file="blocks/list_templates/grid_list.tpl"
        products=$player_data.gear.R
        columns=1
        form_prefix="block_manager"
        no_sorting="Y"
        no_pagination="Y"
        no_ids="Y"
        obj_prefix="players_gear"
        item_number=false
        show_trunc_name=true
        show_old_price=true
        show_price=true
        show_rating=true
        show_clean_price=true
        show_list_discount=true
        show_add_to_cart=false
        but_role="action"
        show_discount_label=true}
    </div>
    {/if}
</div>
{if $player_data.gear.BC}
<div class="ty-tp-block ty-player-gear">
    <h2>{__("bags_and_accessories")}</h2>
    {include file="blocks/list_templates/grid_list.tpl"
    products=$player_data.gear.BC
    columns=5
    form_prefix="block_manager"
    no_sorting="Y"
    no_pagination="Y"
    no_ids="Y"
    obj_prefix="players_gear"
    show_trunc_name=true
    show_old_price=true
    show_price=true
    show_rating=true
    show_clean_price=true
    show_list_discount=true
    show_add_to_cart=$show_add_to_cart|default:false
    but_role="action"
    show_discount_label=true}
</div>
{/if}
{if $player_data.gear.AS}
<div class="ty-tp-block ty-player-gear">
    <h2>{__("apparel_and_shoes")}</h2>
    {include file="blocks/list_templates/grid_list.tpl"
    products=$player_data.gear.AS
    columns=5
    form_prefix="block_manager"
    no_sorting="Y"
    no_pagination="Y"
    no_ids="Y"
    obj_prefix="players_gear"
    show_trunc_name=true
    show_old_price=true
    show_price=true
    show_rating=true
    show_clean_price=true
    show_list_discount=true
    show_add_to_cart=$show_add_to_cart|default:false
    but_role="action"
    show_discount_label=true}
</div>
{/if}