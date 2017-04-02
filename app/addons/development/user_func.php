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
use Tygh\Shippings\Shippings;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

function fn_is_icon_feature($feature_id)
{
    return (in_array($feature_id, array(R_WEIGHT_FEATURE_ID, R_HEADSIZE_FEATURE_ID))) ? true : false;
}

function fn_check_category_discussion($id_path)
{
    $enable_discussion = array(RACKETS_CATEGORY_ID, BALLS_CATEGORY_ID, STRINGS_CATEGORY_ID, SHOES_CATEGORY_ID, OVERGRIPS_CATEGORY_ID, BALL_MACHINE_CATEGORY_ID, STR_MACHINE_CATEGORY_ID);
    $disable_discussion = array(BALL_MACHINE_ACC_CATEGORY_ID);
    if (!empty(array_intersect($id_path, $enable_discussion)) && empty(array_intersect($id_path, $disable_discussion))) {
        return 'B';
    } else {
        return 'D';
    }
}

function fn_review_reward_available($user_id)
{
    if (empty($user_id)) {
        return false;
    }
    $settings = Registry::get('addons.development');
    $now = getdate(TIME);
    $limit = db_get_field("SELECT COUNT(*) FROM ?:discussion_posts WHERE user_id = ?i AND is_rewarded = 'Y' AND timestamp >= ?i", $user_id, mktime(0, 0, 0, $now['mon'] - $settings['product_reviews_time_limit'], $now['mday'], $now['year']));
    
    return ($limit < $settings['product_reviews_number_limit']) ? true : false;
}

function fn_get_big_cities()
{
    return unserialize(BIG_CITIES);
}

function fn_get_approximate_shipping($location)
{
    $priority = array(COURIER_SH_ID, SDEK_STOCK_SH_ID, SDEK_DOOR_SH_ID, RU_POST_SH_ID, EMS_SH_ID);
    $shipping_time = 0;
    $group = array(
        'package_info' => array(
            'W' => 1,
            'C' => 5000,
            'I' => 1,
            'origination' => array(
                'city' => Registry::get('settings.Company.company_city'),
                'country' => Registry::get('settings.Company.company_country'),
                'state' => Registry::get('settings.Company.company_state'),
            ),
            'location' => array(
                'city' => $location['city'],
                'country' => $location['country'],
                'state' => $location['state'],
            )
        )
    );
    $shippings = array();
    $shippings_group = Shippings::getShippingsList($group);
    foreach ($shippings_group as $shipping_id => $shipping) {

        $_shipping = $shipping;
        $_shipping['package_info'] = $group['package_info'];
        $_shipping['keys'] = array(
            'shipping_id' => $shipping_id,
        );
        $shippings[] = $_shipping;

        $shipping['rate'] = 0;
    }

    $rates = Shippings::calculateRates($shippings);
    $est_ship = array();
    
    if (!empty($rates)) {
        foreach ($rates as $i => $sh_r) {
            if ($sh_r['price'] !== false && !empty($sh_r['delivery_time']) && empty($sh_r['error'])) {
                $est_ship[$sh_r['keys']['shipping_id']] = preg_replace('/[^\-0-9]/', '', $sh_r['delivery_time']);
            }
        }
        if (!empty($est_ship)) {
            foreach ($priority as $k => $sh_id) {
                if (!empty($est_ship[$sh_id])) {
                    return $est_ship[$sh_id];
                }
            }
            return reset($est_ship);
        }
    }
    
    return false;
}

function fn_get_similar_category_products($params)
{
    $result = array();
    if (!empty($_SESSION['product_category'])) {
        $_params = array (
            'cid' => $_SESSION['product_category'],
            'subcats' => 'Y',
            'sort_by' => 'popularity',
            'limit' => $params['limit']
        );
        $result = fn_get_products($_params);
    }
    if (empty($result[0]) && !empty($_SESSION['main_product_category'])) {
        $_params = array (
            'cid' => $_SESSION['main_product_category'],
            'subcats' => 'Y',
            'sort_by' => 'popularity',
            'limit' => $params['limit']
        );
        $result = fn_get_products($_params);
    }
    
    return $result;
}

function fn_format_submenu(&$menu_items, $display_subheaders = true)
{
    if (!empty($menu_items)) {
        if (count($menu_items) == 1) {
            $elm_tmp = reset($menu_items);
            if ($elm_tmp['is_virtual'] == 'Y' && !empty($elm_tmp['subitems'])) {
                $menu_items = $elm_tmp['subitems'];
            } elseif (empty($elm_tmp['subitems'])) {
                $menu_items = array();
            }
        }
        foreach ($menu_items as $j => $item) {
            $display_subheaders = ($item['level'] == 1) ? fn_display_subheaders($item['object_id']) : $display_subheaders;
            if ($item['level'] == 2 && !$display_subheaders && !empty($item['subitems'])) {
                unset($menu_items[$j]['subitems']);
            }
            if (!empty($item['subitems'])) {
                $menu_items[$j]['expand'] = false;
                if ($item['is_virtual'] == 'Y' && !empty($item['parent_id'])) {
                    $menu_items[$j]['href'] = 'categories.view?category_id=' . $item['parent_id'];
                }
                fn_format_submenu($menu_items[$j]['subitems'], $display_subheaders);
            }
        }
    }
}

