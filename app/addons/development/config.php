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

set_error_handler("fn_process_php_errors");

/*Categories*/
define('RACKETS_CATEGORY_ID', 254);
define('PRO_RACKETS_CATEGORY_ID', 344);
define('CLUB_RACKETS_CATEGORY_ID', 345);
define('BEGINNERS_RACKETS_CATEGORY_ID', 346);
define('KIDS_RACKETS_CATEGORY_ID', 409);
define('APPAREL_CATEGORY_ID', 262);
define('MENS_APPAREL_CATEGORY_ID', 295);
define('WOMEN_APPAREL_CATEGORY_ID', 296);
define('SHOES_CATEGORY_ID', 263);
define('MENS_SHOES_CATEGORY_ID', 303);
define('WOMEN_SHOES_CATEGORY_ID', 304);
define('BAGS_CATEGORY_ID', 264);
define('SPORTS_NUTRITION_CATEGORY_ID', 302);
define('STRINGS_CATEGORY_ID', 265);
define('BALLS_CATEGORY_ID', 266);
define('GRIPS_CATEGORY_ID', 472);
define('OVERGRIPS_CATEGORY_ID', 312);
define('BASEGRIPS_CATEGORY_ID', 313);
define('DAMPENERS_CATEGORY_ID', 315);
define('STR_MACHINE_CATEGORY_ID', 463);
define('BALL_HOPPER_CATEGORY_ID', 471);
define('BALL_MACHINE_CATEGORY_ID', 465);
define('BALL_MACHINE_ACC_CATEGORY_ID', 473);
define('COURT_EQUIPMENT_CATEGORY_ID', 438);
define('OTHER_CATEGORY_ID', 437);

define('ACCESSORIES_CATEGORY_ID', 267);

define('HYBRID_MATERIAL_CATEGORY_ID', 421);
define('NATURAL_GUT_MATERIAL_CATEGORY_ID', 420);
define('NYLON_MATERIAL_CATEGORY_ID', 418);
define('POLYESTER_MATERIAL_CATEGORY_ID', 419);
define('MONO_STRUCTURE_CATEGORY_ID', 422);
define('MULTI_STRUCTURE_CATEGORY_ID', 423);
define('TEXTURED_STRUCTURE_CATEGORY_ID', 424);
define('SYNTH_GUT_STRUCTURE_CATEGORY_ID', 425);
/*Categories*/

/*Features*/
define('R_WEIGHT_FEATURE_ID', 22);
define('R_LENGTH_FEATURE_ID', 25);
define('R_BALANCE_FEATURE_ID', 24);
define('R_HEADSIZE_FEATURE_ID', 20);
define('R_STIFFNESS_FEATURE_ID', 23);
define('R_STRING_PATTERN_FEATURE_ID', 21);
define('R_STRINGS_FEATURE_ID', 82);
define('R_POWER_FEATURE_ID', 119);
define('CLOTHES_GENDER_FEATURE_ID', 52);
define('SHOES_GENDER_FEATURE_ID', 54);
define('BRAND_FEATURE_ID', 29);
define('TYPE_FEATURE_ID', 33);
define('PLAYER_FEATURE_ID', 48);
define('BABOLAT_SERIES_FEATURE_ID', 34);
define('HEAD_SERIES_FEATURE_ID', 110);
define('WILSON_SERIES_FEATURE_ID', 105);
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

define('BRAND_FEATURE_TYPE', 'V');
/*Features*/

/*Profile fields*/
define('BIRTHDAY_PF_ID', 37);
define('PLAY_LEVEL_PF_ID', 36);
define('SURFACE_PF_ID', 38);
define('CONFIGURATION_PF_ID', 39);
/*Profile fields*/

/*Feature values*/
/*Brands*/
define('BABOLAT_FV_ID', 143);
/*Brands*/
/*Apparel*/
define('C_GENDER_M_FV_ID', 324);
define('C_GENDER_W_FV_ID', 388);
/*Apparel*/
/*Shoes*/
define('S_GENDER_M_FV_ID', 326);
define('S_GENDER_W_FV_ID', 387);
define('ALLCOURT_SURFACE_FV_ID', 359);
define('CLAY_SURFACE_FV_ID', 360);
define('GRASS_SURFACE_FV_ID', 361);
/*Shoes*/
/*Rackets*/
define('POWER_RACKET_FV_ID', 211);
define('CLUB_RACKET_FV_ID', 212);
define('PRO_RACKET_FV_ID', 265);
define('KIDS_RACKET_FV_ID', 282);
define('REGULAR_LENGTH_FV_ID', 141);
define('CLOSED_PATTERN_FV_ID', 191);
define('KIDS_17_FV_ID', 474);
define('KIDS_19_FV_ID', 463);
define('KIDS_21_FV_ID', 460);
define('KIDS_23_FV_ID', 465);
define('KIDS_25_FV_ID', 458);
define('KIDS_26_FV_ID', 455);
define('POWER_VHIGH_FV_ID', 1050);
define('POWER_HIGH_FV_ID', 1049);
define('POWER_MOD_FV_ID', 1048);
define('POWER_LOW_FV_ID', 1047);
define('POWER_VLOW_FV_ID', 1046);
/*Rackets*/
/*Strings*/
define('TW_M_STRINGS_FV_ID', 499);
define('STRING_PACK_FV_ID', 338);

