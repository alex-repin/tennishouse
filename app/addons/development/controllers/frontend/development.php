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

if (!defined('BOOTSTRAP')) { die('Access denied'); }

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