function fn_get_catalog_panel_categoies()
{
    $params = array(
        'param_3' => 'C:0:N'
    );
    $menu = fn_top_menu_form(array($params));
    $menu_items = $menu[0]['subitems'];
    fn_format_submenu($menu_items);
    return $menu_items;
}

function fn_get_catalog_panel_pages()
{
//     $params = array(
//         'param_3' => 'A:53:Y'
//     );
//     $menu_items = array();
//     $players = array(
//         'item' => __("players"),
//         'href' => 'players.list',
//     );
//     $menu_items[] = $players;
//     $kb_items = fn_top_menu_form(array($params));
//     $menu_items[] = reset($kb_items);
//     $reviews = array(
//         'item' => __("players"),
//         'href' => 'players.list',
//     );
    $block['content']['menu'] = 2;
    $menu_items = fn_get_menu_items_th(true, $block, true);
    foreach ($menu_items as $i => $m_item) {
        if (in_array($m_item['id_path'], array(152, 153))) {
            unset($menu_items[$i]);
        }
        if ($m_item['id_path'] == 158) {
            unset($menu_items[$i]['subitems']);
        }
    }
    $racket_finder = array(
        'param_id' => RACKET_FINDER_MENU_ITEM_ID,
        'status' => 'A',
        'active' => 1,
        'item' => __('find_tennis_racket_menu'),
        'href' => 'racket_finder.view'
    );
    $menu_items = fn_insert_before_key($menu_items, LCENTER_MENU_ITEM_ID, RACKET_FINDER_MENU_ITEM_ID, $racket_finder);
    return $menu_items;
}

function fn_process_php_errors($errno, $errstr, $errfile, $errline, $errcontext)
{
    if (strpos($errfile, DIR_ROOT . '/var/') === false && strpos($errfile, DIR_ROOT . '/app/lib/') === false) {
        LogFacade::error("Error #" . $errno . ":" . $errstr . " in " . $errfile . " at line " . $errline);
    }
}

