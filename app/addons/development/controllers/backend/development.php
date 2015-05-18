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
use Tygh\FeaturesCache;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if ($mode == 'update_stocks') {

        if (!empty($_REQUEST['calculate'])) {
            $file = fn_filter_uploaded_data('csv_file');
            $missing_products = $updated_products = $broken_options_products = $trash = array();
            if (!empty($file) && !empty($_REQUEST['brand_id'])) {
                if ($_REQUEST['brand_id'] == BABOLAT_FV_ID) {
                    $options = array(
                        'delimiter' => 'C',
                        'lang_code' => 'ru'
                    );
                    if (list($total_data, $trash) = fn_get_babolat_csv($file[0]['path'], $options)) {

                        $params = array(
                            'features_hash' => 'V' . $_REQUEST['brand_id'],
                            //'force_get_by_ids' => 'Y',
                        );
                        list($products,) = fn_get_products($params);
                        $in_stock = $all_ids = $no_code = $updated_by_combinations = array();
                        if (!empty($products)) {
                            foreach ($products as $i => $p_data) {
                                if (!empty($p_data['product_code'])) {
                                    $all_ids[] = $p_data['product_id'];
                                } else {
                                    $no_code[] = $p_data['product_id'];
                                }
                            }
                        }
                        $ignore_list = db_get_field("SELECT ignore_list FROM ?:brand_ignore_list WHERE brand_id = ?i", $_REQUEST['brand_id']);
                        $ignore_list = !empty($ignore_list) ? unserialize($ignore_list) : array();
                        foreach ($total_data as $product_code => $data) {
                            if (!empty($ignore_list) && in_array($product_code, $ignore_list)) {
                                continue;
                            }
                            $ids = db_get_fields("SELECT product_id FROM ?:products WHERE product_code = ?s", $product_code);
                            if (empty($data['data'])) {
                                $missing_products[$product_code] = $data;
                            } elseif (count($ids) == 0) {
                                $amount_num = count($data['data'][0]) - 1;
                                $combination_hash = db_get_array("SELECT combination_hash, product_id FROM ?:product_options_inventory WHERE product_code = ?s", $product_code);
                                if (count($combination_hash) != 1 || count($data['data']) > 1 || empty($data['data'][0][$amount_num])) {
                                    $missing_products[$product_code] = $data;
                                } else {
                                    db_query("UPDATE ?:product_options_inventory SET amount = ?i WHERE combination_hash = ?i", $data['data'][0][$amount_num], $combination_hash[0]['combination_hash']);
                                    $updated_products[$product_code] = array(
                                        'code' => $product_code,
                                        'data' => $data
                                    );
                                    if (!in_array($combination_hash[0]['product_id'], $in_stock)) {
                                        $in_stock[] = $product_id;
                                    }
                                    $updated_by_combinations[$combination_hash[0]['product_id']] = (empty($updated_by_combinations[$combination_hash[0]['product_id']])) ? array() : $updated_by_combinations[$combination_hash[0]['product_id']];
                                    $updated_by_combinations[$combination_hash[0]['product_id']][] = $combination_hash[0]['combination_hash'];
                                }
                            } else {
                                $combinations_data = array();
                                foreach ($data['data'] as $i => $variant) {
                                    $option_data = $var_id_tmp = $options_count = $missing_variants = $max = array();
                                    $break = false;
                                    $amount_num = count($variant) - 1;
                                    foreach ($ids as $m => $product_id) {
                                        $option_data[$product_id] = (empty($option_data[$product_id])) ? array() : $option_data[$product_id];
                                        $product_data = fn_get_product_data($product_id, $auth, DESCR_SL, '', false, false, false, false, false, false, false);
                                        $product_options = fn_get_product_options($product_id, DESCR_SL, true, true);
                                        $options_count[$product_id] = array_keys($product_options);
                                        $option_names = array();
                                        if (!empty($product_options)) {
                                            foreach ($product_options as $h => $option) {
                                                $option_names[] = $option['option_name'];
                                            }
                                        }
                                        if (!empty($_REQUEST['debug']) && ($product_id == $_REQUEST['debug'] || $product_code == $_REQUEST['debug'])) {
                                            fn_print_r($variant);
                                        }
                                        if ($variant[0] != '' && !empty($product_options) && $product_data['tracking'] == 'O' && !empty($variant[$amount_num])) {
                                            $variants = explode(',', fn_normalize_string($variant[0]));
                                            $prev_numeric = false;
                                            $prev_id = '';
                                            foreach ($variants as $j => $variant_name) {
                                                if (is_numeric($variant_name) && $prev_numeric) {
                                                    $variants[$prev_id] = $variants[$prev_id] . '.' . $variant_name;
                                                    unset($variants[$j]);
                                                    continue;
                                                } elseif (is_numeric($variant_name)) {
                                                    $prev_numeric = true;
                                                }
                                                $prev_id = $j;
                                            }
                                            if (!empty($_REQUEST['debug']) && ($product_id == $_REQUEST['debug'] || $product_code == $_REQUEST['debug'])) {
                                                fn_print_r($variants);
                                            }
                                            foreach ($variants as $j => $variant_name) {
                                                $max[$j] = (isset($max[$j])) ? $max[$j] : 0;
                                                $variant_name = fn_format_variant_name($variant_name);
                                                if (!empty($option_names)) {
                                                    foreach ($option_names as $h => $o_name) {
                                                        $variant_name = str_ireplace(fn_strtolower($o_name), '', fn_strtolower($variant_name));
                                                    }
                                                }
                                                if (!empty($_REQUEST['debug']) && ($product_id == $_REQUEST['debug'] || $product_code == $_REQUEST['debug'])) {
                                                    fn_print_r($variant_name);
                                                }
                                                $variant_found = false;
                                                foreach ($product_options as $k => $opt_data) {
                                                    if (!empty($opt_data['variants'])) {
                                                        foreach ($opt_data['variants'] as $kk => $vr_data) {
                                                            $var_name = fn_format_variant_name($vr_data['variant_name']);
                                                            if (strlen($var_name) > 1 && strpos($variant_name, $var_name) !== false) {
                                                                $prc = round(strlen($var_name)/strlen($variant_name), 2) * 100;
                                                                $var_id_tmp[$j][$opt_data['option_id']][$prc] = $vr_data['variant_id'];
                                                                if ($prc > $max[$j]) {
                                                                    $max[$j] = $prc;
                                                                }
                                                            }
                                                            if (strlen($variant_name) > 1 && strpos($var_name, $variant_name) !== false) {
                                                                $prc = round(strlen($variant_name)/strlen($var_name), 2) * 100;
                                                                $var_id_tmp[$j][$opt_data['option_id']][round(strlen($variant_name)/strlen($var_name), 2) * 100] = $vr_data['variant_id'];
                                                                if ($prc > $max[$j]) {
                                                                    $max[$j] = $prc;
                                                                }
                                                            }
                                                            if ($var_name === $variant_name) {
                                                                if (empty($option_data[$product_id][$opt_data['option_id']])) {
                                                                    $option_data[$product_id][$opt_data['option_id']] = $vr_data['variant_id'];
                                                                    $variant_found = true;
                                                                    break 2;
                                                                } else {
                                                                    $broken_options_products[$product_code] = $data;
                                                                    break 5;
                                                                }
                                                            }
                                                        }
                                                    }
                                                }
                                                if (!$variant_found) {
                                                    $missing_variants[$product_id][] = $j;
                                                }
                                            }
                                        } elseif (count($data['data']) == 1 && $product_data['tracking'] == 'B' && !empty($variant[$amount_num]) && empty($product_options)) {
                                            $amount = floor($variant[$amount_num] / $product_data['import_divider']);
                                            db_query("UPDATE ?:products SET amount = ?i, status = ?s WHERE product_id = ?i", $amount, ($amount > 0 ? 'A' : 'H'), $product_id);
                                            $updated_products[$product_code] = array(
                                                'code' => $product_code,
                                                'data' => $data
                                            );
                                            if ($amount > 0) {
                                                $in_stock[] = $product_id;
                                            }
                                            $break = true;
                                        } else {
                                            $broken_options_products[$product_code] = $data;
                                            break 2;
                                        }
                                        if (!empty($_REQUEST['debug']) && ($product_id == $_REQUEST['debug'] || $product_code == $_REQUEST['debug'])) {
                                            fn_print_r($option_data);
                                        }
                                    }
                                    if ($break) {
                                        break;
                                    }
                                    if (!empty($option_data)) {
                                        $combination_hash = false;
                                        if (!empty($_REQUEST['debug']) && ($product_id == $_REQUEST['debug'] || $product_code == $_REQUEST['debug'])) {
                                            fn_print_r($options_count, $option_data, $var_id_tmp, $missing_variants, $max);
                                        }
                                        foreach ($option_data as $product_id => $opt_data) {
                                            if (count($options_count[$product_id]) != count($option_data[$product_id]) && !empty($missing_variants[$product_id])) {
                                                $diff = array_diff($options_count[$product_id], array_keys($option_data[$product_id]));
                                                if (!empty($diff)) {
                                                    foreach ($diff as $b => $opt_id) {
                                                        foreach ($missing_variants[$product_id] as $r => $var_num) {
                                                            if (!empty($var_id_tmp[$var_num][$opt_id][$max[$var_num]])) {
                                                                $option_data[$product_id][$opt_id] = $var_id_tmp[$var_num][$opt_id][$max[$var_num]];
                                                            }
                                                        }
                                                    }
                                                }
                                                if (count($options_count[$product_id]) != count($option_data[$product_id])) {
                                                    continue;
                                                }
                                            }
                                            $combination_hash = fn_generate_cart_id($product_id, array('product_options' => $option_data[$product_id]));
                                            $combinations_data[$product_id][$combination_hash]['amount'] = $variant[$amount_num];
                                            if (!empty($_REQUEST['debug']) && ($product_id == $_REQUEST['debug'] || $product_code == $_REQUEST['debug'])) {
                                                fn_print_r($combinations_data);
                                            }
                                            $is_combination = true;
                                            break;
                                        }
                                        if (empty($combination_hash)) {
                                            $broken_options_products[$product_code] = $data;
                                            break;
                                        }
                                    } else {
                                        $broken_options_products[$product_code] = $data;
                                        break;
                                    }
                                }
                                if (!empty($_REQUEST['debug']) && ($product_id == $_REQUEST['debug'] || $product_code == $_REQUEST['debug'])) {
                                    fn_print_die($combinations_data);
                                }
                                if (!empty($combinations_data)) {
                                    $ttl_updated = 0;
                                    foreach ($combinations_data as $product_id => $comb_data) {
                                        $ttl_updated += count($comb_data);
                                        fn_rebuild_product_options_inventory($product_id);
                                        $inventory = db_get_hash_array("SELECT amount, combination_hash FROM ?:product_options_inventory WHERE product_id = ?i", 'combination_hash', $product_id);

                                        foreach ($inventory as $k => $v) {
                                            if (!empty($comb_data[$k])) {
                                                $inventory[$k]['amount'] = $comb_data[$k]['amount'];
                                                unset($comb_data[$k]);
                                            } else {
                                                $inventory[$k]['amount'] = 0;
                                            }
                                            db_query("UPDATE ?:product_options_inventory SET ?u WHERE combination_hash = ?s", $inventory[$k], $k);
                                        }
                                        //db_query("DELETE FROM ?:product_options_exceptions WHERE product_id = ?i", $product_id);
                                        fn_update_product_exceptions($product_id, $inventory);
                                        if (!empty($comb_data)) {
                                            $broken_options_products[$product_code] = $data;
                                        } else {
                                            $updated_products[$product_code] = array(
                                                'code' => $product_code,
                                                'data' => $data
                                            );
                                            $in_stock[] = $product_id;
                                        }
                                    }
                                    if ($ttl_updated != count($data['data'])) {
                                        $broken_options_products[$product_code] = $data;
                                    }
                                }
                            }
                        }
                        $out_of_stock = array_diff($all_ids, $in_stock);
                        if (!empty($in_stock)) {
                            db_query("UPDATE ?:products SET status = 'A' WHERE product_id IN (?n)", $in_stock);
                        }
                        if (!empty($out_of_stock)) {
                            db_query("UPDATE ?:products SET status = 'H', amount = '0' WHERE product_id IN (?n)", $out_of_stock);
                            foreach ($out_of_stock as $os_i => $pr_id) {
                                fn_rebuild_product_options_inventory($pr_id);
                                $all_combs = db_get_hash_single_array("SELECT combination_hash, combination FROM ?:product_options_inventory WHERE product_id = ?i", array('combination_hash', 'combination'), $pr_id);
                                db_query("UPDATE ?:product_options_inventory SET amount = '0' WHERE combination_hash IN (?n)", array_keys($all_combs));
                                foreach ($all_combs as $t => $comb_hash) {
                                    $options_array = fn_get_product_options_by_combination($comb_hash);
                                    $_data = array(
                                        'product_id' => $pr_id,
                                        'combination' => serialize($options_array)
                                    );
                                    db_query("REPLACE INTO ?:product_options_exceptions ?e", $_data);
                                }
                            }
                        }
                        if (!empty($updated_by_combinations)) {
                            foreach ($updated_by_combinations as $pr_id => $combs) {
                                $all_combs = db_get_hash_single_array("SELECT combination_hash, combination FROM ?:product_options_inventory WHERE product_id = ?i", array('combination_hash', 'combination'), $pr_id);
                                db_query("DELETE FROM ?:product_options_exceptions WHERE product_id = ?i", $pr_id);
                                $out = array_diff(array_keys($all_combs), $combs);
                                if (!empty($out)) {
                                    db_query("UPDATE ?:product_options_inventory SET amount = '0' WHERE combination_hash IN (?n)", $out);
                                    foreach ($out as $t => $comb_hash) {
                                        $options_array = fn_get_product_options_by_combination($all_combs[$comb_hash]);
                                        $_data = array(
                                            'product_id' => $pr_id,
                                            'combination' => serialize($options_array)
                                        );
                                        db_query("INSERT INTO ?:product_options_exceptions ?e", $_data);
                                    }
                                }
                            }
                        }
                    }
                }
            } elseif (empty($_REQUEST['brand_id'])) {
                fn_set_notification('E', __('error'), __('error_brand_undefined'));
            } elseif (empty($file)) {
                fn_set_notification('E', __('error'), __('error_exim_no_file_uploaded'));
            }
            $ignored_products = array();
            if (!empty($ignore_list)) {
                foreach ($ignore_list as $i => $pcode) {
                    $ignored_products[$pcode] = !empty($total_data[$pcode]) ? $total_data[$pcode] : array('product' => __('missing'));
                }
            }
            
            FeaturesCache::generate(CART_LANGUAGE);
            
            Registry::get('view')->assign('out_of_stock', count($out_of_stock) + count($no_code));
            Registry::get('view')->assign('in_stock', count($in_stock));
            Registry::get('view')->assign('brand_id', $_REQUEST['brand_id']);
            Registry::get('view')->assign('calculate', true);
            Registry::get('view')->assign('total', count($total_data));
            Registry::get('view')->assign('ignore_list', $ignored_products);
            Registry::get('view')->assign('trash', $trash);
            Registry::get('view')->assign('missing_products', $missing_products);
            Registry::get('view')->assign('updated_products', $updated_products);
            Registry::get('view')->assign('broken_options_products', $broken_options_products);
            fn_set_notification('N', __('notice'), __('stocks_updated_successfully'));
        }
        
        Registry::get('view')->assign('brands', fn_development_get_brands());
        Registry::get('view')->display('addons/development/views/development/supplier_stocks.tpl');
        exit;
    }
    
    if ($mode == 'ignore_products') {
        if (!empty($_REQUEST['brand_id'])) {
            $ignore_list = db_get_field("SELECT ignore_list FROM ?:brand_ignore_list WHERE brand_id = ?i", $_REQUEST['brand_id']);
            $ignore_list = !empty($ignore_list) ? unserialize($ignore_list) : array();
            $ignore_list = array_merge($ignore_list, $_REQUEST['product_codes']);
            $_data = array(
                'brand_id' => $_REQUEST['brand_id'],
                'ignore_list' => serialize($ignore_list)
            );
            db_query("REPLACE INTO ?:brand_ignore_list ?e", $_data);
            fn_set_notification('N', __('notice'), __('added_to_ignore_list'));
        }
        exit;
    }
    
    if ($mode == 'watch_products') {
        if (!empty($_REQUEST['brand_id'])) {
            $ignore_list = db_get_field("SELECT ignore_list FROM ?:brand_ignore_list WHERE brand_id = ?i", $_REQUEST['brand_id']);
            $ignore_list = !empty($ignore_list) ? unserialize($ignore_list) : array();
            if (!empty($ignore_list)) {
                foreach ($ignore_list as $i => $pcode) {
                    if (in_array($pcode, $_REQUEST['product_codes'])) {
                        unset($ignore_list[$i]);
                    }
                }
                $_data = array(
                    'brand_id' => $_REQUEST['brand_id'],
                    'ignore_list' => serialize($ignore_list)
                );
                db_query("REPLACE INTO ?:brand_ignore_list ?e", $_data);
                fn_set_notification('N', __('notice'), __('updated_ignore_list'));
            }
        }
        exit;
    }
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

} elseif ($mode == 'supplier_stocks') {

    Registry::get('view')->assign('brands', fn_development_get_brands());
    
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
} elseif ($mode == 'fix_tracking') {
    $ids = db_get_fields("SELECT DISTINCT(product_id) FROM ?:product_options WHERE inventory = 'Y'");
    if (!empty($ids)) {
        db_query("UPDATE ?:products SET tracking = 'O' WHERE product_id IN (?n)", $ids);
    }
    exit;
}

