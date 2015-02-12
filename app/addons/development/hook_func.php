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

use Tygh\FeaturesCache;
use Tygh\Registry;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

function fn_development_get_product_features_list_before_select(&$fields, $join, $condition, $product, $display_on, $lang_code)
{
    $fields .= db_quote(", vd.mens_clothes_size_chart, vd.womens_clothes_size_chart, vd.mens_shoes_size_chart, vd.womens_shoes_size_chart");
}

function fn_development_redirect_complete(&$meta_redirect)
{
    $meta_redirect = false;
}

function fn_development_delete_product_feature_variants($feature_id, $variant_ids)
{
    if (!empty($feature_id)) {
        FeaturesCache::deleteFeature($feature_id);
    } elseif (!empty($variant_ids)) {
        FeaturesCache::deleteVariants($variant_ids);
    }
}

function fn_development_update_product_features_value($product_id, $product_features, $add_new_variant, $lang_code)
{
    $features = db_get_array("SELECT * FROM ?:product_features_values WHERE product_id = ?i", $product_id);
    FeaturesCache::updateProductFeaturesValue($product_id, $features, $lang_code);
}

function fn_development_render_block_register_cache($block, $cache_name, &$block_scheme, $register_cache, $display_block)
{
    if (!empty($block['properties']['random']) && $block['properties']['random'] == 'Y') {
        unset($block_scheme['cache']);
    }
}

function fn_development_get_category_data_post($category_id, $field_list, $get_main_pair, $skip_company_condition, $lang_code, &$category_data)
{
    if (!empty($category_data['brand_id'])) {
        list($brands) = fn_get_product_feature_variants(array(
            'feature_id' => BRAND_FEATURE_ID,
            'feature_type' => 'E',
            'variant_ids' => $category_data['brand_id'],
            'get_images' => true
        ));
        $category_data['brand'] = $brands[$category_data['brand_id']];
    }
}

function fn_development_get_product_feature_variants($fields, $join, &$condition, $group_by, $sorting, $lang_code, $limit)
{
    if (!empty($params['variant_ids'])) {
        $params['variant_ids'] = is_array($params['variant_ids']) ? $params['variant_ids'] : array($params['variant_ids']);
        $condition .= db_quote(" AND ?:product_feature_variants.variant_id IN (?n)", $params['variant_ids']);
    }
}

function fn_development_get_categories_post(&$categories_list, $params, $lang_code)
{
    if (!empty($params['roundabout']) && !empty($categories_list)) {
        $brand_ids = array();
        foreach ($categories_list as $i => $category) {
            if (!empty($category['brand_id'])) {
                $brand_ids[] = $category['brand_id'];
            }
        }
        if (!empty($brand_ids)) {
            list($brands) = fn_get_product_feature_variants(array(
                'feature_id' => BRAND_FEATURE_ID,
                'feature_type' => 'E',
                'variant_ids' => $brand_ids,
                'get_images' => true
            ));
            foreach ($categories_list as $i => $category) {
                if (!empty($category['brand_id'])) {
                    $categories_list[$i]['brand'] = $brands[$category['brand_id']];
                }
            }
        }
    }
}

function fn_development_get_lang_var_post(&$value, $var_name)
{
    $value = fn_check_vars($value);
}

function fn_development_get_categories(&$params, $join, $condition, &$fields, $group_by, $sortings, $lang_code)
{
    $fields[] = '?:categories.note_url';
    $fields[] = '?:categories.note_text';
    if (!empty($params['roundabout'])) {
        $params['get_images'] = true;
        $fields[] = '?:category_descriptions.description';
        $fields[] = '?:categories.brand_id';
    }
}

function fn_development_top_menu_form(&$v, $type, $id, $use_name)
{
    if ($type == 'P') {
        $params = array('plain' => true);
        list($players,) = fn_get_players($params);
        if (!empty($players)) {
            $atp = array();
            $wta = array();
            foreach($players as $i => $player) {
                if ($player['gender'] == 'M') {
                    $atp[] = $player;
                } else {
                    $wta[] = $player;
                }
            }
            $v['subitems'] = array(
                array('descr' => __('atp'), 'param' => 'players.list', 'subitems' => fn_top_menu_standardize($atp, 'player_id', 'player', '', 'players.view?player_id=')),
                array('descr' => __('wta'), 'param' => 'players.list', 'subitems' => fn_top_menu_standardize($wta, 'player_id', 'player', '', 'players.view?player_id=')),
            );
        }
    }
}

