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
                $approx_shipping['zipcode'] = $profile_data['s_zipcode'];
            } elseif (!empty($_SESSION['ip_data']) && !empty($_SESSION['ip_data']['city']) && !empty($_SESSION['ip_data']['state']) && !empty($_SESSION['ip_data']['country'])) {
                $approx_shipping = $_SESSION['ip_data'];
            } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
                $approx_shipping = fn_get_location_by_ip();
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
if ($mode == 'switch_dmode') {
    if (!empty($_REQUEST['dmode'])) {
        if (fn_get_session_data('dmode') != $_REQUEST['dmode']) {
            fn_set_session_data('dmode', $_REQUEST['dmode']);
        }
    }
    
    return array(CONTROLLER_STATUS_REDIRECT, !empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : "index.index");
}
if ($mode == 'redirect') {
    return array(CONTROLLER_STATUS_REDIRECT, !empty($_REQUEST['redirect_url']) ? $_REQUEST['redirect_url'] : (!empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : "index.index"));
}
if ($mode == 'load_image') {
    if (!empty($_REQUEST['id']) && !empty($_REQUEST['el_id'])) {
        $pair_data = fn_get_image_by_pair_id($_REQUEST['id']);
        if (!empty($pair_data)) {
            Registry::get('view')->assign('pair_data', $pair_data);
            Registry::get('view')->assign('iw', Registry::get('settings.Thumbnails.product_lists_thumbnail_width'));
            Registry::get('view')->assign('ih', Registry::get('settings.Thumbnails.product_lists_thumbnail_height'));
            Registry::get('view')->assign('el_id', $_REQUEST['el_id']);
            Registry::get('view')->display('addons/development/common/load_image.tpl');
        }
    }
    exit;
}