define('NATURAL_GUT_STRINGS_FV_ID', 368);
define('NYLON_STRINGS_FV_ID', 365);
define('POLYESTER_STRINGS_FV_ID', 372);
define('HYBRID_STRINGS_FV_ID', 369);
define('MONOFIL_STRINGS_FV_ID', 330);
define('MULTIFIL_STRINGS_FV_ID', 331);
define('TEXTURED_STRINGS_FV_ID', 370);
define('SYNTHETIC_GUT_STRINGS_FV_ID', 371);
define('NATURAL_GUT_STRINGS_STRUCTURE_FV_ID', 376);
/*Strings*/
/*Feature values*/

define('PRODUCT_BLOCK_TABS_GRID_ID', 200);

define('CATALOG_MENU_ITEM_ID', 153);
define('LCENTER_MENU_ITEM_ID', 156);
define('RACKET_FINDER_MENU_ITEM_ID', 9999);

define('KIRSCHBAUM_BRAND_ID', 340);
define('SLAZENGER_BRAND_ID', 1020);

define('RACKETS_QTY_DSC_PRC', 5);

/* Pages*/
define('LEARNING_CENTER_PAGE_ID', 53);
define('LC_RACKETS_PAGE_ID', 55);
define('LETS_PLAY_PAGE_ID', 75);
define('LOYALITY_PROGRAM_PAGE_ID', 77);
define('SAVING_PROGRAM_PAGE_ID', 77);
define('ABOUT_US_PAGE_ID', 2);
define('SHIPPING_PAGE_ID', 5);
define('PAYMENT_PAGE_ID', 73);
/* Pages*/

/* Global options */
define('GLOBAL_COLOR_OPT_ID', 139);
define('SHOE_SIZE_OPT_ID', 112);
define('APPAREL_SIZE_OPT_ID', 141);
define('APPAREL_KIDS_SIZE_OPT_ID', 209);
/* Global options */

define('SIZE_CHART_TAB_ID', 10);

define('TH_WAREHOUSE_ID', 1);
define('BABOLAT_WAREHOUSE_ID', 2);

define('REVIEWS_THREAD_ID', 259);

define('COURIER_SH_ID', 10);
define('SDEK_STOCK_SH_ID', 18);
define('SDEK_DOOR_SH_ID', 13);
define('RU_POST_SH_ID', 9);
define('EMS_SH_ID', 7);

define('BIG_CITIES', serialize(array(
    array(
        'city' => 'Москва',
        'state' => 'MOW',
        'city_id' => '7700000000000'
    ),
    array(
        'city' => 'Санкт-Петербург',
        'state' => 'SPE',
        'city_id' => '7800000000000'
    ),
    array(
        'city' => 'Новосибирск',
        'state' => 'NVS',
        'city_id' => '5400000100000'
    ),
    array(
        'city' => 'Екатеринбург',
        'state' => 'SVE',
        'city_id' => '6600000100000'
    ),
    array(
        'city' => 'Нижний Новгород',
        'state' => 'NIZ',
        'city_id' => '5200000100000'
    ),
    array(
        'city' => 'Казань',
        'state' => 'TA',
        'city_id' => '1600000100000'
    ),
    array(
        'city' => 'Челябинск',
        'state' => 'CHE',
        'city_id' => '7400000100000'
    ),
    array(
        'city' => 'Омск',
        'state' => 'OMS',
        'city_id' => '5500000100000'
    ),
    array(
        'city' => 'Самара',
        'state' => 'SAM',
        'city_id' => '6300000100000'
    ),
    array(
        'city' => 'Ростов-на-Дону',
        'state' => 'ROS',
        'city_id' => '6100000100000'
    ),
    array(
        'city' => 'Уфа',
        'state' => 'BA',
        'city_id' => '0200000100000'
    ),
    array(
        'city' => 'Красноярск',
        'state' => 'KIA',
        'city_id' => '2400000100000'
    ),
    array(
        'city' => 'Пермь',
        'state' => 'PER',
        'city_id' => '5900000100000'
    ),
    array(
        'city' => 'Воронеж',
        'state' => 'VOR',
        'city_id' => '3600000100000'
    ),
    array(
        'city' => 'Волгоград',
        'state' => 'VGG',
        'city_id' => '3400000100000'
    ),
    array(
        'city' => 'Краснодар',
        'state' => 'KDA',
        'city_id' => '2300000100000'
    ),
)));