function fn_development_get_filters_products_count_pre(&$params)
{
    $params['get_all'] = true;
}

function fn_development_gather_additional_product_data_post(&$product, $auth, $params)
{
    if (AREA == 'C') {
        if ($product['tracking'] == 'O' && !empty($product['combination_hash'])) {
            $combination = $product['combination_hash'];
        } elseif ($product['tracking'] == 'B') {
            $combination = 0;
        }
        if (isset($combination)) {
            if ($auth['user_id'] == 0 && !empty($_SESSION['product_notifications']['email'])) {
                $subscription_id = db_get_field("SELECT subscription_id FROM ?:product_subscriptions WHERE product_id = ?i AND combination_hash = ?i AND email = ?s", $product['product_id'], $combination, $_SESSION['product_notifications']['email']);
                if (!empty($subscription_id)) {
                    $product['inventory_notification'] = 'Y';
                    $product['inventory_notification_email'] = $_SESSION['product_notifications']['email'];
                }
            } else {
                $email = db_get_field("SELECT email FROM ?:product_subscriptions WHERE product_id = ?i AND combination_hash = ?i AND user_id = ?i", $product['product_id'], $combination, $auth['user_id']);
                if (!empty($email)) {
                    $product['inventory_notification'] = 'Y';
                    $product['inventory_notification_email'] = $email;
                }
            }
        }
    }
}

function fn_development_get_product_features_list_post(&$features_list, $product, $display_on, $lang_code)
{
    if (!empty($features_list[BRAND_FEATURE_ID]['variants'])) {
        $image_pairs = fn_get_image_pairs(array_keys($features_list[BRAND_FEATURE_ID]['variants']), 'feature_variant', 'V', true, true, CART_LANGUAGE);
        foreach ($features_list[BRAND_FEATURE_ID]['variants'] as $variant_id => $variant) {
            $features_list[BRAND_FEATURE_ID]['variants'][$variant_id]['image_pair'] = array_pop($image_pairs[$variant_id]);
        }
    }
}

function fn_development_update_product_filter(&$filter_data, $filter_id, $lang_code)
{
    if (!empty($filter_data['feature_type']) && $filter_data['feature_type'] == 'N') {
        if (!empty($filter_data['is_slider']) && $filter_data['is_slider'] != 'Y') {
            db_query("UPDATE ?:product_filters SET field_type = '' WHERE filter_id = ?i", $filter_id);
        } else {
            $field_type = db_get_field("SELECT field_type FROM ?:product_filters WHERE filter_id = ?i", $filter_id);
            if (empty($field_type)) {
                $existing_codes = db_get_fields('SELECT field_type FROM ?:product_filters GROUP BY field_type');
                $existing_codes[] = 'P';
                $existing_codes[] = 'A';
                $existing_codes[] = 'F';
                $existing_codes[] = 'V';
                $existing_codes[] = 'R';
                $existing_codes[] = 'B';
                $codes = array_diff(range('A', 'Z'), $existing_codes);
                $field_type = reset($codes);
                db_query("UPDATE ?:product_filters SET field_type = ?s WHERE filter_id = ?i", $field_type, $filter_id);
            }
        }
    }
}

function fn_development_add_range_to_url_hash_pre(&$hash, $range, $field_type)
{
    $fields = fn_get_product_filter_fields();
    foreach ($fields as $i => $fld) {
        if ($field_type == $i && !empty($fld['slider']) && !in_array($i, array('P', 'A'))) {
            $pattern = '/(' . $i . '\d+-\d+\.?)|(\.?' . $i . '\d+-\d+)/';
            $hash = preg_replace($pattern, '', $hash);          
        }
    }

}

