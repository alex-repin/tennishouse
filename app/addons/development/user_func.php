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
use Tygh\LogFacade;
use Tygh\Http;
use Tygh\FeaturesCache;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

function fn_gather_additional_products_data_cs(&$products, $params)
{
    if (empty($products)) {
        return;
    } else {
        foreach ($products as $i => $prods) {
            if (!empty($prods['items'])) {
                fn_gather_additional_products_data($products[$i]['items'], $params);
            }
        }
    }
}

function fn_process_php_errors($errno, $errstr, $errfile, $errline, $errcontext)
{
    if (strpos($errfile, Registry::get('config.dir.var')) === false && strpos($errfile, Registry::get('config.dir.lib')) === false) {
        LogFacade::error("Error #" . $errno . ":" . $errstr . " in " . $errfile . " at line " . $errline);
    }
}

function fn_feature_has_size_chart($feature_id)
{
    return in_array($feature_id, array(BRAND_FEATURE_ID, SHOES_GENDER_FEATURE_ID, CLOTHES_GENDER_FEATURE_ID));
}

function fn_get_memcached_status()
{
    return (class_exists('Memcached')) ? true : false;
}

function fn_add_product_features($pid, $data)
{
    $addition = array();
    foreach ($data as $v) {
        $v['product_id'] = $pid;
        $addition[] = $v;
    }
    FeaturesCache::updateProductFeaturesValue($pid, $addition);
}

function fn_update_feature_value_int($variant_id, $value_int, $lang_code)
{
    $tmp = db_get_row("SELECT feature_id, value_int FROM ?:product_features_values WHERE variant_id = ?i AND lang_code = ?s", $variant_id, $lang_code);
    if (!empty($tmp['feature_id']) && !empty($tmp['value_int'])) {
        FeaturesCache::updateFeatureValueInt($tmp['feature_id'], $tmp['value_int'], $value_int, $lang_code);
    }
}

function fn_change_feature_category($feature_id, $new_categories)
{
    $product_ids = db_get_fields("SELECT product_id FROM ?:products_categories WHERE link_type = 'M' AND category_id IN (?a)", $new_categories);
    if (!empty($product_ids)) {
        $params = array(
            'delete' => array('not_product_id' => $product_ids),
            'condition' => array('feature_id' => array($feature_id))
        );
        FeaturesCache::clearoutFeatures($params);
    }
}

function fn_get_block_categories($category_id)
{
    $categories = array();
    $_params = array (
        'category_id' => $category_id,
        'visible' => true,
        'get_images' => true,
        'limit' => 3
    );
    list($subcategories, ) = fn_get_categories($_params, CART_LANGUAGE);
    if (!empty($subcategories)) {
        if (fn_display_subheaders($category_id)) {
            $subcategory = reset($subcategories);
            $params = array (
                'category_id' => $subcategory['category_id'],
                'visible' => true,
                'get_images' => true,
                'limit' => 3
            );

            list($categories, ) = fn_get_categories($params, CART_LANGUAGE);
        } else {
            $categories = $subcategories;
        }
    }
    
    return $categories;
}

function fn_get_brands()
{
    list($variants) = fn_get_product_feature_variants(array(
        'feature_id' => BRAND_FEATURE_ID
    ));
    
    return $variants;
}

function fn_read_title($title)
{
    $brand = !empty($_SESSION['product_features'][BRAND_FEATURE_ID]['variant_name']) ? $_SESSION['product_features'][BRAND_FEATURE_ID]['variant_name'] : __("this_brand");
    return str_replace(array('[brand]'), array($brand), $title);
}

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

function fn_display_subheaders($category_id)
{
    return in_array($category_id, array(RACKETS_CATEGORY_ID, APPAREL_CATEGORY_ID, SHOES_CATEGORY_ID, STRINGS_CATEGORY_ID, BAGS_CATEGORY_ID));
}

