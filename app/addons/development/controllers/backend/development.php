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

use Tygh\Memcached;
use Tygh\Registry;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
}

if ($mode == 'calculate_balance') {

    $params = $_REQUEST;
    if (!empty($params['length']) && !empty($params['points']) && !empty($params['relation'])) {
        if (strtoupper($params['relation']) == 'HL') {
            $result = $params['length']/2 - $params['points'] * 1/8;
        } else {
            $result = $params['length']/2 + $params['points'] * 1/8;
        }
        $params['result'] = $result * 2.54;
    }
    Registry::get('view')->assign('params', $params);
    
} elseif ($mode == 'test_memcached') {
// Memcached::instance()->set('a', serialize(array()));
 //fn_print_r(Memcached::instance()->get('9ab0ee1123f31620ca9b0c08b5a3b1d2'), unserialize(Memcached::instance()->get('9ab0ee1123f31620ca9b0c08b5a3b1d2')) !== false);
// fn_print_r(unserialize(Memcached::instance()->get('b')) !== false);
// $keys = Memcached::instance()->getAllKeys();
// if (!empty($keys)) {
//     foreach ($keys as $i => $key) {
//         fn_print_r($key, Memcached::instance()->get($key));
//     }
// }
// Memcached::instance()->clear();
$keys = Memcached::instance()->getAllKeys();
if (!empty($keys)) {
    foreach ($keys as $i => $key) {
        fn_print_r($key, Memcached::instance()->get($key));
    }
}
exit;
}