function fn_development_get_product_filter_fields(&$filters)
{
    $fields = db_get_array("SELECT ?:product_filters.field_type, ?:product_filter_descriptions.filter, ?:product_filters.feature_id FROM ?:product_filters LEFT JOIN ?:product_features ON ?:product_features.feature_id = ?:product_filters.feature_id LEFT JOIN ?:product_filter_descriptions ON ?:product_filter_descriptions.filter_id = ?:product_filters.filter_id AND ?:product_filter_descriptions.lang_code = ?s WHERE ?:product_filters.is_slider = 'Y' AND ?:product_features.feature_type = 'N' AND ?:product_filters.field_type != ''", CART_LANGUAGE);

    if (!empty($fields)) {
        foreach ($fields as $i => $field) {
            $filters[$field['field_type']] = array(
                'description' => $field['filter'],
                'feature_id' => $field['feature_id'],
                'condition_type' => 'S',
                'slider' => true,
            );
        }
    }
}

function fn_development_get_filter_range_name_post(&$range_name, $range_type, $range_id)
{
    if (!in_array($range_type, array('P', 'A', 'V', 'R'))) {
        $from = fn_strtolower(__('range_from'));
        $to = fn_strtolower(__('range_to'));
        $fields = fn_get_product_filter_fields();
        $data = explode('-', $range_id);
        $from_val = !empty($data[0]) ? $data[0] : 0;
        $to_val = !empty($data[1]) ? $data[1] : 0;
        $range_name = $fields[$range_type]['description'] . " : $from $from_val $to $to_val";
    }
}

function fn_development_get_filters_products_count_before_select_filters(&$sf_fields, $sf_join, $condition, $sf_sorting, $params)
{
    $sf_fields .= db_quote(", ?:product_filters.is_slider, ?:product_filters.units, ?:product_filters.note_url, ?:product_filters.note_text");
}

function fn_development_get_products($params, &$fields, &$sortings, &$condition, &$join, $sorting, $group_by, $lang_code, $having)
{
    $fields[] = '?:categories.id_path';
    $sortings['random'] = 'RAND()';
    if (!empty($params['similar_pid'])) {
        $similar_products_features = array(
            'R' => array(R_BALANCE_FEATURE_ID, R_LENGTH_FEATURE_ID, R_HEADSIZE_FEATURE_ID, R_WEIGHT_FEATURE_ID, R_STIFFNESS_FEATURE_ID),
            'A' => array('52', '50'),
            'S' => array('54'),
            'B' => array('58'),
            'ST' => array('77', '60'),
            'BL' => array('64'),
            'OG' => array('66'),
            'BG' => array('72'),
        );
        $digit_features = array(R_WEIGHT_FEATURE_ID, R_STIFFNESS_FEATURE_ID, R_BALANCE_FEATURE_ID);
        if (!empty($similar_products_features[$_SESSION['category_type']])) {
            foreach ($similar_products_features[$_SESSION['category_type']] as $i => $feature_id) {
                if (!empty($_SESSION['product_features'][$feature_id])) {
                    if (!empty($_SESSION['product_features'][$feature_id]['variants'])) {
                        foreach ($_SESSION['product_features'][$feature_id]['variants'] as $j => $variant) {
                            if (!empty($variant['variant_id'])) {
                                $params['features_condition'][$feature_id]['variants'][] = array(
                                    'variant_id' => $variant['variant_id'],
                                );
                            }
                        }
                    } else {
                        if (!in_array($feature_id, $digit_features)) {
                            if (!empty($_SESSION['product_features'][$feature_id]['variant_id'])) {
                                $params['features_condition'][$feature_id] = array(
                                    'variant_id' => $_SESSION['product_features'][$feature_id]['variant_id'],
                                );
                            } elseif (!empty($_SESSION['product_features'][$feature_id]['value'])) {
                                $params['features_condition'][$feature_id] = array(
                                    'value' => $_SESSION['product_features'][$feature_id]['value'],
                                );
                            }
                        } else {
                            if ($feature_id == R_WEIGHT_FEATURE_ID) {
                                $margin_value = 5;
                            }
                            if ($feature_id == R_STIFFNESS_FEATURE_ID) {
                                $margin_value = 2;
                            }
                            if ($feature_id == R_BALANCE_FEATURE_ID) {
                                $margin_value = 0.32;
                            }
                            $params['features_condition'][$feature_id] = array(
                                'min_value' => $_SESSION['product_features'][$feature_id]['variant_name'] - $margin_value,
                                'max_value' => $_SESSION['product_features'][$feature_id]['variant_name'] + $margin_value
                            );
                        }
                    }
                }
            }
        } elseif (!empty($_SESSION['product_category'])) {
            $condition .= db_quote(" AND ?:categories.category_id = ?i", $_SESSION['product_category']);
        } else {
            $condition .= " AND NULL";
        }
    }
    if (!empty($params['same_brand_pid'])) {
        if (!empty($_SESSION['product_features'][BRAND_FEATURE_ID])) {
            $params['features_condition'][BRAND_FEATURE_ID] = array(
                'variant_id' => $_SESSION['product_features'][BRAND_FEATURE_ID]['variant_id'],
            );
        } else {
            $condition .= " AND NULL";
        }
    }
    if (!empty($params['features_condition'])) {
        FeaturesCache::getProductsConditions($params['features_condition'], $join, $condition, $lang_code);
    }
}

