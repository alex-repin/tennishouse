{capture name="mainbox"}

{capture name="sidebar"}
    {include file="addons/development/views/players/components/players_search_form.tpl" dispatch="players.manage"}
{/capture}

<form action="{""|fn_url}" method="post" name="players_form" enctype="multipart/form-data">
<input type="hidden" name="fake" value="1" />

{if $players}
<table class="table table-middle">
<thead>
<tr>
    <th width="1%" class="left">
        {include file="common/check_items.tpl" class="cm-no-hide-input"}</th>
    <th width="5%">{__("atp_ranking")}</th>
    <th>{__("player_name")}</th>
    <th width="6%">&nbsp;</th>
    <th width="10%" class="right">{__("status")}</th>
</tr>
</thead>
{foreach from=$players item=player}
<tr class="cm-row-status-{$player.status|lower}">

    <td class="left">
        <input type="checkbox" name="player_ids[]" value="{$player.player_id}" class="cm-item " /></td>
    <td class="">
        #{$player.ranking}
    </td>
    <td class="">
        <a class="row-status" href="{"players.update?player_id=`$player.player_id`"|fn_url}">{$player.player}</a>
    </td>
    <td>
        {capture name="tools_list"}
            <li>{btn type="list" text=__("edit") href="players.update?player_id=`$player.player_id`"}</li>
            <li>{btn type="list" class="cm-confirm" text=__("delete") href="players.delete?player_id=`$player.player_id`"}</li>
        {/capture}
        <div class="hidden-tools">
            {dropdown content=$smarty.capture.tools_list}
        </div>
    </td>
    <td class="right">
        {include file="common/select_popup.tpl" id=$player.player_id status=$player.status hidden=true object_id_name="player_id" table="players" popup_additional_class="dropleft"}
    </td>
</tr>
{/foreach}
</table>
{else}
    <p class="no-items">{__("no_data")}</p>
{/if}

{capture name="buttons"}
    {capture name="tools_list"}
        {if $players}
            <li>{btn type="delete_selected" dispatch="dispatch[players.m_delete]" form="players_form"}</li>
        {/if}
    {/capture}
    {dropdown content=$smarty.capture.tools_list}
{/capture}
{capture name="adv_buttons"}
    {include file="common/tools.tpl" tool_href="players.add" prefix="top" hide_tools="true" title=__("add_player") icon="icon-plus"}
{/capture}

</form>

{/capture}
{include file="common/mainbox.tpl" title=__("players") sidebar=$smarty.capture.sidebar content=$smarty.capture.mainbox buttons=$smarty.capture.buttons adv_buttons=$smarty.capture.adv_buttons select_languages=true}
