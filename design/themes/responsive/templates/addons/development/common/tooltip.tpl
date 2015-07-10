{if $note_text}
    {capture name="category_note"}
        {if $note_url}<a href="{"`$note_url`"|fn_url}" target="_blank">{/if}{$note_text}{if $note_url}</a>{/if}
    {/capture}
    {include file="common/tooltip.tpl" tooltip=$smarty.capture.category_note}
{/if}