function fn_development_calculate_cart_items(&$cart, $cart_products, $auth)
{
    if (!empty($cart_products)) {
        foreach ($cart_products as $i => $product) {
            $cart['products'][$i]['main_category'] = $product['main_category'];
        }
    }
}

function fn_development_get_products_pre(&$params, $items_per_page, $lang_code)
{
    if (!empty($params['shoes_surface'])) {
        $params['cid'] = SHOES_CATEGORY_ID;
        $params['subcats'] = 'Y';
        $feature_hash = 'V' . ALLCOURT_SURFACE_FV_ID;
        if ($params['shoes_surface'] == 'clay') {
            $feature_hash .= '.V' . CLAY_SURFACE_FV_ID;
        }
        if ($params['shoes_surface'] == 'grass') {
            $feature_hash .= '.V' . GRASS_SURFACE_FV_ID;
        }
        $params['features_hash'] = (!empty($params['features_hash']) ? '.' : '') . $feature_hash;
    }
    if (!empty($params['rackets_type'])) {
        $params['cid'] = RACKETS_CATEGORY_ID;
        $params['subcats'] = 'Y';
        if ($params['rackets_type'] == 'power') {
            $feature_hash = 'V' . POWER_RACKET_FV_ID;
        }
        if ($params['rackets_type'] == 'club') {
            $feature_hash = 'V' . CLUB_RACKET_FV_ID;
        }
        if ($params['rackets_type'] == 'pro') {
            $feature_hash = 'V' . PRO_RACKET_FV_ID;
        }
        if ($params['rackets_type'] == 'heavy_head_light') {
            $params['features_condition'][R_WEIGHT_FEATURE_ID] = array(
                'min_value' => 300
            );
            $params['features_condition'][R_BALANCE_FEATURE_ID] = array(
                'max_value' => 35
            );
        }
        if ($params['rackets_type'] == 'light_head_heavy') {
            $params['features_condition'][R_WEIGHT_FEATURE_ID] = array(
                'max_value' => 300
            );
            $params['features_condition'][R_BALANCE_FEATURE_ID] = array(
                'min_value' => 35
            );
        }
        if ($params['rackets_type'] == 'stiff') {
            $params['features_condition'][R_STIFFNESS_FEATURE_ID] = array(
                'min_value' => 65
            );
        }
        if ($params['rackets_type'] == 'soft') {
            $params['features_condition'][R_STIFFNESS_FEATURE_ID] = array(
                'max_value' => 64
            );
        }
        if ($params['rackets_type'] == 'regular_head') {
            $params['features_condition'][R_HEADSIZE_FEATURE_ID] = array(
                'min_value' => 612,
                'max_value' => 677
            );
        }
        if ($params['rackets_type'] == 'regular_length') {
            $params['features_condition'][R_LENGTH_FEATURE_ID] = array(
                'variant_id' => REGULAR_LENGTH_FV_ID
            );
        }
        if ($params['rackets_type'] == 'kids_17') {
            $params['features_condition'][R_LENGTH_FEATURE_ID] = array(
                'variant_id' => KIDS_17_FV_ID
            );
        }
        if ($params['rackets_type'] == 'kids_19') {
            $params['features_condition'][R_LENGTH_FEATURE_ID] = array(
                'variant_id' => KIDS_19_FV_ID
            );
        }
        if ($params['rackets_type'] == 'kids_21') {
            $params['features_condition'][R_LENGTH_FEATURE_ID] = array(
                'variant_id' => KIDS_21_FV_ID
            );
        }
        if ($params['rackets_type'] == 'kids_23') {
            $params['features_condition'][R_LENGTH_FEATURE_ID] = array(
                'variant_id' => KIDS_23_FV_ID
            );
        }
        if ($params['rackets_type'] == 'kids_25') {
            $params['features_condition'][R_LENGTH_FEATURE_ID] = array(
                'variant_id' => KIDS_25_FV_ID
            );
        }
        if ($params['rackets_type'] == 'kids_26') {
            $params['features_condition'][R_LENGTH_FEATURE_ID] = array(
                'variant_id' => KIDS_26_FV_ID
            );
        }
        if ($params['rackets_type'] == 'closed_pattern') {
            $params['features_condition'][R_STRING_PATTERN_FEATURE_ID] = array(
                'variant_id' => CLOSED_PATTERN_FV_ID
            );
        }
        if ($params['rackets_type'] == 'open_pattern') {
            $params['features_condition'][R_STRING_PATTERN_FEATURE_ID] = array(
                'not_variant' => CLOSED_PATTERN_FV_ID
            );
        }
        if (!empty($feature_hash)) {
            $params['features_hash'] = (!empty($params['features_hash']) ? '.' : '') . $feature_hash;
        }
    }
    if (!empty($params['strings_type'])) {
        $params['cid'] = STRINGS_CATEGORY_ID;
        $params['subcats'] = 'Y';
        if ($params['strings_type'] == 'natural_gut') {
            $params['cid'] = NATURAL_GUT_MATERIAL_CATEGORY_ID;
        }
        if ($params['strings_type'] == 'nylon') {
            $params['cid'] = NYLON_MATERIAL_CATEGORY_ID;
        }
        if ($params['strings_type'] == 'polyester') {
            $params['cid'] = POLYESTER_MATERIAL_CATEGORY_ID;
        }
        if ($params['strings_type'] == 'hybrid') {
            $params['cid'] = HYBRID_MATERIAL_CATEGORY_ID;
        }
        if ($params['strings_type'] == 'monofil') {
            $params['cid'] = MONO_STRUCTURE_CATEGORY_ID;
        }
        if ($params['strings_type'] == 'multifil') {
            $params['cid'] = MULTI_STRUCTURE_CATEGORY_ID;
        }
        if ($params['strings_type'] == 'textured') {
            $params['cid'] = TEXTURED_STRUCTURE_CATEGORY_ID;
        }
        if ($params['strings_type'] == 'synthetic_gut') {
            $params['cid'] = SYNTH_GUT_STRUCTURE_CATEGORY_ID;
        }
        if (!empty($feature_hash)) {
            $params['features_hash'] = (!empty($params['features_hash']) ? '.' : '') . $feature_hash;
        }
    }
}

