{if $display_base_href}
<base href="{$config.current_location}/" />
{/if}
<meta http-equiv="Content-Type" content="text/html; charset={$smarty.const.CHARSET}" data-ca-mode="{$store_trigger}" />
{hook name="index:meta_description"}
{if $runtime.controller == 'products' && $runtime.mode == 'view'}
<meta property="og:description" content="{$product.full_description|strip_tags|truncate:200}" />
{elseif $runtime.controller == 'players' && $runtime.mode == 'view'}
<meta property="og:description" content="{__("player_share_buttons_description")}" />
{else}
<meta name="description" content="{$meta_description|html_entity_decode:$smarty.const.ENT_COMPAT:"UTF-8"|default:$location_data.meta_description}" />
{/if}
{/hook}
<meta name="keywords" content="{$meta_keywords|default:$location_data.meta_keywords}" />
{$location_data.custom_html nofilter}
