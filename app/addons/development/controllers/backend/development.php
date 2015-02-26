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

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
}

if ($mode == 'calculate_balance') {

    $params = $_REQUEST;
    if (!empty($params['length']) && !empty($params['relation'])) {
        if (strtoupper($params['relation']) == 'HL') {
            $result = $params['length']/2 - $params['points'] * 1/8;
        } else {
            $result = $params['length']/2 + $params['points'] * 1/8;
        }
        $params['result'] = $result * 2.54;
    }
    Registry::get('view')->assign('params', $params);
    
} elseif ($mode == 'show_memcached') {
//    fn_print_r(Memcache::instance()->call('getMemcacheKeys', 2000));
    $keys = Memcache::instance()->call('getAllKeys');
    $result = array();
    if (!empty($keys)) {
        foreach ($keys as $i => $key) {
            $result[$key] = Memcache::instance()->call('get', $key);
        }
    }
    fn_print_r($result);
    exit;
} elseif ($mode == 'test_memcached') {
    exit;
} elseif ($mode == 'rebuild_combinations') {
    $product_ids = db_get_fields("SELECT DISTINCT(product_id) FROM ?:product_options_inventory");
    if (!empty($product_ids)) {
        foreach ($product_ids as $i => $product_id) {
            fn_rebuild_product_options_inventory($product_id);
        }
    }
    exit;
} elseif ($mode == 'colors') {
    $product_ids = db_get_fields("SELECT a.product_id FROM ?:product_options AS a");
    fn_print_die($product_ids);
}