function fn_get_product_cross_sales($params)
{
    $result = array();
    if (!empty($_SESSION['product_features'][TYPE_FEATURE_ID])) {
        if ($_SESSION['product_features'][TYPE_FEATURE_ID]['variant_id'] == KIDS_RACKET_FV_ID) {
        } else {
            if (!empty($_SESSION['product_features'][R_STRINGS_FEATURE_ID]['value']) && $_SESSION['product_features'][R_STRINGS_FEATURE_ID]['value'] == 'N') {
                $params_array = array('V' . NATURAL_GUT_STRINGS_FV_ID, 'V' . NYLON_STRINGS_FV_ID, 'V' . POLYESTER_STRINGS_FV_ID, 'V' . HYBRID_STRINGS_FV_ID);
                $_params = array (
                    'sort_by' => 'random',
                    'limit' => 1,
                    'cid' => STRINGS_CATEGORY_ID,
                    'subcats' => 'Y',
                    'amount_from' => 1
                );
                $result[] = array(
                    'title' => __('strings'),
                    'items' => fn_get_result_products($_params, 'features_hash', $params_array)
                );
            }
            $_params = array (
                'sort_by' => 'random',
                'limit' => (!empty($_SESSION['product_features'][R_STRINGS_FEATURE_ID]['value']) && $_SESSION['product_features'][R_STRINGS_FEATURE_ID]['value'] == 'N') ? 1 : 4,
                'cid' => OVERGRIPS_CATEGORY_ID,
                'subcats' => 'Y',
                'amount_from' => 1
            );
            list($prods,) = fn_get_products($_params);
            $result[] = array(
                'title' => __('overgrips'),
                'items' => $prods
            );
        }
    }
    
    return array($result, $params);
}

function fn_get_result_products($params, $key, $param_array)
{
    $result = array();
    foreach ($param_array as $val) {
        $params[$key] = $val;
        list($prods,) = fn_get_products($params);
        $result = array_merge($result, $prods);
    }
    
    return $result;
}

function fn_get_cross_sales($params)
{
    $result = array();
    if (!empty($_SESSION['cart']['products'])) {
        $objective_cat_ids = array(RACKETS_CATEGORY_ID, APPAREL_CATEGORY_ID, SHOES_CATEGORY_ID, BAGS_CATEGORY_ID, STRINGS_CATEGORY_ID, BALLS_CATEGORY_ID, TOWELS_CATEGORY_ID, OVERGRIPS_CATEGORY_ID, BASEGRIPS_CATEGORY_ID, DAMPENERS_CATEGORY_ID);
        $cat_ids = array();
        foreach ($_SESSION['cart']['products'] as $item_id => $item) {
            $cat_ids[] = $item['main_category'];
        }
        $show_cat_ids = array_diff($objective_cat_ids, $cat_ids);
        if (!empty($show_cat_ids)) {
            $limit = ceil($params['limit'] / count($show_cat_ids));
            $_params = array (
                'bestsellers' => true,
                'sales_amount_from' => 1,
                'sort_by' => 'sales_amount',
                'sort_order' => 'desc',
                'limit' => $limit,
                'subcats' => 'Y'
            );
            foreach ($show_cat_ids as $id) {
                $_params['cid'] = $id;
                list($prods,) = fn_get_products($_params);
                $result = array_merge($result, $prods);
            }
        }
        if (!empty($result)) {
            shuffle($result);
        }
    }

    return array($result, $params);
}

function fn_check_vars($description)
{
    if (preg_match_all('/\{([a-zA-Z_]*)\}/', $description, $matches)) {
        foreach ($matches[0] as $i => $vl) {
            if ($vl == '{free_shipping_cost}') {
                $description = str_replace($vl, Registry::get('addons.development.free_shipping_cost'), $description);
            }
        }
    }
    return $description;
}

