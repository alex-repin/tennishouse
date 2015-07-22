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

use Tygh\Memcache;
use Tygh\Registry;
use Tygh\LogFacade;
use Tygh\Http;
use Tygh\FeaturesCache;
use Tygh\Menu;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

function fn_process_php_errors($errno, $errstr, $errfile, $errline, $errcontext)
{
//     $dirs = Registry::get('config.dir');
//     if (strpos($errfile, $dirs['var']) === false && strpos($errfile, $dirs['lib']) === false) {
//         LogFacade::error("Error #" . $errno . ":" . $errstr . " in " . $errfile . " at line " . $errline);
//     }
}

function fn_get_menu_items_th($value, $block, $block_scheme)
{
    $menu_items = array();

    if (!empty($block['content']['menu']) && Menu::getStatus($block['content']['menu']) == 'A') {
        $params = array(
            'section' => 'A',
            'get_params' => true,
            'icon_name' => '',
            'multi_level' => true,
            'use_localization' => true,
            'status' => 'A',
            'generate_levels' => true,
            'request' => array(
                'menu_id' => $block['content']['menu'],
            )
        );

        $menu_items = fn_top_menu_form(fn_get_static_data($params));
        fn_dropdown_appearance_cut_second_third_levels($menu_items, 'subitems', $block['properties']);
        
        foreach ($menu_items as $i => $item) {
            if (!empty($item['param_3'])) {
                list($type, $id, $use_name) = fn_explode(':', $item['param_3']);
                if ($type == 'P') {
                    $menu_items[$i]['show_more'] = true;
                    $menu_items[$i]['show_more_text'] = __('see_all_players');
                    foreach ($item['subitems'] as $j => $group) {
                        $menu_items[$i]['subitems'][$j]['show_more'] = false;
                    }
                } elseif ($type == 'C') {
                    foreach ($item['subitems'] as $j => $group) {
                        if (!empty($group['subitems'])) {
                            $menu_items[$i]['subitems'][$j]['expand'] = false;
                            foreach ($group['subitems'] as $k => $item) {
                                if (fn_gender_match($item['code'])) {
                                    $menu_items[$i]['subitems'][$j]['expand'] = $k;
                                    break;
                                }
                                if ($item['is_virtual'] == 'Y' && !empty($item['parent_id'])) {
                                    $menu_items[$i]['subitems'][$j]['subitems'][$k]['href'] = 'categories.view?category_id=' . $item['parent_id'];
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    return $menu_items;
}

function fn_get_block_rss_news($value, $block, $block_scheme)
{
    return fn_get_rss_news(array('rss_feed_link' => $block['properties']['rss_feed_link']));
}

function fn_get_rss_news($params)
{
    $news_feed = array();
    if (!empty($params['rss_feed_link'])) {
        $extra = array(
            'request_timeout' => 2
        );
        $response = Http::get($params['rss_feed_link'], array(), $extra);
        if (!empty($response)) {
            libxml_use_internal_errors(true);
            $xml = @simplexml_load_string($response);
            if ($xml !== false) {
                foreach ($xml->channel->item as $item) {
                    $news = array();
                    $news['title'] = (string) $item->title;
                    $news['link'] = (string) $item->link;
                    $news['description'] = (string) $item->description;
                    $news['image'] = (string) $item->image->url;
                    $aResult = strptime((string) $item->pubDate, '%a, %d %b %Y %T %z');
                    $news['timestamp'] = mktime($aResult['tm_hour'], $aResult['tm_min'], $aResult['tm_sec'], $aResult['tm_mon'] + 1, $aResult['tm_mday'], $aResult['tm_year'] + 1900);
                    $news_date = date('Y-m-d', $news['timestamp']);
                    $today = date('Y-m-d');
                    $yesterday = date('Y-m-d', strtotime('yesterday'));
                    if ($news_date == $today) {
                        $news['today'] = true;
                    } elseif ($news_date == $yesterday) {
                        $news['yesterday'] = true;
                    }
                    $news_feed[] = $news;
                    if (count($news_feed) >= 10) {
                        break;
                    }
                }
            }
        }
    }
    
    return $news_feed;
}

function fn_update_product_tracking($product_id)
{
    $options_left = db_get_fields("SELECT po.option_id, go.option_id FROM ?:product_options AS po LEFT JOIN ?:product_global_option_links AS go ON go.option_id = po.option_id WHERE (po.product_id = ?i OR go.product_id = ?i) AND po.option_type IN ('S','R','C') AND po.inventory = 'Y'", $product_id, $product_id);
    if (empty($options_left)) {
        $tracking = db_get_field("SELECT tracking FROM ?:products WHERE product_id = ?i", $product_id);
        if ($tracking == 'O') {
            db_query("UPDATE ?:products SET tracking = 'B' WHERE product_id = ?i", $product_id);
        }
    } else {
        db_query("UPDATE ?:products SET tracking = 'O' WHERE product_id = ?i", $product_id);
    }
}

function fn_filter_categroies(&$categories)
{
    if (!empty($categories)) {
        foreach ($categories as $i => $cat) {
            if (empty($cat['subcategories']) && $cat['product_count'] == 0 && empty($cat['has_children'])) {
                unset($categories[$i]);
            } elseif (!empty($cat['subcategories'])) {
                fn_filter_categroies($categories[$i]['subcategories']);
            }
        }
    }
}

function fn_set_store_gender_mode($mode)
{
    $gender_mode = fn_get_store_gender_mode();

    if (empty($gender_mode) || (!(in_array($gender_mode, array('B', 'G')) && $mode == 'K') && !(in_array($gender_mode, array('M', 'F')) && $mode == 'A'))) {
        fn_set_session_data('gender_mode', $mode);
    }
}

function fn_format_categorization(&$category_data, $ctz_data, $type)
{
    if (empty($category_data[$type])) {
        foreach (array_reverse($category_data['cat_ids']) as $i => $cat_id) {
            if (!empty($ctz_data[$cat_id][$type])) {
                $category_data[$type] = $ctz_data[$cat_id][$type];
                break;
            }
        }
    }
}

function fn_generate_features_cash()
{
    FeaturesCache::generate(CART_LANGUAGE);
    return array(true, array());
}

function fn_update_rankings($ids = array())
{
    if (!empty($ids)) {
        $players = db_get_array("SELECT player_id, data_link, gender FROM ?:players WHERE data_link != '' AND player_id IN (?n)", $ids);
    } else {
        $players = db_get_array("SELECT player_id, data_link, gender FROM ?:players WHERE data_link != ''");
    }
    $update = array();
    if (!empty($players)) {
        foreach ($players as $i => $player) {
            $result = Http::get($player['data_link']);
            if ($result) {
                //$player_data = array('data' => array());
                if ($player['gender'] == 'M') {
                    if (preg_match('/<div class="player-ranking-position">.*?(\d+).*?<\/div>/', preg_replace('/[\r\n]/', '', $result), $match)) {
                        $player_data = array(
                            'player_id' => $player['player_id'],
                            'ranking' => isset($match['1']) ? $match['1'] : 'n/a'
                        );
                    }
                    if (preg_match('/id="playersStatsTable".*?>(.*?)<\/table>/', preg_replace('/[\r\n\t]/', '', $result), $match)) {
                        if (preg_match('/>Career<\/div>(.*)/', $match[1], $match)) {
                            if (preg_match_all('/>(\d+)</', $match[1], $_match)) {
                                $player_data['titles'] = $player_data['data']['career_titles'] = isset($_match[1][1]) ? $_match[1][1] : 'n/a';
                            }
                            if (preg_match('/>\$([\d,]+)</', $match[1], $_match)) {
                                $player_data['data']['career_prize'] = isset($_match[1]) ? intval(str_replace(',', '', $_match[1])) : 'n/a';
                            }
                            if (preg_match('/>(\d+)-(\d+)</', $match[1], $_match)) {
                                $player_data['data']['career_won'] = isset($_match[1]) ? $_match[1] : 'n/a';
                                $player_data['data']['career_lost'] = isset($_match[2]) ? $_match[2] : 'n/a';
                            }
                        }
                    }
                    $update[] = $player_data;
                } else {
                    if (preg_match('/<div class="box ranking">.*?>(\d+)<.*?<\/div>/', preg_replace('/[\r\n]/', '', $result), $match)) {
                        $player_data = array(
                            'player_id' => $player['player_id'],
                            'ranking' => isset($match['1']) ? $match['1'] : 'n/a'
                        );
                    }
                    if (preg_match('/<tr>.*?<td>WTA Singles Titles<\/td>(.*?)<\/tr>/', preg_replace('/[\r\n]/', '', $result), $match)) {
                        if (preg_match_all('/<td>(.*?)<\/td>/', $match[1], $match)) {
                            $player_data['titles'] = $player_data['data']['career_titles'] = isset($match[1][1]) ? $match[1][1] : 'n/a';
                        }
                    }
                    if (preg_match('/<tr>.*?<td>Prize Money<\/td>(.*?)<\/tr>/', preg_replace('/[\r\n]/', '', $result), $match)) {
                        if (preg_match_all('/>\$(.*?)</', $match[1], $match)) {
                            $player_data['data']['career_prize'] = isset($match[1][1]) ? intval(str_replace(',', '', $match[1][1])) : 'n/a';
                        }
                    }
                    if (preg_match('/<tr>.*?<td>W\/L - Singles<\/td>(.*?)<\/tr>/', preg_replace('/[\r\n]/', '', $result), $match)) {
                        if (preg_match_all('/<td>(.*?) - (.*?)<\/td>/', $match[1], $match)) {
                            $player_data['data']['career_won'] = isset($match[1][1]) ? $match[1][1] : 'n/a';
                            $player_data['data']['career_lost'] = isset($match[2][1]) ? $match[2][1] : 'n/a';
                        }
                    }
                    $update[] = $player_data;
                }
            }
        }
    }
    $errors = array();
    if (!empty($update)) {
        foreach ($update as $i => $_dt) {
            if (fn_check_player_data($_dt)) {
                if (!empty($_dt['data'])) {
                    $_dt['data'] = serialize($_dt['data']);
                }
                db_query("UPDATE ?:players SET ?u WHERE player_id = ?i", $_dt, $_dt['player_id']);
            } else {
                $errors[] = $_dt;
            }
        }
        if (empty($errors) && count($players) == count($update)) {
            //fn_set_notification('N', __('notice'), __('rankings_updated_successfully', array('[total]' => count($players), '[updated]' => count($update))));
        } else {
            return array(false, $errors);
        }
    }
    return array(true, $errors);
}

function fn_check_player_data($player_data)
{
    $scheme = array(
        'player_id' => 1,
        'ranking' => 1,
        'titles' => 1,
        'data' => array(
            'career_titles' => 1,
            'career_prize' => 1,
            'career_won' => 1,
            'career_lost' => 1
        )
        
    );
    
    foreach ($scheme as $key => $value) {
        if (!isset($player_data[$key]) || $player_data[$key] == 'n/a') {
            return false;
        } elseif (is_array($value)) {
            foreach ($value as $_key => $_value) {
                if (!isset($player_data[$key][$_key]) || $player_data[$key][$_key] == 'n/a') {
                    return false;
                }
            }
        }
    }
    
    return true;
}

function fn_update_rub_rate()
{
    $update_limits = array(
        'USD' => 2.5,
        'EUR' => 2.5,
    );
    $rates = fn_get_currency_exchange_rates();
    $update_prices = false;
    $errors = array();
    if ($rates) {
        foreach ($rates as $code => $rate) {
            if (!empty(Registry::get('currencies.' . $code)) && (Registry::get('currencies.' . $code . '.coefficient') < $rate || (Registry::get('currencies.' . $code . '.coefficient') > $rate && (empty($update_limits[$code]) || Registry::get('currencies.' . $code . '.coefficient') - $rate > $update_limits[$code])))) {
                $update_prices = true;
                $currency_data = array('coefficient' => $rate);
                db_query("UPDATE ?:currencies SET ?u WHERE currency_code = ?s", $currency_data, $code);
            }
        }
    }
    if ($update_prices) {
        $params = array();
        fn_init_currency($params);
        fn_update_prices();
        fn_set_notification('N', __('notice'), __('currencies_updated_successfully'));
    }
    
    return array(true, $errors);
}

function fn_update_product_exception($product_id, $product_options, $new_amount)
{
    $exist = fn_check_combination($product_options, $product_id);
    if ($new_amount < 1) {
        if (!$exist) {
            $_data = array(
                'product_id' => $product_id,
                'combination' => serialize($product_options)
            );
            db_query("INSERT INTO ?:product_options_exceptions ?e", $_data);
        }
    } else {
        if ($exist) {
            db_query("DELETE FROM ?:product_options_exceptions WHERE product_id = ?i AND combination = ?s", $product_id, serialize($product_options));
        }
    }
    fn_update_combinations($product_id);
}

function fn_update_combinations($product_id)
{
    $combinations = db_get_array("SELECT * FROM ?:product_options_inventory WHERE product_id = ?i", $product_id);
    if (!empty($combinations)) {
        $option_variants_avail = $option_variants = array();
        foreach ($combinations as $i => $combination) {
            $options_array = fn_get_product_options_by_combination($combination['combination']);
            if ($combination['amount'] < 1) {
                foreach ($options_array as $option_id => $variant_id) {
                    if (!in_array($option_id, array_keys($option_variants_avail))) {
                        $option_variants_avail[$option_id] = array();
                    }
                }
            } else {
                foreach ($options_array as $option_id => $variant_id) {
                    if (empty($option_variants_avail[$option_id]) || !in_array($variant_id, $option_variants_avail[$option_id])) {
                        $option_variants_avail[$option_id][] = $option_variants[] = $variant_id;
                    }
                }
            }
        }
        if (!empty($option_variants_avail)) {
            $features = db_get_hash_single_array("SELECT a.feature_id, option_id FROM ?:product_options AS a INNER JOIN ?:product_features ON ?:product_features.feature_id = a.feature_id WHERE option_id IN (?n)", array('option_id', 'feature_id'), array_keys($option_variants_avail));
            if (!empty($option_variants)) {
                $feature_variants = db_get_hash_single_array("SELECT feature_variant_id, variant_id FROM ?:product_option_variants WHERE variant_id IN (?n)", array('variant_id', 'feature_variant_id'), $option_variants);
            }
            $features_data = array();
            foreach ($option_variants_avail as $option_id => $variants) {
                if (!empty($features[$option_id])) {
                    $features_data[$features[$option_id]] = array();
                    if (!empty($variants)) {
                        foreach ($variants as $j => $variant_id) {
                            if (!empty($feature_variants[$variant_id])) {
                                $features_data[$features[$option_id]][] = $feature_variants[$variant_id];
                            }
                        }
                    }
                }
            }
            if (!empty($features_data)) {
                $add_new_variant = array();
                fn_update_product_features_value($product_id, $features_data, $add_new_variant, CART_LANGUAGE);
            }
        }
    }
}

function fn_update_product_exceptions($product_id, $combinations)
{
    if (!empty($combinations)) {
        $combination_options = db_get_hash_single_array("SELECT combination, combination_hash FROM ?:product_options_inventory WHERE combination_hash IN (?n)", array('combination_hash', 'combination'), array_keys($combinations));
        if (!empty($combination_options)) {
            foreach ($combination_options as $hash => $combination) {
                $options_array = fn_get_product_options_by_combination($combination);
                
                db_query("DELETE FROM ?:product_options_exceptions WHERE product_id = ?i AND combination = ?s", $product_id, serialize($options_array));
                if (!empty($combinations[$hash]) && $combinations[$hash]['amount'] < 1) {
                    $_data = array(
                        'product_id' => $product_id,
                        'combination' => serialize($options_array)
                    );
                    db_query("INSERT INTO ?:product_options_exceptions ?e", $_data);
                }
            }
        }
    }
    fn_update_combinations($product_id);
}

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

function fn_feature_has_size_chart($feature_id)
{
    return in_array($feature_id, array(BRAND_FEATURE_ID, SHOES_GENDER_FEATURE_ID, CLOTHES_GENDER_FEATURE_ID));
}

function fn_get_memcached_stats()
{
    $result = array();
    if (class_exists('Memcached')) {
        $result['status'] = true;
        $stats = Memcache::instance()->call('stats');
        $localhost = $stats['localhost:11211'];
        if (!empty($localhost['limit_maxbytes'])) {
            $result['used_prc'] = ceil($localhost['bytes'] / $localhost['limit_maxbytes'] * 100);
        }
        $result['used'] = ceil($localhost['bytes'] / 1024);
    } else {
        $result['status'] = false;
    }
    
    return $result;
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
        'limit' => 10
    );
    if (fn_display_subheaders($category_id)) {
        $_params['skip_filter'] = true;
    }
    list($subcategories, ) = fn_get_categories($_params, CART_LANGUAGE);
    if (!empty($subcategories)) {
        if (fn_display_subheaders($category_id)) {
            $subcategory = reset($subcategories);
            $params = array (
                'category_id' => $subcategory['category_id'],
                'visible' => true,
                'get_images' => true,
                'limit' => 10
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
                $params_array = array(POLYESTER_MATERIAL_CATEGORY_ID, HYBRID_MATERIAL_CATEGORY_ID, NATURAL_GUT_MATERIAL_CATEGORY_ID, NYLON_MATERIAL_CATEGORY_ID);
                $_params = array (
                    'sort_by' => 'random',
                    'limit' => 1,
                    'subcats' => 'Y',
                    'features_hash' => 'V' . TW_M_STRINGS_FV_ID,
                    'amount_from' => 1
                );
                $result[] = array(
                    'title' => __('strings'),
                    'items' => fn_get_result_products($_params, 'cid', $params_array)
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

function fn_get_same_brand_products($params)
{
    $result = array();
    $_limit = $params['limit'];
    unset($params['limit']);
    list($products, ) = fn_get_products($params);
    $ids = array();
    if (!empty($products)) {
        foreach ($products as $i => $product) {
            $ids[] = $product['product_id'];
        }
        $objective_cat_ids = array(RACKETS_CATEGORY_ID, APPAREL_CATEGORY_ID, SHOES_CATEGORY_ID, BAGS_CATEGORY_ID, STRINGS_CATEGORY_ID, ACCESSORIES_CATEGORY_ID);
        $category_path = db_get_field("SELECT id_path FROM ?:categories AS c LEFT JOIN ?:products_categories AS pc ON pc.category_id = c.category_id AND pc.link_type = 'M' LEFT JOIN ?:products AS p ON p.product_id = pc.product_id WHERE p.product_id = ?i", $params['same_brand_pid']);
        $show_cat_ids = array_diff($objective_cat_ids, explode('/', $category_path));
        if (!empty($show_cat_ids)) {
            fn_gender_categories($show_cat_ids);
            $limit = ceil($_limit / count($show_cat_ids));
            $_params = array (
//                 'sort_by' => 'bestsellers',
//                 'sort_order' => 'desc',
                'sort_by' => 'random',
                'limit' => $limit,
                'subcats' => 'Y',
                'item_ids' => implode(',', $ids)
            );
            $start = microtime();
            foreach ($show_cat_ids as $id) {
                $_params['cid'] = $id;
                list($prods,) = fn_get_products($_params);
                $result = array_merge($result, $prods);
            }
            shuffle($result);
        }
    }
    $params['limit'] = $_limit;

    return array($result, $params);
}

function fn_get_cross_sales($params)
{
    $result = array();
    if (!empty($_SESSION['cart']['products'])) {
        $objective_cat_ids = array(RACKETS_CATEGORY_ID, APPAREL_CATEGORY_ID, SHOES_CATEGORY_ID, BAGS_CATEGORY_ID, STRINGS_CATEGORY_ID, BALLS_CATEGORY_ID, OVERGRIPS_CATEGORY_ID, DAMPENERS_CATEGORY_ID);
        $show_cat_ids = array_diff($objective_cat_ids, $_SESSION['cart']['product_categories']);
        if (!empty($show_cat_ids)) {
            fn_gender_categories($show_cat_ids);
            $limit = ceil($params['limit'] / count($show_cat_ids));
            $_params = array (
//                 'sort_by' => 'bestsellers',
//                 'sort_order' => 'desc',
                'sort_by' => 'random',
                'limit' => $limit,
                'subcats' => 'Y'
            );
            $start = microtime();
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

function fn_gender_categories(&$show_cat_ids)
{
    $gender_categories = array(APPAREL_CATEGORY_ID, SHOES_CATEGORY_ID, RACKETS_CATEGORY_ID);
    $gender = fn_get_store_gender_mode();
    if (!empty($gender)) {
        foreach ($gender_categories as $i => $c_id) {
            $cat_it = array_search($c_id, $show_cat_ids);
            if ($cat_it !== false) {
                $modes = array(
                    $gender,
                    'U'
                );
                if (in_array($gender, array('B', 'G'))) {
                    $modes[] = 'K';
                }
                if ($gender == 'K') {
                    $modes[] = 'B';
                    $modes[] = 'G';
                }
                if (in_array($gender, array('M', 'F'))) {
                    $modes[] = 'A';
                }
                if ($gender == 'A') {
                    $modes[] = 'M';
                    $modes[] = 'F';
                }
                $_condition = array();
                foreach ($modes as $j => $mode) {
                    $_condition[] = db_quote("code = ?s", $mode);
                }
                $condition = "(" . implode(' OR ', $_condition) . ")";
                $gender_cids = db_get_fields("SELECT category_id FROM ?:categories WHERE $condition AND id_path LIKE ?l", $c_id . '/%');
                if (!empty($gender_cids)) {
                    $show_cat_ids[$cat_it] = $gender_cids;
                }
            }
        }
    }
}

function fn_check_vars($description)
{
    if (preg_match_all('/\{([a-zA-Z_]*)\}/', $description, $matches)) {
        foreach ($matches[0] as $i => $vl) {
            if ($vl == '{free_shipping_cost}') {
                $description = str_replace($vl, Registry::get('addons.development.free_shipping_cost'), $description);
            }
            if ($vl == '{company_phone}') {
                $description = str_replace($vl, Registry::get('settings.Company.company_phone'), $description);
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

function fn_get_product_global_margin($category_id)
{
    $path = db_get_field("SELECT id_path FROM ?:categories WHERE category_id = ?i", $category_id);
    $result = Registry::get('addons.development.global_product_margin');
    $currency = '';
    if (!empty($path)) {
        $cat_ids = explode('/', $path);
        $cat_data = db_get_hash_array("SELECT margin, category_id, net_currency_code, override_margin FROM ?:categories WHERE category_id IN (?n)", 'category_id', $cat_ids);
        foreach (array_reverse($cat_ids) as $i => $cat_id) {
            if (empty($currency)) {
                $currency = $cat_data[$cat_id]['net_currency_code'];
            }
            if ($cat_data[$cat_id]['override_margin'] == 'Y' && !empty($cat_data[$cat_id]['margin'])) {
                $result = $cat_data[$cat_id]['margin'];
                $currency = $cat_data[$cat_id]['net_currency_code'];
                break;
            }
        }
    }
    
    return array($result, $currency);
}

function fn_get_product_global_weight($product_data)
{
    $result = 0;
    $paths = db_get_hash_single_array("SELECT category_id, id_path FROM ?:categories WHERE category_id IN (?n)", array('category_id', 'id_path'), $product_data['category_ids']);
    $all_ids = array();
    $use_order = array();
    if (!empty($paths)) {
        foreach ($paths as $cat_id => $path) {
            $ids = explode('/', $path);
            foreach(array_reverse($ids) as $j => $cat_id) {
                if (empty($use_order[$j]) || !in_array($cat_id, $use_order[$j])) {
                    $use_order[$j][] = $cat_id;
                }
            }
            $all_ids = array_merge($all_ids, $ids);
        }
    }
    $weights = db_get_hash_single_array("SELECT category_id, shipping_weight FROM ?:categories WHERE category_id IN (?n)", array('category_id', 'shipping_weight'), array_unique($all_ids));
    if (!empty($use_order)) {
        foreach ($use_order as $lvl => $cat_ids) {
            foreach ($cat_ids as $i => $ct_id) {
                if (!empty(floatval($weights[$ct_id]))) {
                    $result = $weights[$ct_id];
                    break 2;
                }
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

function fn_get_product_margin(&$product)
{
    list($md, $currency) = fn_get_product_global_margin($product['main_category']);
    if (empty($product['net_currency_code']) && !empty($currency)) {
        $product['net_currency_code'] = $currency;
    }
    $error = false;
    if (!empty($md) && !empty($currency)) {
        $_md = explode(';', $md);
        if (count($_md) == 2) {
            $min_md = explode(':', $_md[0]);
            $max_md = explode(':', $_md[1]);
            if (count($min_md) == 2 && count($max_md) == 2) {
                $min_md[0] = $min_md[0] * Registry::get('currencies.' . $currency . '.coefficient');
                $max_md[0] = $max_md[0] * Registry::get('currencies.' . $currency . '.coefficient');
                $net_cost = $product['net_cost'] * Registry::get('currencies.' . $product['net_currency_code'] . '.coefficient');
                if ($net_cost <= $min_md[0]) {
                    $product['margin'] = $min_md[1];
                } elseif ($net_cost >= $max_md[0]) {
                    $product['margin'] = $max_md[1];
                } else {
                    $product['margin'] = ceil((($net_cost - $min_md[0]) * ($max_md[1] - $min_md[1]) / ($max_md[0] - $min_md[0])) + $min_md[1]);
                }
            } else {
                $error = true;
            }
        } else {
            $error = true;
        }
    } else {
        $error = true;
    }
    if ($error) {
        fn_set_notification('E', __('error'), __('error_incorrect_margin_data'));
    }
}

function fn_process_update_prices($products)
{
    $result = array();
    if (!empty($products)) {
        $prices = db_get_hash_multi_array("SELECT * FROM ?:product_prices WHERE product_id IN (?n) AND lower_limit IN ('1', '2')", array('product_id', 'lower_limit'), array_keys($products));
        if (!empty($prices)) {
            foreach ($prices as $product_id => $prs) {
                if (!empty($prs)) {
                    if (empty($products[$product_id]['margin']) || $products[$product_id]['margin'] == 0) {
                        fn_get_product_margin($products[$product_id]);
                        if ($products[$product_id]['margin'] > 0) {
                            db_query("UPDATE ?:products SET margin = ?d, net_currency_code = ?s WHERE product_id = ?i", $products[$product_id]['margin'], $products[$product_id]['net_currency_code'], $product_id);
                        }
                    }
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
    
    return $result;
}

function fn_update_prices()
{
    $products = db_get_hash_array("SELECT prods.product_id, prods.margin, prods.net_cost, prods.net_currency_code, prods.auto_price, cats.category_id AS main_category FROM ?:products AS prods LEFT JOIN ?:products_categories AS cats ON prods.product_id = cats.product_id AND cats.link_type = 'M' WHERE prods.auto_price = 'Y' AND prods.net_cost > 0", 'product_id');
    $result = fn_process_update_prices($products);
    
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
        $result[$i] = fn_identify_type_category_id($path);
    }
    return $result;
}

function fn_identify_category_type($path)
{
    return fn_get_category_type(fn_identify_type_category_id($path));
}
function fn_get_category_type($category_id)
{
    $types = array(
        RACKETS_CATEGORY_ID => 'R',
        APPAREL_CATEGORY_ID => 'A',
        SHOES_CATEGORY_ID => 'S',
        BAGS_CATEGORY_ID => 'B',
        SPORTS_NUTRITION_CATEGORY_ID => 'N',
        STRINGS_CATEGORY_ID => 'ST',
        ACCESSORIES_CATEGORY_ID => 'C',
        BALLS_CATEGORY_ID => 'BL',
        OVERGRIPS_CATEGORY_ID => 'OG',
        BASEGRIPS_CATEGORY_ID => 'BG',
        DAMPENERS_CATEGORY_ID => 'DP',
    );
    
    return !empty($types[$category_id]) ? $types[$category_id] : '';
}

function fn_identify_type_category_id($path)
{
    $type = '';
    if (!empty($path)) {
        $cats = explode('/', $path);
        if (in_array(RACKETS_CATEGORY_ID, $cats)) {
            $type = RACKETS_CATEGORY_ID;
        } elseif (in_array(APPAREL_CATEGORY_ID, $cats)) {
            $type = APPAREL_CATEGORY_ID;
        } elseif (in_array(SHOES_CATEGORY_ID, $cats)) {
            $type = SHOES_CATEGORY_ID;
        } elseif (in_array(BAGS_CATEGORY_ID, $cats)) {
            $type = BAGS_CATEGORY_ID;
        } elseif (in_array(SPORTS_NUTRITION_CATEGORY_ID, $cats)) {
            $type = SPORTS_NUTRITION_CATEGORY_ID;
        } elseif (in_array(STRINGS_CATEGORY_ID, $cats)) {
            $type = STRINGS_CATEGORY_ID;
        } elseif (in_array(ACCESSORIES_CATEGORY_ID, $cats)) {
            if (in_array(BALLS_CATEGORY_ID, $cats)) {
                $type = BALLS_CATEGORY_ID;
            } elseif (in_array(OVERGRIPS_CATEGORY_ID, $cats)) {
                $type = OVERGRIPS_CATEGORY_ID;
            } elseif (in_array(BASEGRIPS_CATEGORY_ID, $cats)) {
                $type = BASEGRIPS_CATEGORY_ID;
            } elseif (in_array(DAMPENERS_CATEGORY_ID, $cats)) {
                $type = DAMPENERS_CATEGORY_ID;
            } else {
                $type = ACCESSORIES_CATEGORY_ID;
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
            if (!empty($insertKey)) {
                $newArray[$insertKey] = $insertValue;
            } else {
                $newArray[] = $insertValue;
            }
            $inserted = true;
        }

        if (!empty($insertKey)) {
            $newArray[ $key ] = $value;
        } else {
            $newArray[] = $value;
        }

    }

    return $newArray;

}

function fn_get_player_data($player_id)
{
    $field_list = "?:players.*";

    fn_set_hook('get_player_data', $player_id, $field_list, $join, $condition);
    
    $player_data = db_get_row("SELECT $field_list FROM ?:players LEFT JOIN ?:players_gear ON ?:players.player_id = ?:players_gear.player_id ?p WHERE ?:players.player_id = ?i  ?p", $join, $player_id, $condition);

    if (!empty($player_data)) {
        if (!empty($player_data['website'])) {
            $player_data['website'] = (strpos($player_data['website'], 'http://') === false) ? ('http://' . $player_data['website']) : $player_data['website'];
        }
        $player_data['main_pair'] = fn_get_image_pairs($player_id, 'player', 'M', true, true);
        $player_data['bg_image'] = fn_get_image_pairs($player_id, 'player', 'B', false, true);
        $player_data['gear'] = db_get_fields("SELECT product_id FROM ?:players_gear WHERE player_id = ?i", $player_id);
        $player_data['data'] = unserialize($player_data['data']);
    }

    fn_set_hook('get_player_data_post', $player_data);

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

    if (AREA == 'C') {
        $condition .= db_quote(' AND ?:players_gear.product_id IS NOT NULL');
    }
    
    $limit = '';

    fn_set_hook('get_players', $params, $join, $condition, $fields);
    
    if (!empty($params['limit'])) {
        $limit = db_quote(' LIMIT 0, ?i', $params['limit']);
    }

    $players = db_get_hash_array('SELECT ' . implode(',', $fields) . " FROM ?:players ?p WHERE 1 ?p GROUP BY ?:players.player_id ORDER BY ?:players.ranking ASC ?p", 'player_id', $join, $condition, $limit);

    if (empty($players)) {
        return array(array(), $params);
    }

    if (empty($params['plain'])) {
        $players_images = fn_get_image_pairs(array_keys($players), 'player', 'M', true, false);
        foreach ($players as $k => $v) {
            if (!empty($players_images[$v['player_id']])) {
                $players[$k]['main_pair'] = reset($players_images[$v['player_id']]);
            }
            $players[$k]['gear'] = explode(',', $players[$k]['gear']);
        }
    }

    fn_set_hook('get_players_post', $players, $params);
    
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
    
    fn_set_hook('delete_player', $player_id);
    
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
    
    $variant_data = array();
    if (isset($player_data['player'])) {
        $variant_data['variant'] = $player_data['player'];
    }

    // create new player
    if (empty($player_id)) {

        $create = true;

        $variant_data['variant_id'] = $_data['feature_variant_id'] = fn_update_product_feature_variant(PLAYER_FEATURE_ID, 'M', $variant_data);

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
        $to_update = array_merge($existing_gear, $_data['gear']);

        if (!empty($to_delete)) {
            db_query("DELETE FROM ?:players_gear WHERE product_id IN (?n) AND player_id = ?i", $to_delete, $player_id);
            db_query("DELETE FROM ?:product_features_values WHERE feature_id = ?i AND product_id IN (?n)", PLAYER_FEATURE_ID, $to_delete);
        }
        $to_add = array_diff($_data['gear'], $existing_gear);

        if (!empty($to_add)) {
            foreach ($to_add as $i => $gr) {
                $__data = array(
                    'player_id' => $player_id,
                    'product_id' => $gr
                );
                db_query("REPLACE INTO ?:players_gear ?e", $__data);
                $i_data = array(
                    'feature_id' => PLAYER_FEATURE_ID,
                    'product_id' => $gr,
                    'variant_id' => $variant_data['variant_id'],
                    'lang_code' => DESCR_SL
                );
                db_query("REPLACE INTO ?:product_features_values ?e", $i_data);
            }
        }
        
        if (!empty($to_update)) {
            foreach ($to_update as $i => $pr_id) {
                $features = db_get_array("SELECT * FROM ?:product_features_values WHERE product_id = ?i", $pr_id);
                FeaturesCache::updateProductFeaturesValue($pr_id, $features, DESCR_SL);
            }
        }
    }
    
    fn_set_hook('update_player_post', $_data, $player_id);
    
    return $player_id;

}