function fn_get_discounted_products($params)
{
    list($products, $search) = fn_get_products($params);
    fn_gather_additional_products_data($products, array(
        'get_icon' => false,
        'get_detailed' => false,
        'get_additional' => false,
        'get_options' => false,
        'get_discounts' => true,
        'get_features' => false,
        'get_title_features' => false,
        'allow_duplication' => false
    ));
    $result = array();
    foreach ($products as $i => $product) {
        if ($product['base_price'] > $product['price']) {
            $result[] = $product;
        }
        if (count($result) >= $params['products_limit']) {
            break;
        }
    }
    
    return array($result, $params);
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
        $block['properties'] = !empty($block['properties']) ? $block['properties'] : array();
        fn_dropdown_appearance_cut_second_third_levels($menu_items, 'subitems', $block['properties']);
        
        foreach ($menu_items as $i => $item) {
            if (!empty($item['param_3'])) {
                list($type, $id, $use_name) = fn_explode(':', $item['param_3']);
                $menu_items[$i]['type'] = $type;
                if ($type == 'P') {
                    $menu_items[$i]['show_more'] = true;
                    $menu_items[$i]['show_more_text'] = __('see_all_players');
                    foreach ($item['subitems'] as $j => $group) {
                        $menu_items[$i]['subitems'][$j]['show_more'] = false;
                    }
                } elseif ($type == 'C') {
                    fn_format_submenu($menu_items[$i]['subitems']);
                    foreach ($menu_items[$i]['subitems'] as $j => $group) {
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

function fn_get_news_feed($params)
{
    $news = array();
    if (!empty($params['player_id'])) {
        $feed_link = db_get_field("SELECT rss_link FROM ?:players WHERE player_id = ?i", $params['player_id']);
        if (!empty($feed_link)) {
            $news = fn_get_rss_news(array('rss_feed_link' => $feed_link));
        }
    } elseif (!empty($params['rss_feed_link'])) {
        $news = fn_get_rss_news(array('rss_feed_link' => $params['rss_feed_link']));
    }
    
    return array($news, $params);
}

function fn_get_rss_news($params)
{
    $news_feed = array();
    if (!empty($params['rss_feed_link'])) {
        $extra = array(
            'request_timeout' => 10
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
                    if (preg_match('/<div class="player-ranking-position">.*?(\d+).*?<\/div>/', preg_replace('/[\r\n\t]/', '', $result), $match)) {
                        if (preg_match('/>(\d+)</', $match[0], $_match)) {
                            $player_data = array(
                                'player_id' => $player['player_id'],
                                'ranking' => isset($_match['1']) ? $_match['1'] : 'n/a'
                            );
                        }
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
                    if (preg_match('/<div class="box ranking">.*?>([\d-]+)<.*?<\/div>/', preg_replace('/[\r\n]/', '', $result), $match)) {
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
        } elseif (!empty($errors)) {
            $ids = '';
            foreach ($errors as $i => $dt) {
                $ids = (($ids != '') ? ', ' : ' ') . $dt['player_id'];
            }
            LogFacade::error("Rankings update error ids:" . $ids);

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
        'USD' => 0,
        'EUR' => 0,
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
    $combinations = db_get_array("SELECT ?:product_options_inventory.combination, SUM(?:product_warehouses_inventory.amount) AS amount FROM ?:product_warehouses_inventory LEFT JOIN ?:product_options_inventory ON ?:product_options_inventory.combination_hash = ?:product_warehouses_inventory.combination_hash WHERE ?:product_warehouses_inventory.product_id = ?i GROUP BY ?:product_warehouses_inventory.combination_hash", $product_id);
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
    return in_array($category_id, array(STRINGS_CATEGORY_ID));
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
                    'limit' => 3,
                    'subcats' => 'Y',
                    'features_hash' => 'V' . TW_M_STRINGS_FV_ID,
                    'amount_from' => 1
                );
                $result[] = array(
                    'title' => __('strings'),
                    'items' => fn_get_result_products($_params, 'cid', $params_array)
                );
            }
//             $_params = array (
//                 'sort_by' => 'random',
//                 'limit' => (!empty($_SESSION['product_features'][R_STRINGS_FEATURE_ID]['value']) && $_SESSION['product_features'][R_STRINGS_FEATURE_ID]['value'] == 'N') ? 1 : 4,
//                 'cid' => OVERGRIPS_CATEGORY_ID,
//                 'subcats' => 'Y',
//                 'amount_from' => 1
//             );
//             list($prods,) = fn_get_products($_params);
//             $result[] = array(
//                 'title' => __('overgrips'),
//                 'items' => $prods
//             );
        }
    }
    
    return array($result, $params);
}

function fn_get_cross_sales($params)
{
    $result = array();
    if (!empty($params['category_ids'])) {
        if (!empty($_SESSION['cart']['product_categories'])) {
            $params['category_ids'] = array_diff($params['category_ids'], $_SESSION['cart']['product_categories']);
        }
        if (!empty($params['category_ids'])) {
            $cat_params = array(
                'item_ids' => implode(',', $params['category_ids']),
                'simple' => false,
                'group_by_level' => false,
                'skip_filter' => true,
                'get_description' => true
            );
            list($categories, ) = fn_get_categories($cat_params);
            $products = array();
            $_params = array (
                'sort_by' => 'random',
                'limit' => 10,
                'subcats' => 'Y',
                'amount_from' => 1,
            );
            if (!empty($params['price_to'])) {
                $_params['price_to'] = $params['price_to'];
            }
            foreach ($params['category_ids'] as $i => $cat_id) {
                $_params['cid'] = $cat_id;
                list($products[$cat_id],) = fn_get_products($_params);
                fn_gather_additional_products_data($products[$cat_id], array(
                    'get_icon' => false,
                    'get_detailed' => true,
                    'get_additional' => false,
                    'get_options' => true,
                    'get_discounts' => true,
                    'get_features' => false,
                    'get_title_features' => false,
                    'allow_duplication' => false
                ));
            }

            foreach ($categories as $k => $c_data) {
                $categories[$k]['products'] = $products[$c_data['category_id']];
            }
            $result = $categories;
        }
    }
    
    return $result;
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
        $_products = array();
        foreach ($products as $i => $product) {
            $ids[] = $product['product_id'];
            $_products[$product['product_id']] = $product;
//             $all_ids = array();
//             foreach ($product['all_path'] as $k => $path) {
//                 $all_ids = array_merge($all_ids, explode('/', $path));
//             }
//             $products[$i]['all_path_ids'] = array_unique($all_ids);
        }
        $objective_cat_ids = array(RACKETS_CATEGORY_ID, APPAREL_CATEGORY_ID, SHOES_CATEGORY_ID, BAGS_CATEGORY_ID, STRINGS_CATEGORY_ID, BALLS_CATEGORY_ID);
        $category_path = db_get_field("SELECT id_path FROM ?:categories AS c LEFT JOIN ?:products_categories AS pc ON pc.category_id = c.category_id AND pc.link_type = 'M' LEFT JOIN ?:products AS p ON p.product_id = pc.product_id WHERE p.product_id = ?i", $params['same_brand_pid']);
        $show_cat_ids = array_diff($objective_cat_ids, explode('/', $category_path));
        if (!empty($show_cat_ids)) {
            fn_gender_categories($show_cat_ids);
            $limit = ceil($_limit / count($show_cat_ids));
            
//             $check_cats = array();
//             foreach ($show_cat_ids as $j => $cats) {
//                 $check_cats[$j] = $limit;
//             }
//             $start = microtime();
//             foreach ($products as $i => $product) {
//             fn_print_die($product, $show_cat_ids);
//                 foreach ($show_cat_ids as $h => $id) {
//                     if ($check_cats[$h] > 0) {
//                         if (!empty(array_intersect($product['all_path_ids'], $id))) {
//                             $result[] = $product;
//                             $check_cats[$h]--;
//                             if ($check_cats[$h] == 0) {
//                                 unset($show_cat_ids[$h]);
//                             }
//                         }
//                     }
//                 }
//                 if (empty($show_cat_ids)) {
//                     break;
//                 }
//             }
//         fn_print_die($start, microtime());

            $_params = array (
                'sort_by' => 'random',
                'limit' => $limit,
                'subcats' => 'Y',
                'item_ids' => implode(',', $ids)
            );
            foreach ($show_cat_ids as $id) {
                $_params['cid'] = $id;
                list($prods,) = fn_get_products($_params);
                foreach ($prods as $i => $prod) {
                    unset($_products[$prod['product_id']]);
                }
                $result = array_merge($result, $prods);
            }
        }
        if (count($result) < $_limit && !empty($_products)) {
            $result = array_merge($result, array_slice($_products, 0, $_limit - count($result)));
        }
        shuffle($result);
    }
    $params['limit'] = $_limit;

    return array($result, $params);
}

function fn_get_checkout_cross_sales($params)
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

function fn_render_captured_blocks($description, $smarty_capture)
{
    if (preg_match_all('/\[\-([a-zA-Z1-9_]*)\-\]/', $description, $matches)) {
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

function fn_get_product_global_data($product_data, $data_names)
{
    if (empty($product_data['category_ids']) && !empty($product_data['product_id'])) {
        $product_data['category_ids'] = db_get_fields("SELECT category_id FROM ?:products_categories WHERE product_id = ?i ORDER BY link_type DESC", $product_data['product_id']);
    }
    $result = array();
    if (!empty($product_data['category_ids'])) {
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
        $fields = implode(', ', $data_names);
        $data = db_get_hash_array("SELECT category_id, $fields FROM ?:categories WHERE category_id IN (?n)", 'category_id', array_unique($all_ids));
        $types = db_get_hash_single_array("SELECT COLUMN_NAME, DATA_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '?:categories'", array('COLUMN_NAME', 'DATA_TYPE'));
        if (!empty($use_order)) {
            foreach ($data_names as $j => $dt_name) {
                foreach ($use_order as $lvl => $cat_ids) {
                    foreach ($cat_ids as $i => $ct_id) {
                        if (empty($result[$dt_name]) && ((in_array($types[$dt_name], array('int', 'mediumint', 'smallint', 'tinyint', 'bigint', 'float', 'decimal', 'double', 'real', 'bit', 'boolean', 'serial')) && !empty(floatval($data[$ct_id][$dt_name]))) || ($types[$dt_name] != 'decimal' && !empty($data[$ct_id][$dt_name])))) {
                            $result[$dt_name] = $data[$ct_id][$dt_name];
                            break 2;
                        }
                    }
                }
            }
        }
    }
    foreach ($data_names as $i => $dt_name) {
        if (empty($result[$dt_name]) && !empty(Registry::get('addons.development.' . $dt_name))) {
            $result[$dt_name] = Registry::get('addons.development.' . $dt_name);
        }
    }
    
    return $result;
}

function fn_get_category_global_data($category_data, $data_names)
{
    $cat_ids = array();
    if (!empty($category_data['path'])) {
        $cat_ids = explode('/', $category_data['path']);
    }
    $result = array();
    if (!empty($cat_ids)) {
        $fields = implode(', ', $data_names);
        $data = db_get_hash_array("SELECT category_id, $fields FROM ?:categories WHERE category_id IN (?n)", 'category_id', $cat_ids);
        $types = db_get_hash_single_array("SELECT COLUMN_NAME, DATA_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '?:categories'", array('COLUMN_NAME', 'DATA_TYPE'));
        foreach ($data_names as $j => $dt_name) {
            foreach (array_reverse($cat_ids) as $i => $ct_id) {
                if (empty($result[$dt_name]) && ((in_array($types[$dt_name], array('int', 'mediumint', 'smallint', 'tinyint', 'bigint', 'float', 'decimal', 'double', 'real', 'bit', 'boolean', 'serial')) && !empty(floatval($data[$ct_id][$dt_name]))) || (!in_array($types[$dt_name], array('int', 'mediumint', 'smallint', 'tinyint', 'bigint', 'float', 'decimal', 'double', 'real', 'bit', 'boolean', 'serial')) && !empty($data[$ct_id][$dt_name])))) {
                    $result[$dt_name] = $data[$ct_id][$dt_name];
                    break;
                }
            }
        }
    }
    foreach ($data_names as $i => $dt_name) {
        if (empty($result[$dt_name]) && !empty(Registry::get('addons.development.' . $dt_name))) {
            $result[$dt_name] = Registry::get('addons.development.' . $dt_name);
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
    $error = false;
    if (!empty($product['global_margin']) && !empty($product['net_currency_code'])) {
        $_md = explode(';', $product['global_margin']);
        if (count($_md) == 2) {
            $min_md = explode(':', $_md[0]);
            $max_md = explode(':', $_md[1]);
            if (count($min_md) == 2 && count($max_md) == 2) {
                $min_md[0] = $min_md[0] * Registry::get('currencies.' . $product['global_net_currency_code'] . '.coefficient');
                $max_md[0] = $max_md[0] * Registry::get('currencies.' . $product['global_net_currency_code'] . '.coefficient');
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
                        $global_data = fn_get_product_global_data($products[$product_id], array('margin', 'net_currency_code'));
                        $products[$product_id]['global_margin'] = $global_data['margin'];
                        $products[$product_id]['global_net_currency_code'] = $global_data['net_currency_code'];
                        if (empty($products[$product_id]['net_currency_code'])) {
                            $products[$product_id]['net_currency_code'] = $global_data['net_currency_code'];
                        }
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
    
    if (!empty($result)) {
        db_query("REPLACE INTO ?:product_prices ?m", $result);
    }
}

function fn_update_prices()
{
    $products = db_get_hash_array("SELECT prods.product_id, prods.margin, prods.net_cost, prods.net_currency_code, prods.auto_price, cats.category_id AS main_category FROM ?:products AS prods LEFT JOIN ?:products_categories AS cats ON prods.product_id = cats.product_id AND cats.link_type = 'M' WHERE prods.auto_price = 'Y' AND prods.net_cost > 0 AND update_with_currencies = 'Y'", 'product_id');
    $result = fn_process_update_prices($products);
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
        BALLS_CATEGORY_ID => 'BL',
        OVERGRIPS_CATEGORY_ID => 'OG',
        BASEGRIPS_CATEGORY_ID => 'BG',
        DAMPENERS_CATEGORY_ID => 'DP',
        BALL_HOPPER_CATEGORY_ID => 'BH',
        BALL_MACHINE_CATEGORY_ID => 'BM',
        STR_MACHINE_CATEGORY_ID => 'SM',
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
        } elseif (in_array(BALLS_CATEGORY_ID, $cats)) {
            $type = BALLS_CATEGORY_ID;
        } elseif (in_array(OVERGRIPS_CATEGORY_ID, $cats)) {
            $type = OVERGRIPS_CATEGORY_ID;
        } elseif (in_array(BASEGRIPS_CATEGORY_ID, $cats)) {
            $type = BASEGRIPS_CATEGORY_ID;
        } elseif (in_array(DAMPENERS_CATEGORY_ID, $cats)) {
            $type = DAMPENERS_CATEGORY_ID;
        } elseif (in_array(STR_MACHINE_CATEGORY_ID, $cats)) {
            $type = STR_MACHINE_CATEGORY_ID;
        } elseif (in_array(BALL_HOPPER_CATEGORY_ID, $cats)) {
            $type = BALL_HOPPER_CATEGORY_ID;
        } elseif (in_array(BALL_MACHINE_CATEGORY_ID, $cats)) {
            $type = BALL_MACHINE_CATEGORY_ID;
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

    $players = db_get_hash_array('SELECT ' . implode(',', $fields) . " FROM ?:players ?p WHERE 1 ?p GROUP BY ?:players.player_id ORDER BY ?:players.ranking != '0' DESC, ?:players.ranking ASC ?p", 'player_id', $join, $condition, $limit);

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

function fn_get_technology_data($technology_id)
{
    $field_list = "?:technologies.*";

    fn_set_hook('get_technology_data', $technology_id, $field_list, $join, $condition);
    
    $technology_data = db_get_row("SELECT $field_list FROM ?:technologies LEFT JOIN ?:product_technologies ON ?:technologies.technology_id = ?:product_technologies.technology_id ?p WHERE ?:technologies.technology_id = ?i  ?p", $join, $technology_id, $condition);

    if (!empty($technology_data)) {
        $technology_data['main_pair'] = fn_get_image_pairs($technology_id, 'technology', 'M', true, true);
        $technology_data['products'] = db_get_fields("SELECT product_id FROM ?:product_technologies WHERE technology_id = ?i", $technology_id);
    }

    fn_set_hook('get_technology_data_post', $technology_data);

    return (!empty($technology_data) ? $technology_data : false);
}

function fn_delete_technology($technology_id)
{
    if (empty($technology_id)) {
        return false;
    }

    // Log technology deletion
    fn_log_event('technologies', 'delete', array(
        'technology_id' => $technology_id,
    ));

    // Deleting technology
    db_query("DELETE FROM ?:technologies WHERE technology_id = ?i", $technology_id);
    db_query("DELETE FROM ?:product_technologies WHERE technology_id = ?i", $technology_id);

    // Deleting technology images
    fn_delete_image_pairs($technology_id, 'technology');
    
    fn_set_hook('delete_technology', $technology_id);
    
    return true;
}

function fn_get_technologies($params)
{
    $fields = array (
        '?:technologies.*',
        'GROUP_CONCAT(?:product_technologies.product_id) as products'
    );

    $condition = $join = '';
    $join .= db_quote(" LEFT JOIN ?:product_technologies ON ?:product_technologies.technology_id = ?:technologies.technology_id ");

    if (!empty($params['technology'])) {
        $condition .= db_quote(" AND ?:technologies.name LIKE ?l", "%".trim($params['technology'])."%");
    }

    if (!empty($params['item_ids'])) {
        $condition .= db_quote(' AND ?:technologies.technology_id IN (?n)', explode(',', $params['item_ids']));
    }

    if (!empty($params['product_id'])) {
        $condition .= db_quote(' AND ?:product_technologies.product_id = ?i', $params['product_id']);
    }

    if (!empty($params['except_id']) && (empty($params['item_ids']) || !empty($params['item_ids']) && !in_array($params['except_id'], explode(',', $params['item_ids'])))) {
        $condition .= db_quote(' AND ?:technologies.technology_id != ?i', $params['except_id']);
    }

    if (AREA == 'C') {
        $condition .= db_quote(' AND ?:product_technologies.product_id IS NOT NULL');
    }
    
    $limit = '';

    fn_set_hook('get_technologies', $params, $join, $condition, $fields);
    
    if (!empty($params['limit'])) {
        $limit = db_quote(' LIMIT 0, ?i', $params['limit']);
    }

    $technologies = db_get_hash_array('SELECT ' . implode(',', $fields) . " FROM ?:technologies ?p WHERE 1 ?p GROUP BY ?:technologies.technology_id ORDER BY ?:technologies.name ASC ?p", 'technology_id', $join, $condition, $limit);

    if (empty($technologies)) {
        return array(array(), $params);
    }

    if (empty($params['plain'])) {
        $technologies_images = fn_get_image_pairs(array_keys($technologies), 'technology', 'M', true, false);
        foreach ($technologies as $k => $v) {
            $technologies[$k]['width'] = 80;
            $technologies[$k]['height'] = 80;
            if (!empty($technologies_images[$v['technology_id']])) {
                $technologies[$k]['main_pair'] = reset($technologies_images[$v['technology_id']]);
                $ratio = $technologies[$k]['main_pair']['icon']['image_x'] / $technologies[$k]['main_pair']['icon']['image_y'];
//                 if ($ratio > 1) {
//                     $technologies[$k]['width'] = 80;
//                     $technologies[$k]['height'] = 80;
//                     $technologies[$k]['height'] = round((100 - 50 / $ratio) / $ratio);
//                 } else {
//                     $technologies[$k]['width'] = round((100 - 50 * $ratio) * $ratio);
//                     $technologies[$k]['height'] = 80;
//                     $technologies[$k]['height'] = 80;
//                 }
            }
            $technologies[$k]['products'] = explode(',', $technologies[$k]['products']);
        }
    }

    fn_set_hook('get_technologies_post', $technologies, $params);
    
    return array($technologies, $params);
}

function fn_update_technology($technology_data, $technology_id = 0)
{
    $_data = $technology_data;

    // create new technology
    if (empty($technology_id)) {

        $create = true;

        $technology_id = db_query("INSERT INTO ?:technologies ?e", $_data);
        $existing_products = array();
        
    // update existing technology
    } else {

        $arow = db_query("UPDATE ?:technologies SET ?u WHERE technology_id = ?i", $_data, $technology_id);

        if ($arow === false) {
            fn_set_notification('E', __('error'), __('object_not_found', array('[object]' => __('technology'))),'','404');
            $technology_id = false;
        }
        $existing_products = db_get_fields("SELECT product_id FROM ?:product_technologies WHERE technology_id = ?i", $technology_id);
    }

    if (!empty($technology_id) && isset($_data['products'])) {

        // Log technology add/update
        fn_log_event('technologies', !empty($create) ? 'create' : 'update', array(
            'technology_id' => $technology_id,
        ));
        
        $_data['products'] = (empty($_data['products'])) ? array() : explode(',', $_data['products']);
        $to_delete = array_diff($existing_products, $_data['products']);

        if (!empty($to_delete)) {
            db_query("DELETE FROM ?:product_technologies WHERE product_id IN (?n) AND technology_id = ?i", $to_delete, $technology_id);
        }
        $to_add = array_diff($_data['products'], $existing_products);

        if (!empty($to_add)) {
            foreach ($to_add as $i => $gr) {
                $__data = array(
                    'technology_id' => $technology_id,
                    'product_id' => $gr
                );
                db_query("REPLACE INTO ?:product_technologies ?e", $__data);
            }
        }
    }
    
    fn_set_hook('update_technology_post', $_data, $technology_id);
    
    return $technology_id;

}

function fn_get_state_parts($state)
{
    $state_parts = preg_split("/( |\-)/", trim(str_replace(array('область', 'республика', 'автономный', 'округ', 'автономная', 'край'), array('', '', '', '', '', ''), fn_strtolower($state))));
    foreach ($state_parts as $i => $pr) {
        if (empty($pr) || $pr == '—') {
            unset($state_parts[$i]);
        }
    }
    
    return $state_parts;
}

function fn_find_state_match($state)
{
    $state_parts = fn_get_state_parts($state);

    list($states,) = fn_get_states(array('country_code' => 'RU'));
    $match = array();
    foreach ($states as $i => $st_dt) {
        $_state_parts = fn_get_state_parts($st_dt['state']);
        $match[$st_dt['code']] = round(100 / count($state_parts) * count(array_intersect($_state_parts, $state_parts)), 2);
    }
    if (!empty($match)) {
        arsort($match);
        return key($match);
    }
    
    return false;
}

function fn_get_warehouse_data($warehouse_id)
{
    $field_list = "?:warehouses.*, GROUP_CONCAT(?:warehouse_brands.brand_id SEPARATOR ',') AS brand_ids";

    fn_set_hook('get_warehouse_data', $warehouse_id, $field_list, $join, $condition);
    
    $warehouse_data = db_get_row("SELECT $field_list FROM ?:warehouses LEFT JOIN ?:warehouse_brands ON ?:warehouse_brands.warehouse_id = ?:warehouses.warehouse_id ?p WHERE ?:warehouses.warehouse_id = ?i  ?p GROUP BY ?:warehouses.warehouse_id", $join, $warehouse_id, $condition);
    
    if (!empty($warehouse_data['brand_ids'])) {
        $warehouse_data['brand_ids'] = explode(',', $warehouse_data['brand_ids']);
    }

    fn_set_hook('get_warehouse_data_post', $warehouse_data);

    return (!empty($warehouse_data) ? $warehouse_data : false);
}

function fn_delete_warehouse($warehouse_id)
{
    if (empty($warehouse_id) || $warehouse_id == TH_WAREHOUSE_ID) {
        return false;
    }

    // Log warehouse deletion
    fn_log_event('warehouses', 'delete', array(
        'warehouse_id' => $warehouse_id,
    ));

    // Deleting warehouse
    db_query("DELETE FROM ?:warehouses WHERE warehouse_id = ?i", $warehouse_id);
    db_query("DELETE FROM ?:warehouse_brands WHERE warehouse_id = ?i", $warehouse_id);
    db_query("DELETE FROM ?:product_warehouses_inventory WHERE warehouse_id = ?i", $warehouse_id);

    fn_set_hook('delete_warehouse', $warehouse_id);
    
    return true;
}

function fn_get_product_warehouses($product_id)
{
    $warehouses = array();
    $warehouse_ids = db_get_field("SELECT warehouse_ids FROM ?:products WHERE product_id = ?i", $product_id);
    if (!empty($warehouse_ids)) {
        $warehouse_ids = unserialize($warehouse_ids);
        $warehouses = db_get_hash_array("SELECT warehouse_id, name FROM ?:warehouses WHERE warehouse_id IN (?n) ORDER BY priority ASC", 'warehouse_id', $warehouse_ids);
    }
    
    return $warehouses;
}

function fn_get_warehouses($params)
{
    $fields = array (
        '?:warehouses.*',
    );

    $condition = $join = '';

    if (!empty($params['warehouse'])) {
        $condition .= db_quote(" AND ?:warehouses.name LIKE ?l", "%".trim($params['warehouse'])."%");
    }

    if (!empty($params['item_ids'])) {
        $condition .= db_quote(' AND ?:warehouses.warehouse_id IN (?n)', explode(',', $params['item_ids']));
    }

    if (!empty($params['except_id']) && (empty($params['item_ids']) || !empty($params['item_ids']) && !in_array($params['except_id'], explode(',', $params['item_ids'])))) {
        $condition .= db_quote(' AND ?:warehouses.warehouse_id != ?i', $params['except_id']);
    }

    $limit = '';

    fn_set_hook('get_warehouses', $params, $join, $condition, $fields);
    
    if (!empty($params['limit'])) {
        $limit = db_quote(' LIMIT 0, ?i', $params['limit']);
    }

    $warehouses = db_get_hash_array('SELECT ' . implode(',', $fields) . " FROM ?:warehouses ?p WHERE 1 ?p GROUP BY ?:warehouses.warehouse_id ORDER BY ?:warehouses.priority ASC ?p", 'warehouse_id', $join, $condition, $limit);

    if (empty($warehouses)) {
        return array(array(), $params);
    }

    fn_set_hook('get_warehouses_post', $warehouses, $params);
    
    return array($warehouses, $params);
}

function fn_update_warehouse($warehouse_data, $warehouse_id = 0)
{
    $_data = $warehouse_data;

    // create new warehouse
    if (empty($warehouse_id)) {

        $create = true;

        $warehouse_id = db_query("INSERT INTO ?:warehouses ?e", $_data);
        
        $existing_brands = array();
    // update existing warehouse
    } else {

        $arow = db_query("UPDATE ?:warehouses SET ?u WHERE warehouse_id = ?i", $_data, $warehouse_id);

        if ($arow === false) {
            fn_set_notification('E', __('error'), __('object_not_found', array('[object]' => __('warehouse'))),'','404');
            $warehouse_id = false;
        }
        $existing_brands = db_get_fields("SELECT brand_id FROM ?:warehouse_brands WHERE warehouse_id = ?i", $warehouse_id);
    }

    if (!empty($warehouse_id) && isset($_data['brand_ids'])) {

        $to_delete = array_diff($existing_brands, $_data['brand_ids']);

        if (!empty($to_delete)) {
            db_query("DELETE FROM ?:warehouse_brands WHERE brand_id IN (?n) AND warehouse_id = ?i", $to_delete, $warehouse_id);
        }
        $to_add = array_diff($_data['brand_ids'], $existing_brands);

        if (!empty($to_add)) {
            foreach ($to_add as $i => $gr) {
                $__data = array(
                    'warehouse_id' => $warehouse_id,
                    'brand_id' => $gr
                );
                db_query("REPLACE INTO ?:warehouse_brands ?e", $__data);
            }
        }

        // Log warehouse add/update
        fn_log_event('warehouses', !empty($create) ? 'create' : 'update', array(
            'warehouse_id' => $warehouse_id,
        ));
        
    }
    
    fn_set_hook('update_warehouse_post', $_data, $warehouse_id);
    
    return $warehouse_id;

}

function fn_get_warehouse_name($warehouse_id)
{
    return db_get_field("SELECT name FROM ?:warehouses WHERE warehouse_id = ?i", $warehouse_id);
}

function fn_development_get_brands()
{
    $params = array(
        'exclude_group' => true,
        'get_descriptions' => true,
        'feature_types' => array('E'),
        'variants' => true,
        'plain' => true,
    );

    list($features) = fn_get_product_features($params, 0);

    $variants = array();

    foreach ($features as $feature) {
        $variants = array_merge($variants, $feature['variants']);
    }

    return $variants;
}

function fn_rebuild_product_options_inventory_multi($product_ids, $products_options = array(), $product_data = array(), $amount = 50)
{
    if (empty($products_options)) {
        $products_options = fn_get_product_options($product_ids, DESCR_SL, true, true);
    }

    if (!empty($products_options)) {
    
        if (empty($product_data)) {
            $product_data = db_get_hash_array("SELECT product_code, warehouse_ids FROM ?:products WHERE product_id IN (?n)", 'product_id', $product_ids);
        }
        $inventory_data = db_get_hash_multi_array("SELECT combination_hash, amount, product_code, product_id FROM ?:product_options_inventory WHERE product_id IN (?n)", array('product_id', 'combination_hash'), $product_ids);
        
        $warehouse_data = db_get_hash_multi_array("SELECT * FROM ?:product_warehouses_inventory WHERE product_id IN (?n)", array('product_id', 'warehouse_hash'), $product_ids);
        db_query("DELETE FROM ?:product_warehouses_inventory WHERE product_id IN (?n) AND combination_hash != '0'", $product_ids);

        $new_inventory_data = $new_wh_inventory = $delete_combinations = array();
        foreach ($products_options as $product_id => $_options) {

            if (empty($_options)) {
                continue;
            }
            fn_set_hook('rebuild_product_options_inventory_pre', $product_id, $amount);

            $options = array_keys($_options);

            $variants = $variant_codes = array();
            foreach ($_options as $k => $option) {
                $variants[] = array_keys($option['variants']);
                $_codes = array();
                foreach ($option['variants'] as $vr_id => $vr_data) {
                    $_codes[$vr_id] = $vr_data['code_suffix'];
                }
                $variant_codes[$k] = $_codes;
            }
            fn_set_hook('look_through_variants_pre', $product_id, $amount, $options, $variants);
        
            $position = 0;
            $hashes = array();
            $combinations = fn_get_options_combinations($options, $variants);
            
            if (!empty($combinations)) {
                foreach ($combinations as $combination) {

                    $_data = array();
                    $_data['product_id'] = $product_id;

                    $_data['combination_hash'] = fn_generate_cart_id($product_id, array('product_options' => $combination));

                    if (array_search($_data['combination_hash'], $hashes) === false) {
                        $hashes[] = $_data['combination_hash'];
                        $_data['combination'] = fn_get_options_combination($combination);
                        $_data['position'] = $position++;

                        $_data['product_code'] = (!empty($product_data[$product_id]['product_code'])) ? $product_data[$product_id]['product_code'] : '';
                        foreach ($combination as $option_id => $variant_id) {
                            if (isset($variant_codes[$option_id][$variant_id])) {
                                $_data['product_code'] .= $variant_codes[$option_id][$variant_id];
                            }
                        }

                        $_data['amount'] = isset($inventory_data[$product_id][$_data['combination_hash']]['amount']) ? $inventory_data[$product_id][$_data['combination_hash']]['amount'] : $amount;
        //                $_data['product_code'] = isset($inventory_data[$product_id][$_data['combination_hash']]['product_code']) ? $inventory_data[$product_id][$_data['combination_hash']]['product_code'] : '';

                        fn_set_hook('look_through_variants_update_combination', $combination, $_data, $product_id, $amount, $options, $variants);

                        $new_inventory_data[] = $_data;
//                         $combinations[] = $combination;
                    }
//                     echo str_repeat('. ', count($combination));
                }
                if (!empty($hashes)) {
                    $warehouse_ids = explode(',', $product_data[$product_id]['warehouse_ids']);
                    if (!empty($warehouse_ids)) {
                        foreach ($combinations as $combination) {
                            foreach ($warehouse_ids as $i => $wh_id) {
                                $wh_hash = fn_generate_cart_id($product_id, array('product_options' => $combination, 'warehouse_id' => $wh_id));
                                $new_wh_inventory[] = array(
                                    'warehouse_hash' => $wh_hash,
                                    'warehouse_id' => $wh_id,
                                    'product_id' => $product_id,
                                    'combination_hash' => fn_generate_cart_id($product_id, array('product_options' => $combination)),
                                    'amount' => isset($warehouse_data[$product_id][$wh_hash]['amount']) ? $warehouse_data[$product_id][$wh_hash]['amount'] : 0
                                );
                            }
                        }
                    }
                }
            }

            fn_set_hook('look_through_variants_post', $combinations, $product_id, $amount, $options, $variants);
            
            if (!empty($inventory_data[$product_id])) {
                $delete_combinations = array_merge($delete_combinations, array_diff(array_keys($inventory_data[$product_id]), $hashes));
            }

            fn_set_hook('rebuild_product_options_inventory_post', $product_id);
        }
        if (!empty($delete_combinations)) {
            db_query("DELETE FROM ?:product_options_inventory WHERE combination_hash IN (?n)", $delete_combinations);
            foreach ($delete_combinations as $v) {
                fn_delete_image_pairs($v, 'product_option');
            }
        }
        if (!empty($new_inventory_data)) {
            db_query("REPLACE INTO ?:product_options_inventory ?m", $new_inventory_data);
        }
        if (!empty($new_wh_inventory)) {
            db_query("REPLACE INTO ?:product_warehouses_inventory ?m", $new_wh_inventory);
        }
    }
}