function fn_development_delete_product_post($product_id, $product_deleted)
{
    if ($product_deleted) {
        db_query("DELETE FROM ?:players_gear WHERE product_id = ?i", $product_id);
        FeaturesCache::deleteProduct($product_id);
    }
}

function fn_development_update_product_post($product_data, $product_id, $lang_code, $create)
{
    if (isset($product_data['players'])) {
        if ($create) {
            $existing_gear = array();
        } else {
            $existing_gear = db_get_fields("SELECT player_id FROM ?:players_gear WHERE product_id = ?i", $product_id);
        }
        $product_data['players'] = (empty($product_data['players'])) ? array() : explode(',', $product_data['players']);
        $to_delete = array_diff($existing_gear, $product_data['players']);

        if (!empty($to_delete)) {
            db_query("DELETE FROM ?:players_gear WHERE player_id IN (?n) AND product_id = ?i", $to_delete, $product_id);
        }
        $to_add = array_diff($product_data['players'], $existing_gear);

        if (!empty($to_add)) {
            foreach ($to_add as $i => $gr) {
                $__data = array(
                    'product_id' => $product_id,
                    'player_id' => $gr
                );
                db_query("REPLACE INTO ?:players_gear ?e", $__data);
            }
        }
    }
}

function fn_development_update_product_pre(&$product_data, $product_id, $lang_code, $can_update)
{
    if (!empty($product_data['main_category'])) {
        $id_path = explode('/', db_get_field("SELECT id_path FROM ?:categories WHERE category_id = ?i", $product_data['main_category']));
        $enable_discussion = array('254', '263', '265', '266', '312', '313', '315', '316');
        $intersection = array_intersect($id_path, $enable_discussion);
        if (!empty($intersection)) {
            $product_data['discussion_type'] = 'B';
        }
        
        $players = (empty($product_data['players'])) ? array() : explode(',', $product_data['players']);
        $variant_ids = db_get_fields("SELECT feature_variant_id FROM ?:players WHERE player_id IN (?n)", $players);
        $product_data['product_features'][PLAYER_FEATURE_ID] = array_combine($variant_ids, $variant_ids);
        
        if ($product_data['auto_price'] == 'Y' && $product_data['margin'] == 0 && $product_data['net_cost'] > 0) {
            fn_get_product_margin($product_data);
        }

        $old_data = db_get_row("SELECT auto_price, margin, net_cost, net_currency_code FROM ?:products WHERE product_id = ?i", $product_id);
        if ($product_data['auto_price'] == 'Y' && $product_data['net_cost'] > 0 && $product_data['margin'] > 0 && !empty($product_data['net_currency_code']) && ($product_data['auto_price'] != $old_data['auto_price'] || $product_data['margin'] != $old_data['margin'] || $product_data['net_cost'] != $old_data['net_cost'] || $product_data['net_currency_code'] != $old_data['net_currency_code'])) {
            $base_price = fn_calculate_base_price($product_data);
            $product_data['price'] = fn_round_price($base_price);
            if (!empty($product_data['prices'])) {
                foreach ($product_data['prices'] as $i => $p_data) {
                    if (!empty($p_data['lower_limit'])) {
                        if ($p_data['lower_limit'] == 1) {
                            $product_data['prices'][$i]['price'] = fn_round_price($base_price);
                        } elseif ($p_data['type'] == 'A') {
                            $product_data['prices'][$i]['price'] = fn_round_price($base_price - $base_price * RACKETS_QTY_DSC_PRC / 100);
                        }
                    }
                }
            }
        }
    }
}

