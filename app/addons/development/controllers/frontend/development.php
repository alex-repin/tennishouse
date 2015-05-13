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
use Tygh\FeaturesCache;
use Tygh\Http;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($mode == 'update_rub_rate') {
    $rates = fn_get_currency_exchange_rates();
    $update_prices = false;
    if ($rates) {
        foreach ($rates as $code => $rate) {
            if (!empty(Registry::get('currencies.' . $code)) && Registry::get('currencies.' . $code . '.coefficient') != $rate) {
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

    exit;
} elseif ($mode == 'generate_features_cache') {
    FeaturesCache::generate(CART_LANGUAGE);
    exit;
} elseif ($mode == 'update_rankings') {
    $players = db_get_array("SELECT player_id, data_link, gender FROM ?:players WHERE data_link != ''");
    $update = array();
    if (!empty($players)) {
        foreach ($players as $i => $player) {
            $result = Http::get($player['data_link']);
            if ($result) {
                if ($player['gender'] == 'M') {
                    preg_match('/<div id="playerBioInfoRank">.*?(\d+).*?<\/div>/', preg_replace('/[\r\n]/', '', $result), $match);
                    if (!empty($match['1'])) {
                        $update[] = array(
                            'player_id' => $player['player_id'],
                            'ranking' => $match['1']
                        );
                    }
                } else {
                    preg_match('/<div class="box ranking">.*?>(\d+)<.*?<\/div>/', preg_replace('/[\r\n]/', '', $result), $match);
                    if (!empty($match['1'])) {
                        $update[] = array(
                            'player_id' => $player['player_id'],
                            'ranking' => $match['1']
                        );
                    }
                }
            }
        }
    }
    if (!empty($update)) {
        foreach ($update as $i => $_dt) {
            db_query("UPDATE ?:players SET ranking = ?i WHERE player_id = ?i", $_dt['ranking'], $_dt['player_id']);
        }
        fn_set_notification('N', __('notice'), __('rankings_updated_successfully', array('[total]' => count($players), '[updated]' => count($update))));
    }
    exit;
}