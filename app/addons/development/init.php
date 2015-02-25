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
    'get_products_pre',
    'get_categories',
    'get_lang_var_post',
    'calculate_cart_items',
    'get_categories_post',
    'get_product_feature_variants',
    'get_category_data_post',
    'render_block_register_cache',
    'update_product_features_value',
    'delete_product_feature_variants',
    'redirect_complete',
    'get_product_features_list_before_select',
    'update_category_post',
    'get_product_option_data_pre'
);