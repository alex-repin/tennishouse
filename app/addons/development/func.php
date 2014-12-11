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

if (!defined('BOOTSTRAP')) { die('Access denied'); }

function fn_development_update_product_filter(&$filter_data, $filter_id, $lang_code)
{
    if (!empty($filter_data['feature_type']) && $filter_data['feature_type'] == 'N') {
        if ($filter_data['is_slider'] != 'Y') {
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
function fn_development_get_products_post(&$products, $params, $lang_code)
{
    if (!empty($products)) {
        foreach ($products as $i => $product) {
            $cats = explode('/', $product['id_path']);
            if (in_array(RACKETS_CATEGORY_ID, $cats)) {
                $products[$i]['type'] = 'R';
            } elseif (in_array(APPAREL_CATEGORY_ID, $cats)) {
                $products[$i]['type'] = 'A';
            } elseif (in_array(SHOES_CATEGORY_ID, $cats)) {
                $products[$i]['type'] = 'S';
            } elseif (in_array(BAGS_CATEGORY_ID, $cats)) {
                $products[$i]['type'] = 'B';
            } elseif (in_array(ACCESSORIES_CATEGORY_ID, $cats)) {
                $products[$i]['type'] = 'C';
            } elseif (in_array(SPORTS_NUTRITION_CATEGORY_ID, $cats)) {
                $products[$i]['type'] = 'N';
            }
        }
    }
}

function fn_development_get_products($params, &$fields, $sortings, $condition, $join, $sorting, $group_by, $lang_code, $having)
{
    $fields[] = '?:categories.id_path';
}

function fn_get_age($birth_date)
{
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

function fn_get_category_type($category_id)
{
    $path = explode('/', db_get_field("SELECT id_path FROM ?:categories WHERE category_id = ?i", $category_id));
    array_pop($path);
    $result = '';
    if (!empty($path)) {
        foreach (array_reverse($path) as $cat_id) {
            if ($cat_id == RACKETS_CATEGORY_ID) {
                $result = 'R';
                break;
            }
        }
    }
    
    return $result;
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

    // create new player
    if (empty($player_id)) {

        $create = true;

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
