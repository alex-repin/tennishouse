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

define('RACKETS_CATEGORY_ID', 254);
define('APPAREL_CATEGORY_ID', 262);
define('SHOES_CATEGORY_ID', 263);
define('BAGS_CATEGORY_ID', 264);
define('ACCESSORIES_CATEGORY_ID', 267);
define('SPORTS_NUTRITION_CATEGORY_ID', 302);

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
    'update_product_filter'
);