function fn_development_update_category_post($category_data, $category_id, $lang_code)
{
    if ($category_data['recalculate_margins'] == 'Y') {
        $products = array();
        $_params = array (
            'cid' => $category_id,
            'subcats' => 'Y'
        );
        list($prods,) = fn_get_products($_params);
        if (!empty($prods)) {
            foreach ($prods as $i => $prod) {
                if ($prod['auto_price'] == 'Y' && $prod['net_cost'] > 0) {
                    unset($prod['margin']);
                    $products[$prod['product_id']] = $prod;
                }
            }
        }
        if (!empty($products)) {
            $result = fn_process_update_prices($products);
            
            if (!empty($result)) {
                db_query("REPLACE INTO ?:product_prices ?m", $result);
            }
        }
    }
}

function fn_development_get_product_data_post(&$product_data, $auth, $preview, $lang_code)
{
    if (AREA == 'A') {
        $plain = true;
    } else {
        $plain = false;
    }
    list($players, ) = fn_get_players(array('product_id' => $product_data['product_id'], 'plain' => $plain));
    if (AREA == 'A') {
        $product_data['players'] = implode(',', array_keys($players));
    } else {
        $product_data['players'] = $players;
    }
    $types = fn_get_categories_types($product_data['main_category']);
    $product_data['category_type'] = $types[$product_data['main_category']];
}

function fn_development_get_products_post(&$products, $params, $lang_code)
{
    if (!empty($products)) {
        foreach ($products as $i => $product) {
            $products[$i]['type'] = fn_identify_category_type($product['id_path']);
        }
    }
    if (!empty($params['shuffle']) && $params['shuffle'] == 'Y') {
        shuffle($products);
    }
}