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
use Tygh\Bootstrap;

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

} elseif ($mode == 'update_stocks') {

    if (!empty($_REQUEST['calculate'])) {
        $file = fn_filter_uploaded_data('csv_file');
        if (!empty($file) && !empty($_REQUEST['brand_id'])) {
            if ($_REQUEST['brand_id'] == BABOLAT_FV_ID) {
                $options = array(
                    'delimiter' => 'C',
                    'lang_code' => 'ru'
                );
                if (list($data, $skipped) = fn_get_babolat_csv($file[0]['path'], $options)) {

                    $missing_products = $duplicate_products = $updated_products = $broken_options_products = array();
                    foreach ($data as $product_code => $data) {
                        $ids = db_get_fields("SELECT product_id FROM ?:products WHERE product_code = ?s", $product_code);
                        if (count($ids) == 0 || empty($data['data'])) {
                            $missing_products[$product_code] = $data;
                        } elseif (count($ids) > 1) {
                            $duplicate_products[$product_code] = $data;
                        } else {
                            $product_id = reset($ids);
                            $product_data = fn_get_product_data($product_id, $auth, DESCR_SL, '', false, false, false, false, false, false, false);
                            $product_options = fn_get_product_options($product_id, DESCR_SL, true);
                            $option_names = array();
                            if (!empty($product_options)) {
                                foreach ($product_options as $h => $option) {
                                    $option_names[] = $option['option_name'];
                                }
                            }
                            $combinations_data = array();
                            foreach ($data['data'] as $i => $variant) {
                                if (!empty($variant[0]) && !empty($product_options) && $product_data['tracking'] == 'O' && !empty($variant[4])) {
                                    $variants = explode(',', $variant[0]);
                                    $option_data = array();
                                    foreach ($variants as $j => $variant_name) {
                                        $variant_name = 'ручка: 45';
                                        if (!empty($option_names)) {
                                            foreach ($option_names as $h => $o_name) {
                                                 $variant_name = str_ireplace(fn_strtolower($o_name), '', fn_strtolower($variant_name));
                                            }
                                        }
                                        $variant_name = preg_replace('/:/', '', $variant_name);
                                        fn_trim_helper($variant_name);
                                        $is_found = false;
                                        foreach ($product_options as $k => $opt_data) {
                                            if (!empty($opt_data['variants'])) {
                                                foreach ($opt_data['variants'] as $kk => $vr_data) {
                                                    if ($vr_data['variant_name'] == $variant_name) {
                                                        $option_data[$opt_data['option_id']] = $vr_data['variant_id'];
                                                        $is_found = true;
                                                        break 2;
                                                    }
                                                }
                                            }
                                        }
                                        if (!$is_found) {
                                            $broken_options_products[$product_code] = $data;
                                            break 2;
                                        }
                                    }
                                    if (!empty($option_data)) {
                                        $combination_hash = fn_generate_cart_id($product_id, array('product_options' => $option_data));
                                        $combinations_data[$combination_hash]['amount'] = $variant[4];
                                    } else {
                                        $broken_options_products[$product_code] = $data;
                                        break;
                                    }
                                } elseif (count($data['data']) == 1 && $product_data['tracking'] == 'B' && !empty($variant[4])) {
                                    db_query("UPDATE ?:products SET amount = ?i WHERE product_id = ?i", $variant[4], $product_id);
                                    $updated_products[$product_code] = $data;
                                } else {
                                    $missing_products[$product_code] = $data;
                                    break;
                                }
                            }
                            if (!empty($combinations_data)) {
                                $inventory = db_get_hash_array("SELECT amount, combination_hash FROM ?:product_options_inventory WHERE product_id = ?i", 'combination_hash', $product_id);

                                foreach ($inventory as $k => $v) {
                                    if (!empty($combinations_data[$k])) {
                                        $inventory[$k]['amount'] = $combinations_data[$k]['amount'];
                                        unset($combinations_data[$k]);
                                    } else {
                                        $inventory[$k]['amount'] = 0;
                                    }
                                    db_query("UPDATE ?:product_options_inventory SET ?u WHERE combination_hash = ?s", $inventory[$k], $k);
                                }
                                fn_update_product_exceptions($product_id, $inventory);
                                if (!empty($combinations_data)) {
                                    $missing_products[$product_code] = $data;
                                }
                            } else {
                                $missing_products[$product_code] = $data;
                            }
                        }
                        fn_print_die('iter');
                    }
                    fn_print_die($data, $skipped_lines, $missing_products, $duplicate_products);
                }
            }
        } elseif (empty($_REQUEST['brand_id'])) {
            fn_set_notification('E', __('error'), __('error_brand_undefined'));
        } elseif (empty($file)) {
            fn_set_notification('E', __('error'), __('error_exim_no_file_uploaded'));
        }
    }

    Registry::get('view')->assign('brands', fn_development_get_brands());
    
} elseif ($mode == 'supplier_stocks') {

    Registry::get('view')->assign('brands', fn_get_all_brands());
    
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
} elseif ($mode == 'optimize_option_images') {
    $ids = db_get_hash_array("SELECT a.*, b.image_path, c.image_path AS detailed_path, p.product_id, po.product_id AS option_product_id FROM ?:images_links AS a LEFT JOIN ?:images AS b ON a.image_id = b.image_id LEFT JOIN ?:images AS c ON a.detailed_id = c.image_id LEFT JOIN ?:products AS p ON p.product_id = a.object_id AND a.object_type = 'product' LEFT JOIN ?:product_option_variants AS v ON v.variant_id = a.object_id AND a.object_type = 'variant_additional' LEFT JOIN ?:product_options AS po ON po.option_id = v.option_id", 'pair_id');
        fn_print_die($ids);

//     $to_delete = array();
//     foreach ($ids as $obj_id => $obj_data) {
//         $image_path = $detailed_path = '';
//         if ($obj_data['object_type'] == 'product') {
//             $image_path = substr($obj_data['image_path'], 0, strrpos($obj_data['image_path'], '.'));
//             $detailed_path = substr($obj_data['detailed_path'], 0, strrpos($obj_data['detailed_path'], '.'));
//             foreach ($ids as $_obj_id => $_obj_data) {
//                 if ($_obj_data['object_type'] == 'variant_additional' && $_obj_data['option_product_id'] == $obj_data['product_id'] && ((!empty($_obj_data['image_path']) && strpos($_obj_data['image_path'], $image_path) !== false) || (!empty($_obj_data['detailed_path']) && strpos($_obj_data['detailed_path'], $detailed_path) !== false))) {
//                     $to_delete[] = $_obj_data;
//                 }
//             }
//         }
//         
//     }
//     if (!empty($to_delete)) {
//         foreach ($to_delete as $i => $d_data) {
//             //fn_delete_image_pair($d_data['pair_id'], 'variant_additional');
//         }
//     }
} elseif ($mode == 'check_options') {
    $product_ids = db_get_fields("SELECT DISTINCT(a.product_id) FROM ?:product_options AS a LEFT JOIN ?:products_categories AS b ON a.product_id = b.product_id WHERE b.category_id = '255'");
    if (!empty($product_ids)) {
        db_query("UPDATE ?:products SET options_type = 'S' WHERE product_id IN (?n)", $product_ids);
//         foreach ($product_ids as $i => $product_id) {
//             fn_rebuild_product_options_inventory($product_id);
//         }
    }
    exit;
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

function fn_get_babolat_csv($file, $options)
{
    $max_line_size = 65536; // 64 Кб
    $result = array();

    if ($options['delimiter'] == 'C') {
        $delimiter = ',';
    } elseif ($options['delimiter'] == 'T') {
        $delimiter = "\t";
    } else {
        $delimiter = ';';
    }

    if (!empty($file) && file_exists($file)) {

        $encoding = fn_detect_encoding($file, 'F', !empty($options['lang_code']) ? $options['lang_code'] : CART_LANGUAGE);

        if (!empty($encoding)) {
             $file = fn_convert_encoding($encoding, 'UTF-8', $file, 'F');
        } else {
            fn_set_notification('W', __('warning'), __('text_exim_utf8_file_format'));
        }

        $f = false;
        if ($file !== false) {
            $f = fopen($file, 'rb');
        }

        if ($f) {

            // Collect data
            $skipped_lines = array();
            $line_it = 1;
            $current_product_code = '';
            $previous_row = array();
            while (($data = fn_fgetcsv($f, $max_line_size, $delimiter)) !== false) {

                $line_it ++;
                if (fn_is_empty($data)) {
                    continue;
                }
                $row = Bootstrap::stripSlashes($data);
                if (empty($current_product_code) && empty($row[0])) {
                    $skipped_lines[] = array(
                        'line' => $line_it,
                        'data' => $row
                    );
                    continue;
                } elseif (!empty($row[0]) && preg_match("/^[a-zA-Z0-9]{4,8}$/", $row[0], $matches)) {
                    if (in_array($matches[0], $result)) {
                        fn_set_notification('W', __('warning'), __('error_duplicate_product_code', array(
                            '[line1]' => $result[$matches[0]]['line'],
                            '[line2]' => $line_it,
                            '[product_code]' => $matches[0]
                        )));
                        return false;
                    }
                    $current_product_code = $matches[0];
                    $result[$current_product_code] = array(
                        'line' => $line_it,
                        'product' => $row[1],
                        'data' => array()
                    );
                } elseif (!empty($current_product_code) && empty($row[1])/* && !empty($previous_row) && $row[2] == $previous_row[2] && $row[3] == $previous_row[3]*/) {
                    $result[$current_product_code]['data'][] = $row;
                } else {
                    $current_product_code = '';
                    $previous_row = '';
                    $skipped_lines[] = array(
                        'line' => $line_it,
                        'data' => $row
                    );
                    continue;
                }
                $previous_row = $row;
            }

            return array($result, $skipped_lines);
        } else {
            fn_set_notification('E', __('error'), __('error_exim_cant_open_file'));

            return false;
        }
    } else {
        fn_set_notification('E', __('error'), __('error_exim_file_doesnt_exist'));

        return false;
    }
}