function fn_render_page_blocks($description, $smarty_capture)
{
    if (preg_match_all('/\[([a-zA-Z1-9_]*)\]/', $description, $matches)) {
        $blocks = array();
        foreach ($matches[1] as $i => $name) {
            $blocks[] = !empty($smarty_capture['block_' . $name]) ? $smarty_capture['block_' . $name] : '';
        }
        $description = str_replace(
            $matches[0],
            $blocks,
            $description
        );
    }

    return $description;
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

function fn_get_product_global_margin($category_id)
{
    $path = db_get_field("SELECT id_path FROM ?:categories WHERE category_id = ?i", $category_id);
    $result = Registry::get('addons.development.global_product_margin');
    if (!empty($path)) {
        $cat_ids = explode('/', $path);
        $cat_margin = db_get_hash_single_array("SELECT margin, category_id FROM ?:categories WHERE override_margin = 'Y' AND category_id IN (?n)", array('category_id', 'margin'), $cat_ids);
        foreach (array_reverse($cat_ids) as $i => $cat_id) {
            if (!empty($cat_margin[$cat_id])) {
                $result = $cat_margin[$cat_id];
                break;
            }
        }
    }
    
    return $result;
}

function fn_round_price($price)
{
    return ceil(ceil($price) / 10) * 10;
}

function fn_calculate_base_price($product_data)
{
    $net_cost = $product_data['net_cost'] * Registry::get('currencies.' . $product_data['net_currency_code'] . '.coefficient');
    $base_price = $net_cost + $net_cost * $product_data['margin'] / 100;
    
    return $base_price;
}

function fn_update_prices()
{
    $products = db_get_hash_array("SELECT product_id, margin, net_cost, net_currency_code FROM ?:products WHERE auto_price = 'Y' AND margin > 0 AND net_cost > 0 AND net_currency_code != ''", 'product_id');
    $result = array();
    if (!empty($products)) {
        $prices = db_get_hash_multi_array("SELECT * FROM ?:product_prices WHERE product_id IN (?n) AND lower_limit IN ('1', '2')", array('product_id', 'lower_limit'), array_keys($products));
        if (!empty($prices)) {
            foreach ($prices as $product_id => $prs) {
                if (!empty($prs)) {
                    $base_price = fn_calculate_base_price($products[$product_id]);
                    foreach ($prs as $i => $p_data) {
                        if ($p_data['lower_limit'] == 1 || ($p_data['lower_limit'] > 1 && $p_data['percentage_discount'] > 0)) {
                            $prices[$product_id][$i]['price'] = fn_round_price($base_price);
                        } else {
                            $prices[$product_id][$i]['price'] = fn_round_price($base_price - $base_price * RACKETS_QTY_DSC_PRC / 100);
                        }
                        $result[] = $prices[$product_id][$i];
                    }
                }
            }
        }
    }
    
    if (!empty($result)) {
        db_query("REPLACE INTO ?:product_prices ?m", $result);
    }
}

function fn_get_categories_types($category_ids)
{
    $category_ids = is_array($category_ids) ? $category_ids : array($category_ids);
    $paths = db_get_hash_single_array("SELECT id_path, category_id FROM ?:categories WHERE category_id IN (?n)", array("category_id", "id_path"), $category_ids);
    $result = array();
    foreach ($paths as $i => $path) {
        $result[$i] = fn_identify_category_type($path);
    }
    return $result;
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
        } elseif (in_array(STRINGS_CATEGORY_ID, $cats)) {
            $type = 'ST';
        } elseif (in_array(ACCESSORIES_CATEGORY_ID, $cats)) {
            if (in_array(BALLS_CATEGORY_ID, $cats)) {
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

function fn_get_block_players()
{
    $players = array();
    $_params = array (
        'gender' => 'M',
        'limit' => 3
    );
    list($players['male'], ) = fn_get_players($_params);
    $_params = array (
        'gender' => 'F',
        'limit' => 3
    );
    list($players['female'], ) = fn_get_players($_params);

    return $players;
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
