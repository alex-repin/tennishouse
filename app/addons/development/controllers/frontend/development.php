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
use Tygh\Http;
use Tygh\Registry;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

$approx_shipping = & $_SESSION['approx_shipping'];

if ($mode == 'update_rub_rate') {
    fn_update_rub_rate();
    exit;
} elseif ($mode == 'generate_features_cache') {
    FeaturesCache::generate(CART_LANGUAGE);
    exit;
} elseif ($mode == 'update_rankings') {
    fn_update_rankings();
    exit;
} elseif ($mode == 'cron_routine') {
    fn_set_hook('cron_routine');
    exit;
} elseif ($mode == 'hide_anouncement') {
    $_SESSION['hide_anouncement'] = true;
    exit;
} elseif ($mode == 'test_curl') {
    $extra = array(
//        'request_timeout' => 2
        'test' => true
    );
    // 'https://news.yandex.ru/hardware.rss'
    // 'http://www.championat.com/xml/rss_tennis-article.xml'
    $response = Http::get('https://news.yandex.ru/hardware.rss', array(), $extra);
    fn_print_die($response);
}
if ($mode == 'product_shipping_estimation') {
    if (empty($approx_shipping['is_complete'])) {
        if (empty($approx_shipping['city'])) {
            if (!empty($_SESSION['auth']['user_id'])) {
                $profile_data = db_get_row("SELECT * FROM ?:user_profiles WHERE user_id = ?i AND profile_type = 'P'", $_SESSION['auth']['user_id']);
                $approx_shipping['city'] = $profile_data['s_city'];
                $approx_shipping['state'] = $profile_data['s_state'];
                $approx_shipping['country'] = $profile_data['s_country'];
            } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
                $ip = $_SERVER['REMOTE_ADDR'];
//                 $ip = '79.132.124.103';
                $response = Http::get('http://ipgeobase.ru:7020/geo',
                    array('ip' => $ip)
                );
                $xml = @simplexml_load_string($response);
                if (!empty($xml->ip->city)) {
                    $approx_shipping['city'] = strval($xml->ip->city);
                }
                if (!empty($xml->ip->region) && $state = fn_find_state_match($xml->ip->region)) {
                    $approx_shipping['country'] = 'RU';
                    $approx_shipping['state'] = $state;
                }
            }
        }
        if (empty($approx_shipping['time']) && !empty($approx_shipping['country']) && !empty($approx_shipping['state']) && !empty($approx_shipping['city'])) {
            $approx_shipping['time'] = fn_get_approximate_shipping($approx_shipping);
        }
        $approx_shipping['is_complete'] = true;
        Registry::get('view')->display('addons/development/common/product_shipping_estimation.tpl');
    }
    exit;
}
if ($mode == 'update_user_city') {
    unset($approx_shipping['time']);
    if (!empty($_REQUEST['city_id'])) {
        $approx_shipping['city_id'] = $_REQUEST['city_id'];
    }
    if (!empty($_REQUEST['user_city'])) {
        $approx_shipping['city'] = $_REQUEST['user_city'];
        $approx_shipping['state'] = $_REQUEST['state'];
        $approx_shipping['country'] = 'RU';
    } elseif (!empty($_REQUEST['city'])) {
        $approx_shipping['city'] = $_REQUEST['city'];
        $approx_shipping['country'] = 'RU';
        if (!empty($_REQUEST['state'])) {
            $approx_shipping['state'] = $_REQUEST['state'];
        } else {
            $approx_shipping['state'] = db_get_field("SELECT a.state_code FROM ?:rus_cities_sdek AS a LEFT JOIN ?:rus_city_sdek_descriptions AS b ON a.city_id = b.city_id AND b.lang_code = 'ru' WHERE b.city = ?s", $_REQUEST['city']);
        }
    }
    if (empty($approx_shipping['time']) && !empty($approx_shipping['country']) && !empty($approx_shipping['state']) && !empty($approx_shipping['city'])) {
        $approx_shipping['time'] = fn_get_approximate_shipping($approx_shipping);
    }
    $approx_shipping['is_complete'] = true;
    Registry::get('view')->display('addons/development/common/product_shipping_estimation.tpl');
    exit;
}
if ($mode == 'find_state_match') {
    header('Content-Type: application/json');
    fn_echo(json_encode(fn_find_state_match($_REQUEST['state'])));
    exit;
}
if ($mode == 'find_state_data') {
    header('Content-Type: application/json');
    
    $state_code = db_get_field("SELECT a.state_code FROM ?:rus_cities_sdek AS a LEFT JOIN ?:rus_city_sdek_descriptions AS b ON a.city_id = b.city_id AND b.lang_code = 'ru' WHERE b.city = ?s", $_REQUEST['city']);
    fn_echo(json_encode($state_code));
    exit;
}