function fn_normalize_string($string)
{
    if (preg_match("/\d,\d/", $string, $matches)) {
        $rplc = str_replace(',', '.', $matches[0]);
        $string = str_replace($matches[0], $rplc, $string);
    }
    
    return $string;
}

function fn_format_variant_name($variant_name)
{
    $variant_name = fn_strtolower($variant_name);
    $variant_name = preg_replace('/ /', '', $variant_name);
    $variant_name = preg_replace('/:/', '', $variant_name);
    $variant_name = preg_replace('/[\+\-]/', '/', $variant_name);
    $variant_name = preg_replace('/ё/', 'е', $variant_name);
    $variant_name = preg_replace('/голубой/', 'синий', $variant_name);
    $variant_name = preg_replace('/черно/', 'черный', $variant_name);
    $variant_name = preg_replace('/желто/', 'желтый', $variant_name);
    fn_trim_helper($variant_name);
    return $variant_name;
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
            $trash_lines = array();
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
                    $trash_lines[] = array(
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
                    $trash_lines[] = array(
                        'line' => $line_it,
                        'data' => $row
                    );
                    continue;
                }
                $previous_row = $row;
            }

            return array($result, $trash_lines);
        } else {
            fn_set_notification('E', __('error'), __('error_exim_cant_open_file'));

            return false;
        }
    } else {
        fn_set_notification('E', __('error'), __('error_exim_file_doesnt_exist'));

        return false;
    }
}
