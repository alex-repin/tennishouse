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

use Tygh\Registry;
use Tygh\Http;
if (!defined('BOOTSTRAP')) { die('Access denied'); }

function fn_get_currency_exchange_rates()
{
    $url = 'http://www.cbr.ru/scripts/XML_daily.asp';
    $result_xml = Http::get($url);
    $_result = @simplexml_load_string($result_xml);
    $result = array();
    if (!empty($_result->Valute)) {
        foreach ($_result->Valute as $cur_rate) {
            $result[(string) $cur_rate->CharCode] = str_replace(',', '.', (string) $cur_rate->Value);
        }
    }

    return $result;
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
                array('descr' => __('atp'), 'subitems' => fn_top_menu_standardize($atp, 'player_id', 'player', '', 'players.view?player_id=')),
                array('descr' => __('wta'), 'subitems' => fn_top_menu_standardize($wta, 'player_id', 'player', '', 'players.view?player_id=')),
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
        if ($product['tracking'] == 'O') {
            $combination = $product['combination_hash'];
        } elseif ($product['tracking'] == 'B') {
            $combination = 0;
        }
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

function fn_get_type_feature($features)
{
    
}
function fn_get_subtitle_feature($features, $type = 'R')
{
    if ($type == 'R') {
        $feature_ids = array(BABOLAT_SERIES_FEATURE_ID, HEAD_SERIES_FEATURE_ID, WILSON_SERIES_FEATURE_ID, DUNLOP_SERIES_FEATURE_ID, PRINCE_SERIES_FEATURE_ID, YONEX_SERIES_FEATURE_ID, PROKENNEX_SERIES_FEATURE_ID);
    } else if ($type == 'A') {
        $feature_ids = array(CLOTHES_TYPE_FEATURE_ID);
    } else if ($type == 'S') {
        $feature_ids = array(SHOES_SURFACE_FEATURE_ID);
    } else if ($type == 'B') {
        $feature_ids = array(BAG_SIZE_FEATURE_ID);
    } else if ($type == 'ST') {
        $feature_ids = array(STRING_TYPE_FEATURE_ID);
    } else if ($type == 'BL') {
        $feature_ids = array(BALLS_TYPE_FEATURE_ID);
    } else if ($type == 'OG') {
        $feature_ids = array(OG_TYPE_FEATURE_ID);
    } else if ($type == 'BG') {
        $feature_ids = array(BG_TYPE_FEATURE_ID);
    }
    if (!empty($feature_ids)) {
        foreach ($features as $feature_id => $feature) {
            $key = array_search($feature_id, $feature_ids);
            if ($key !== false) {
                return $features[$feature_ids[$key]];
            }
        }
    }
    
    return false;
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
    $sf_fields .= db_quote(", ?:product_filters.is_slider, ?:product_filters.units");
}

function fn_display_subheaders($category_id)
{
    return in_array($category_id, array(RACKETS_CATEGORY_ID, APPAREL_CATEGORY_ID, SHOES_CATEGORY_ID, BAGS_CATEGORY_ID));
}

function fn_development_get_products($params, &$fields, $sortings, &$condition, &$join, $sorting, $group_by, $lang_code, $having)
{
    $fields[] = '?:categories.id_path';
    if (!empty($params['similar_pid'])) {
        $similar_products_features = array(
            'R' => array('24', '25', '20', '22', '23')
        );
        if (!empty($similar_products_features[$_SESSION['category_type']])) {
            foreach ($similar_products_features[$_SESSION['category_type']] as $i => $feature_id) {
                if (!empty($_SESSION['product_features'][$feature_id])) {
                    $join .= db_quote(" LEFT JOIN ?:product_features_values AS feature_?i ON feature_?i.product_id = products.product_id AND feature_?i.feature_id = ?i AND feature_?i.lang_code = ?s", $feature_id, $feature_id, $feature_id, $feature_id, $feature_id, $lang_code);
                    if (in_array($feature_id, array('20', '25'))) {
                        $condition .= db_quote(" AND feature_?i.variant_id = ?i ", $feature_id, $_SESSION['product_features'][$feature_id]['variant_id']);
                    } elseif (in_array($feature_id, array('22', '23', '24'))) {
                        $join .= db_quote(" LEFT JOIN ?:product_feature_variant_descriptions AS fvd_?i ON fvd_?i.variant_id = feature_?i.variant_id AND fvd_?i.lang_code = ?s ", $feature_id, $feature_id, $feature_id, $feature_id, $lang_code);
                        if ($feature_id == '22') {
                            $condition .= db_quote(" AND fvd_?i.variant <= ?d + 5 AND fvd_?i.variant >= ?d - 5 ", $feature_id, $_SESSION['product_features'][$feature_id]['variant_name'], $feature_id, $_SESSION['product_features'][$feature_id]['variant_name']);
                        }
                        if ($feature_id == '23') {
                            $condition .= db_quote(" AND fvd_?i.variant <= ?d + 2 AND fvd_?i.variant >= ?d - 2 ", $feature_id, $_SESSION['product_features'][$feature_id]['variant_name'], $feature_id, $_SESSION['product_features'][$feature_id]['variant_name']);
                        }
                        if ($feature_id == '24') {
                            $condition .= db_quote(" AND fvd_?i.variant <= ?d + .32 AND fvd_?i.variant >= ?d - .32 ", $feature_id, $_SESSION['product_features'][$feature_id]['variant_name'], $feature_id, $_SESSION['product_features'][$feature_id]['variant_name']);
                        }
                    }
                }
            }
        }
    }
    if (!empty($params['same_brand_pid']) && !empty($_SESSION['product_features'][BRAND_FEATURE_ID])) {
        $join .= db_quote(" LEFT JOIN ?:product_features_values ON ?:product_features_values.product_id = products.product_id AND ?:product_features_values.feature_id = ?i ", BRAND_FEATURE_ID);
        $condition .= db_quote(" AND ?:product_features_values.variant_id = ?i ", $_SESSION['product_features'][BRAND_FEATURE_ID]['variant_id']);
    }
}

function fn_show_age($age)
{
    if ($age > 4 && $age < 21) {
        $word = __("years_old_5");
    } else {
        $low_age = $age % 10;
        if ($low_age == 1) {
            $word = __("years_old_1");
        } elseif ($low_age > 1 && $low_age < 5) {
            $word = __("years_old_2_4");
        } else {
            $word = __("years_old_5");
        }
    }
    
    return $age . ' ' . $word;
}
function fn_get_age($birth_date)
{
    if (empty($birth_date)) {
        return false;
    }
    $now = time();
    $years = fn_date_format(time(), "%Y") - fn_date_format($birth_date, "%Y");

    if (fn_date_format(time(), "%m") < fn_date_format($birth_date, "%m") || (fn_date_format(time(), "%m") == fn_date_format($birth_date, "%d") && fn_date_format(time(), "%d") < fn_date_format($birth_date, "%m"))) {
        $years--;
    }
    return $years;
}

function fn_development_delete_product_post($product_id, $product_deleted)
{
    if ($product_deleted) {
        db_query("DELETE FROM ?:players_gear WHERE product_id = ?i", $product_id);
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
        if (!empty(array_intersect($id_path, $enable_discussion))) {
            $product_data['discussion_type'] = 'B';
        }
    }
    $players = (empty($product_data['players'])) ? array() : explode(',', $product_data['players']);
    $variant_ids = db_get_fields("SELECT feature_variant_id FROM ?:players WHERE player_id IN (?n)", $players);
    $product_data['product_features'][PLAYER_FEATURE_ID] = array_combine($variant_ids, $variant_ids);
}

function fn_get_category_type($category_id)
{
    $path = db_get_field("SELECT id_path FROM ?:categories WHERE category_id = ?i", $category_id);
    return fn_identify_category_type($path);
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
    $product_data['category_type'] = fn_get_category_type($product_data['main_category']);
}

function fn_identify_category_type($path)
{
    $type = '';
    if (!empty($path)) {
        $cats = explode('/', $path);
        if (in_array(RACKETS_CATEGORY_ID, $cats)) {
            $type = 'R';
        } elseif (in_array(APPAREL_CATEGORY_ID, $cats)) {
            $type = 'A';
        } elseif (in_array(SHOES_CATEGORY_ID, $cats)) {
            $type = 'S';
        } elseif (in_array(BAGS_CATEGORY_ID, $cats)) {
            $type = 'B';
        } elseif (in_array(SPORTS_NUTRITION_CATEGORY_ID, $cats)) {
            $type = 'N';
        } elseif (in_array(ACCESSORIES_CATEGORY_ID, $cats)) {
            if (in_array(STRINGS_CATEGORY_ID, $cats)) {
                $type = 'ST';
            } elseif (in_array(BALLS_CATEGORY_ID, $cats)) {
                $type = 'BL';
            } elseif (in_array(TOWELS_CATEGORY_ID, $cats)) {
                $type = 'TW';
            } elseif (in_array(OVERGRIPS_CATEGORY_ID, $cats)) {
                $type = 'OG';
            } elseif (in_array(BASEGRIPS_CATEGORY_ID, $cats)) {
                $type = 'BG';
            } elseif (in_array(DAMPENERS_CATEGORY_ID, $cats)) {
                $type = 'DP';
            } else {
                $type = 'C';
            }
        }
    }
    
    return $type;
}
function fn_development_get_products_post(&$products, $params, $lang_code)
{
    if (!empty($products)) {
        foreach ($products as $i => $product) {
            $products[$i]['type'] = fn_identify_category_type($product['id_path']);
        }
    }
}

function fn_insert_before_key($originalArray, $originalKey, $insertKey, $insertValue )
{
    $newArray = array();
    $inserted = false;

    foreach( $originalArray as $key => $value ) {

        if( !$inserted && $key === $originalKey ) {
            $newArray[ $insertKey ] = $insertValue;
            $inserted = true;
        }

        $newArray[ $key ] = $value;

    }

    return $newArray;

}

function fn_get_player_data($player_id)
{
    $field_list = "?:players.*";

    $player_data = db_get_row("SELECT $field_list FROM ?:players LEFT JOIN ?:players_gear ON ?:players.player_id = ?:players_gear.player_id WHERE ?:players.player_id = ?i", $player_id);

    if (!empty($player_data)) {
        $player_data['main_pair'] = fn_get_image_pairs($player_id, 'player', 'M', true, true);
        $player_data['gear'] = db_get_fields("SELECT product_id FROM ?:players_gear WHERE player_id = ?i", $player_id);
    }

    return (!empty($player_data) ? $player_data : false);
}

function fn_get_players($params)
{
    $fields = array (
        '?:players.*',
        'GROUP_CONCAT(?:players_gear.product_id) as gear'
    );

    $condition = $join = '';
    $join .= db_quote(" LEFT JOIN ?:players_gear ON ?:players_gear.player_id = ?:players.player_id ");

    if (AREA == 'C') {
        $_statuses = array('A'); // Show enabled players
        $condition .= db_quote(" AND ?:players.status IN (?a)", $_statuses);
    }

    if (!empty($params['player'])) {
        $condition .= db_quote(" AND ?:players.player LIKE ?l", "%".trim($params['player'])."%");
    }

    if (!empty($params['gender'])) {
        $condition .= db_quote(" AND ?:players.gender = ?s", $params['gender']);
    }

    if (!empty($params['ranking'])) {
        $condition .= db_quote(" AND ?:players.ranking = ?s", $params['ranking']);
    }

    if (!empty($params['status'])) {
        $condition .= db_quote(" AND ?:players.status IN (?a)", $params['status']);
    }

    if (!empty($params['item_ids'])) {
        $condition .= db_quote(' AND ?:players.player_id IN (?n)', explode(',', $params['item_ids']));
    }

    if (!empty($params['product_id'])) {
        $condition .= db_quote(' AND ?:players_gear.product_id = ?i', $params['product_id']);
    }

    if (!empty($params['except_id']) && (empty($params['item_ids']) || !empty($params['item_ids']) && !in_array($params['except_id'], explode(',', $params['item_ids'])))) {
        $condition .= db_quote(' AND ?:players.player_id != ?i', $params['except_id']);
    }

    $limit = $group_by = '';

    if (!empty($params['limit'])) {
        $limit = db_quote(' LIMIT 0, ?i', $params['limit']);
    }

    $players = db_get_hash_array('SELECT ' . implode(',', $fields) . " FROM ?:players ?p WHERE 1 ?p GROUP BY ?:players.player_id ORDER BY ?:players.ranking ASC ?p", 'player_id', $join, $condition, $limit);

    if (empty($players)) {
        return array(array(), $params);
    }

    if (empty($params['plain'])) {
        foreach ($players as $k => $v) {
            $players[$k]['main_pair'] = fn_get_image_pairs($v['player_id'], 'player', 'M', true, true);
            $players[$k]['gear'] = explode(',', $players[$k]['gear']);
        }
    }
    
    return array($players, $params);
}

function fn_delete_player($player_id)
{
    if (empty($player_id)) {
        return false;
    }

    // Log player deletion
    fn_log_event('players', 'delete', array(
        'player_id' => $player_id,
    ));
    $variant_variant_id = db_get_field("SELECT feature_variant_id FROM ?:players WHERE player_id = ?i", $player_id);
    if (!empty($variant_variant_id)) {
        fn_delete_product_feature_variants(0, array($variant_variant_id));
    }

    // Deleting player
    db_query("DELETE FROM ?:players WHERE player_id = ?i", $player_id);
    db_query("DELETE FROM ?:players_gear WHERE player_id = ?i", $player_id);

    // Deleting player images
    fn_delete_image_pairs($player_id, 'player');
    
    return true;
}

function fn_update_player($player_data, $player_id = 0)
{
    $_data = $player_data;

    if (isset($player_data['birthday'])) {
        $_data['birthday'] = fn_parse_date($player_data['birthday']);
    }

    if (isset($_data['ranking']) && empty($_data['ranking']) && $_data['ranking'] != '0') {
        $_data['ranking'] = db_get_field("SELECT max(ranking) FROM ?:players");
        $_data['ranking'] = $_data['ranking'] + 1;
    }
    
    $variant_data = array(
        'variant' => $player_data['player']
    );

    // create new player
    if (empty($player_id)) {

        $create = true;

        $_data['feature_variant_id'] = fn_update_product_feature_variant(PLAYER_FEATURE_ID, 'M', $variant_data);

        $player_id = db_query("INSERT INTO ?:players ?e", $_data);
        $existing_gear = array();
        
    // update existing player
    } else {

        $arow = db_query("UPDATE ?:players SET ?u WHERE player_id = ?i", $_data, $player_id);

        if ($arow === false) {
            fn_set_notification('E', __('error'), __('object_not_found', array('[object]' => __('player'))),'','404');
            $player_id = false;
        }
        $existing_gear = db_get_fields("SELECT product_id FROM ?:players_gear WHERE player_id = ?i", $player_id);
        $variant_data['variant_id'] = db_get_field("SELECT feature_variant_id FROM ?:players WHERE player_id = ?i", $player_id);
        fn_update_product_feature_variant(PLAYER_FEATURE_ID, 'M', $variant_data);
    }

    if (!empty($player_id) && isset($_data['gear'])) {

        // Log player add/update
        fn_log_event('players', !empty($create) ? 'create' : 'update', array(
            'player_id' => $player_id,
        ));
        
        $_data['gear'] = (empty($_data['gear'])) ? array() : explode(',', $_data['gear']);
        $to_delete = array_diff($existing_gear, $_data['gear']);

        if (!empty($to_delete)) {
            db_query("DELETE FROM ?:players_gear WHERE product_id IN (?n) AND player_id = ?i", $to_delete, $player_id);
        }
        $to_add = array_diff($_data['gear'], $existing_gear);

        if (!empty($to_add)) {
            foreach ($to_add as $i => $gr) {
                $__data = array(
                    'player_id' => $player_id,
                    'product_id' => $gr
                );
                db_query("REPLACE INTO ?:players_gear ?e", $__data);
            }
        }
    }
    
    return $player_id;

}
