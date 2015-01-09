<?php
/***************************************************************************
*                                                                          *
*   (c) 2004 Vladimir V. Kalynyak, Alexey V. Vinokurov, Ilya M. Shalnev    *
*                                                                          *
* This  is  commercial  software,  only  users  who have purchased a valid *
* license  and  accept  to the terms of the  License Agreement can install *
* and use this program.                                                    *
*                                                                          *
****************************************************************************
* PLEASE READ THE FULL TEXT  OF THE SOFTWARE  LICENSE   AGREEMENT  IN  THE *
* "copyright.txt" FILE PROVIDED WITH THIS DISTRIBUTION PACKAGE.            *
****************************************************************************/

if (!defined('BOOTSTRAP')) { die('Access denied'); }

/*Categories*/
define('RACKETS_CATEGORY_ID', 254);
define('APPAREL_CATEGORY_ID', 262);
define('SHOES_CATEGORY_ID', 263);
define('BAGS_CATEGORY_ID', 264);
define('SPORTS_NUTRITION_CATEGORY_ID', 302);
define('ACCESSORIES_CATEGORY_ID', 267);
define('STRINGS_CATEGORY_ID', 265);
define('BALLS_CATEGORY_ID', 266);
define('TOWELS_CATEGORY_ID', 314);
define('OVERGRIPS_CATEGORY_ID', 312);
define('BASEGRIPS_CATEGORY_ID', 313);
define('DAMPENERS_CATEGORY_ID', 315);
/*Categories*/

/*Features*/
define('R_WEIGHT_FEATURE_ID', 22);
define('R_LENGTH_FEATURE_ID', 25);
define('R_BALANCE_FEATURE_ID', 24);
define('R_HEADSIZE_FEATURE_ID', 20);
define('R_STIFFNESS_FEATURE_ID', 23);
define('BRAND_FEATURE_ID', 29);
define('TYPE_FEATURE_ID', 33);
define('PLAYER_FEATURE_ID', 48);
define('BABOLAT_SERIES_FEATURE_ID', 34);
define('HEAD_SERIES_FEATURE_ID', 37);
define('WILSON_SERIES_FEATURE_ID', 39);
define('DUNLOP_SERIES_FEATURE_ID', 41);
define('PRINCE_SERIES_FEATURE_ID', 43);
define('YONEX_SERIES_FEATURE_ID', 45);
define('PROKENNEX_SERIES_FEATURE_ID', 47);
define('CLOTHES_TYPE_FEATURE_ID', 50);
define('SHOES_SURFACE_FEATURE_ID', 56);
define('BAG_SIZE_FEATURE_ID', 58);
define('STRING_TYPE_FEATURE_ID', 60);
define('BALLS_TYPE_FEATURE_ID', 64);
define('OG_TYPE_FEATURE_ID', 66);
define('BG_TYPE_FEATURE_ID', 72);
/*Features*/

/*Profile fields*/
define('BIRTHDAY_PF_ID', 37);
define('PLAY_LEVEL_PF_ID', 36);
define('SURFACE_PF_ID', 38);
define('CONFIGURATION_PF_ID', 39);
/*Profile fields*/

/*Feature values*/
/*Shoes*/
define('ALLCOURT_SURFACE_FV_ID', 359);
define('CLAY_SURFACE_FV_ID', 360);
define('GRASS_SURFACE_FV_ID', 361);
/*Shoes*/
/*Rackets*/
define('POWER_RACKET_FV_ID', 211);
define('CLUB_RACKET_FV_ID', 212);
define('PRO_RACKET_FV_ID', 265);
define('KIDS_RACKET_FV_ID', 282);
/*Rackets*/
/*Feature values*/

define('PRODUCT_BLOCK_TABS_GRID_ID', 200);

define('CATELOG_MENU_ITEM_ID', 153);

define('KIRSCHBAUM_BRAND_ID', 340);

define('RACKETS_QTY_DSC_PRC', 5);

fn_register_hooks(
    'update_product_post',
    'get_product_data_post',
    'delete_product_post',
    'get_products',
    'get_products_post',
    'get_filters_products_count_before_select_filters',
    'get_filter_range_name_post',
    'get_product_filter_fields',
    'add_range_to_url_hash_pre',
    'update_product_filter',
    'get_product_features_list_post',
    'gather_additional_product_data_post',
    'get_filters_products_count_pre',
    'update_product_pre',
    'top_menu_form',
    'get_products_pre'
);