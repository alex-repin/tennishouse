<div id="rc_dialog">
{capture name="buttons"}
    {if $mode}
        <div class="ty-sd-back-button cm-sd-option" data-sd-option="">{__("back")}</div>
    {/if}
{/capture}
{capture name="info"}
        <div class="{if $mode}ty-sd__content-mode-{$mode}{/if}">
        <form action="{""|fn_url}" method="post" class="cm-ajax" name="stringing_form" id="sd_form">
            <input type="hidden" name="result_ids" value="rc_dialog">
            <input type="hidden" name="dispatch" value="racket_customization.submit">
            {if $mode}
                {if $mode == '2'}
                    <div class="ty-sd_subtitle">{__("sd_subtitle_mode_2")}</div>
                    <div class="grid-list ty-grid-list__stringing">
                        <div class="ty-column3">
                            <div class="ty-grid-list__item-wrapper">
                                <div class="ty-grid-list__item">
                                    <div class="ty-grid-list__item-sd-bg ty-grid-list__item-sd-es-bg"></div>
                                    <div class="ty-grid-list__item-sd-title">{__("expert_stringing")}</div>
                                </div>
                            </div>
                        </div>
                        <div class="ty-sd__mode-2-textaera">
                            <textarea class="ty-sd_expert_recommendation_notes__text" name="expert_recommendation_notes"></textarea>
                        </div>
                    </div>
                {/if}
            {else}
                <input type="hidden" name="sd_option" id="sd_option" value="">
                <div class="ty-sd_subtitle">{__("sd_subtitle")}</div>
                <div class="grid-list ty-grid-list__stringing">
                    <div class="ty-column3">
                        <div class="ty-grid-list__item-wrapper cm-sd-option" data-sd-option="1">
                            <div class="ty-grid-list__item">
                                <div class="ty-grid-list__item-sd-bg ty-grid-list__item-sd-u-bg"></div>
                                <div class="ty-grid-list__item-sd-title">{__("keep_unstring")}</div>
                                <div class="ty-grid-list__item-sd-price">
                                    <div>{__("string")}: <b>-</b></div>
                                    <div>{__("stringing_service")}: <b>-</b></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="ty-column3">
                        <div class="ty-grid-list__item-wrapper cm-sd-option" data-sd-option="2">
                            <div class="ty-grid-list__item">
                                <div class="ty-grid-list__item-sd-bg ty-grid-list__item-sd-es-bg"></div>
                                <div class="ty-grid-list__item-sd-title">{__("expert_stringing")}</div>
                                <div class="ty-grid-list__item-sd-price">
                                    <div>{__("string")}: <b>{__("free")}</b></div>
                                    <div>{__("stringing_service")}: <b>{__("free")}</b></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="ty-column3">
                        <div class="ty-grid-list__item-wrapper cm-sd-option" data-sd-option="3">
                            <div class="ty-grid-list__item">
                                <div class="ty-grid-list__item-sd-bg ty-grid-list__item-sd-m-bg"></div>
                                <div class="ty-grid-list__item-sd-title">{__("manual_stringing_selection")}</div>
                                <div class="ty-grid-list__item-sd-price">
                                    <div>{__("string")}: <b> -30%</b></div>
                                    <div>{__("stringing_service")}: <b>{__("free")}</b></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            {/if}
            <button class="hidden" id="stringing_form_submit" type="submit"></button>
        </form>
        </div>
{/capture}
{include file="addons/development/views/racket_customization/stringing_notification.tpl" product_buttons=$smarty.capture.buttons product_info=$smarty.capture.info}
<!--rc_dialog--></div>
