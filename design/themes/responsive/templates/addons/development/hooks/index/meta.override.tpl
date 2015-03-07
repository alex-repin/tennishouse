{if $display_base_href}
<base href="{$config.current_location}/" />
{/if}
<meta http-equiv="Content-Type" content="text/html; charset={$smarty.const.CHARSET}" data-ca-mode="{$store_trigger}" />
{hook name="index:meta_description"}
{if $runtime.controller == 'products' && $runtime.mode == 'view'}
{$meta_descr = __('product_meta_description', ['[category]' => $product.category_main_title, '[product]' => $product.product, '[price]' => $product.price|fn_format_price, '[product_code]' => $product.product_code])}
<meta property="og:description" content="{$meta_descr}" />
<meta name="description" content="{$meta_descr}" />
{elseif $runtime.controller == 'players' && $runtime.mode == 'list'}
{$meta_descr = __('players_meta_description', ['[players]' => $meta_players])}
<meta name="description" content="{$meta_descr}" />
{elseif $runtime.controller == 'players' && $runtime.mode == 'view'}
<meta property="og:description" content="{$player_data.player}. {__("player_share_buttons_description")}" />
<meta name="description" content="{$player_data.player}. {__("player_share_buttons_description")}" />
{elseif $meta_description}
<meta name="description" content="{$meta_description|html_entity_decode:$smarty.const.ENT_COMPAT:"UTF-8"|default:$location_data.meta_description}" />
{/if}
{/hook}
{if $meta_keywords}
    <meta name="keywords" content="{$meta_keywords|default:$location_data.meta_keywords}" />
{/if}
{$location_data.custom_html nofilter}
