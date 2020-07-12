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

// wday = Пн - 1, Вт - 2, Ср - 3, Чт - 4, Пт - 5, Сб - 6, Вс - 7
return array(
    'F' => array(
        'frequency' => 60 * 60 * 24,
        'function' => 'fn_generate_features_cash',
        'H' => '02',
        'name' => 'cache_features'
    ),
    'R' => array(
        'frequency' => 60 * 60 * 24,
        'function' => 'fn_update_rankings',
        'wday' => '2',
        'H' => '02',
        'name' => 'update_rankings'
    ),
    'E' => array(
        'frequency' => 60 * 60 * 24,
        'function' => 'fn_check_expired_points',
        'H' => '02',
        'name' => 'check_expired_points'
    ),
    'P' => array(
        'frequency' => 60 * 60 * 24,
        'function' => 'fn_update_rub_rate',
        'H' => '02',
        'name' => 'update_exchange_rates'
    ),
    'M' => array(
        'frequency' => 60 * 60 * 24,
        'function' => 'fn_get_generate_categories_menu_subitems',
        'H' => '02',
        'name' => 'generate_menu'
    ),
    'S' => array(
        'frequency' => 60 * 60 * 24,
        'function' => 'fn_check_delivery_statuses',
        'H' => '10',
        'name' => 'check_delivery_statuses'
    ),
    'C' => array(
        'frequency' => 60 * 60 * 24 * 7,
        'function' => 'fn_update_competitive_catalog',
        'H' => '02',
        'name' => 'update_competitive_catalog'
    ),
    'I' => array(
        'frequency' => 60 * 60 * 24,
        'function' => 'fn_update_competitive_prices',
        'H' => '02',
        'name' => 'update_competitive_prices'
    ),
    'A' => array(
        'frequency' => 60 * 60 * 24,
        'function' => 'fn_actualize_prices',
        'H' => '02',
        'name' => 'actualize_prices'
    ),
    'T' => array(
        'frequency' => 60 * 60,
        'function' => 'fn_check_sms',
        'name' => 'check_sms_statuses'
    ),
    'Y' => array(
        'frequency' => 60 * 60 * 12,
        'function' => 'fn_synchronize_agents',
        'name' => 'catalog_synchronization'
    ),
    'D' => array(
        'frequency' => 60 * 60 * 3,
        'function' => 'fn_update_xml_feed',
        'name' => 'update_xml_feed'
    ),
);
