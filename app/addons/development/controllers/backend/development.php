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
    $product_ids = db_get_fields("SELECT DISTINCT(a.product_id) FROM ?:product_options_inventory AS a LEFT JOIN ?:images_links AS b ON a.combination_hash = b.object_id AND b.object_type = 'product_option' WHERE b.pair_id IS NOT NULL");
    fn_print_die($product_ids);
} elseif ($mode = 'optimize_option_images') {
    $ids = db_get_hash_array("SELECT a.*, b.image_path, c.image_path AS detailed_path, p.product_id, po.product_id AS option_product_id FROM ?:images_links AS a LEFT JOIN ?:images AS b ON a.image_id = b.image_id LEFT JOIN ?:images AS c ON a.detailed_id = c.image_id LEFT JOIN ?:products AS p ON p.product_id = a.object_id AND a.object_type = 'product' LEFT JOIN ?:product_option_variants AS v ON v.variant_id = a.object_id AND a.object_type = 'variant_additional' LEFT JOIN ?:product_options AS po ON po.option_id = v.option_id", 'pair_id');
        fn_print_die($ids);

    $to_delete = array();
    foreach ($ids as $obj_id => $obj_data) {
        $image_path = $detailed_path = '';
        if ($obj_data['object_type'] == 'product') {
            $image_path = substr($obj_data['image_path'], 0, strrpos($obj_data['image_path'], '.'));
            $detailed_path = substr($obj_data['detailed_path'], 0, strrpos($obj_data['detailed_path'], '.'));
            foreach ($ids as $_obj_id => $_obj_data) {
                if ($_obj_data['object_type'] == 'variant_additional' && $_obj_data['option_product_id'] == $obj_data['product_id'] && ((!empty($_obj_data['image_path']) && strpos($_obj_data['image_path'], $image_path) !== false) || (!empty($_obj_data['detailed_path']) && strpos($_obj_data['detailed_path'], $detailed_path) !== false))) {
                    $to_delete[] = $_obj_data;
                }
            }
        }
        
    }
    if (!empty($to_delete)) {
        foreach ($to_delete as $i => $d_data) {
            //fn_delete_image_pair($d_data['pair_id'], 'variant_additional');
        }
    }
}