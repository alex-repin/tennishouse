<div class="ty-discussion-post-popup hidden" id="new_post_dialog_{$obj_prefix}{$obj_id}" title="{$new_post_title}">
<form action="{""|fn_url}" method="post" class="{if !$post_redirect_url}cm-ajax cm-form-dialog-closer{/if} posts-form" name="add_post_form" id="add_post_form_{$obj_prefix}{$obj_id}">
<input type="hidden" name="result_ids" value="posts_list,new_post,average_rating*">
<input type ="hidden" name="post_data[thread_id]" value="{$discussion.thread_id}" />
<input type ="hidden" name="redirect_url" value="{$post_redirect_url|default:$config.current_url}" />
<input type="hidden" name="selected_section" value="" />

<div id="new_post_{$obj_prefix}{$obj_id}">

<div class="ty-control-group">
    <label for="dsc_name_{$obj_prefix}{$obj_id}" class="ty-control-group__title cm-required">{__("your_name")}</label>
    <input type="text" id="dsc_name_{$obj_prefix}{$obj_id}" name="post_data[name]" value="{if $auth.user_id}{$user_info.firstname} {$user_info.lastname}{elseif $discussion.post_data.name}{$discussion.post_data.name}{/if}" size="50" class="ty-input-text-large" />
</div>

{if $discussion.type == "R" || $discussion.type == "B"}
<div class="ty-control-group">
    {$rate_id = "rating_`$obj_prefix``$obj_id`"}
    <label class="ty-control-group__title cm-required cm-multiple-radios">{__("your_rating")}</label>
    {include file="addons/discussion/views/discussion/components/rate.tpl" rate_id=$rate_id rate_name="post_data[rating_value]"}
</div>
{/if}

{hook name="discussion:add_post"}
{if $discussion.type == "C" || $discussion.type == "B"}
<div class="ty-control-group">
    <label for="dsc_message_{$obj_prefix}{$obj_id}" class="ty-control-group__title cm-required">{__("your_message")}</label>
    <textarea id="dsc_message_{$obj_prefix}{$obj_id}" name="post_data[message]" class="ty-input-textarea ty-input-text-large" rows="5" cols="72">{$discussion.post_data.message}</textarea>
</div>
{/if}
{/hook}

{$user_info = $auth.user_id|fn_get_user_info:true}
{$birthday_pf_id = $smarty.const.BIRTHDAY_PF_ID}
{$play_level_pf_id = $smarty.const.PLAY_LEVEL_PF_ID}
{$surface_pf_id = $smarty.const.SURFACE_PF_ID}
{$configuration_pf_id = $smarty.const.CONFIGURATION_PF_ID}

<div class="ty-new-post_additional_fields">
    <div class="ty-control-group ty-inline-block">
        <label for="dsc_age_{$obj_prefix}{$obj_id}" class="ty-control-group__title">{__("age")}</label>
        <input type="text" id="dsc_age_{$obj_prefix}{$obj_id}" name="post_data[age]" value="{if $auth.user_id}{$user_info.fields.$birthday_pf_id|fn_get_age}{elseif $discussion.post_data.age}{$discussion.post_data.age}{/if}" size="50" class="ty-input-text-short" />
    </div>
    <div class="ty-control-group ty-inline-block">
        <label for="dsc_play_level_{$obj_prefix}{$obj_id}" class="ty-control-group__title">{__("play_level")}</label>
        <select id="dsc_play_level_{$obj_prefix}{$obj_id}" name="post_data[play_level]">
            <option value="1" {if $discussion.post_data.play_level == '1' || $user_info.fields.$play_level_pf_id == '1'}selected="selected"{/if}>1.0</option>
            <option value="2" {if $discussion.post_data.play_level == '2' || $user_info.fields.$play_level_pf_id == '2'}selected="selected"{/if}>1.5</option>
            <option value="3" {if $discussion.post_data.play_level == '3' || $user_info.fields.$play_level_pf_id == '3'}selected="selected"{/if}>2.0</option>
            <option value="4" {if $discussion.post_data.play_level == '4' || $user_info.fields.$play_level_pf_id == '4'}selected="selected"{/if}>2.5</option>
            <option value="5" {if $discussion.post_data.play_level == '5' || $user_info.fields.$play_level_pf_id == '5'}selected="selected"{/if}>3.0</option>
            <option value="6" {if $discussion.post_data.play_level == '6' || $user_info.fields.$play_level_pf_id == '6'}selected="selected"{/if}>3.5</option>
            <option value="7" {if $discussion.post_data.play_level == '7' || $user_info.fields.$play_level_pf_id == '7'}selected="selected"{/if}>4.0</option>
            <option value="8" {if $discussion.post_data.play_level == '8' || $user_info.fields.$play_level_pf_id == '8'}selected="selected"{/if}>4.5</option>
            <option value="9" {if $discussion.post_data.play_level == '9' || $user_info.fields.$play_level_pf_id == '9'}selected="selected"{/if}>5.0</option>
            <option value="10" {if $discussion.post_data.play_level == '10' || $user_info.fields.$play_level_pf_id == '10'}selected="selected"{/if}>5.5</option>
            <option value="11" {if $discussion.post_data.play_level == '11' || $user_info.fields.$play_level_pf_id == '11'}selected="selected"{/if}>6.0 - 7.0</option>
        </select>
    </div>
    <div class="ty-control-group ty-inline-block">
        <label for="dsc_favourite_surface_{$obj_prefix}{$obj_id}" class="ty-control-group__title">{__("favourite_surface")}</label>
        <select id="dsc_favourite_surface_{$obj_prefix}{$obj_id}" name="post_data[favourite_surface]">
            <option value="1" {if $discussion.post_data.favourite_surface == '1' || $user_info.fields.$surface_pf_id == '1'}selected="selected"{/if}>{__("hard_surface")}</option>
            <option value="2" {if $discussion.post_data.favourite_surface == '2' || $user_info.fields.$surface_pf_id == '2'}selected="selected"{/if}>{__("clay_surface")}</option>
            <option value="3" {if $discussion.post_data.favourite_surface == '3' || $user_info.fields.$surface_pf_id == '3'}selected="selected"{/if}>{__("grass_surface")}</option>
            <option value="4" {if $discussion.post_data.favourite_surface == '4' || $user_info.fields.$surface_pf_id == '4'}selected="selected"{/if}>{__("synthetic_surface")}</option>
            <option value="5" {if $discussion.post_data.favourite_surface == '5' || $user_info.fields.$surface_pf_id == '5'}selected="selected"{/if}>{__("synthetic_grass_surface")}</option>
            <option value="6" {if $discussion.post_data.favourite_surface == '6' || $user_info.fields.$surface_pf_id == '6'}selected="selected"{/if}>{__("parquet_surface")}</option>
            <option value="7" {if $discussion.post_data.favourite_surface == '7' || $user_info.fields.$surface_pf_id == '7'}selected="selected"{/if}>{__("asphalt_surface")}</option>
        </select>
    </div>
</div>
{include file="common/image_verification.tpl" option="use_for_discussion"}

<!--new_post_{$obj_prefix}{$obj_id}--></div>

<div class="buttons-container">
    {include file="buttons/button.tpl" but_text=__("submit") but_meta="ty-btn__secondary" but_role="submit" but_name="dispatch[discussion.add]"}
</div>

</form>
</div>
