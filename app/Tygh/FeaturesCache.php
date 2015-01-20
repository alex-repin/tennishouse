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

namespace Tygh;

use Tygh\Registry;
use Tygh\Memcached;

class FeaturesCache
{
    public static function generate()
    {
        $feature_values = db_get_array("SELECT * FROM ?:product_features_values");
        $result = array();
        if (!empty($feature_values)) {
            foreach ($feature_values as $i => $feature_value) {
                if ($feature_value['lang_code'] == CART_LANGUAGE) {
                    if (!empty($feature_value['value_int']) && (empty($result[$feature_value['feature_id']][$feature_value['value_int']]) || !in_array($feature_value['product_id'], $result[$feature_value['feature_id']][$feature_value['value_int']]))) {
                        $result[$feature_value['feature_id']]['values'][$feature_value['value_int']][] = $feature_value['product_id'];
                    }
                    if (!empty($feature_value['variant_id']) && (empty($result[$feature_value['feature_id']][$feature_value['variant_id']]) || !in_array($feature_value['product_id'], $result[$feature_value['feature_id']][$feature_value['variant_id']]))) {
                        $result[$feature_value['feature_id']]['variants'][$feature_value['variant_id']][] = $feature_value['product_id'];
                    }
                }
            }
        }
        Memcached::instance()->set('features', $result, 'F');
    }

    public static function getProductsConditions($features_condition, &$join, &$condition)
    {
        $memcache_features = Memcached::instance()->get('features', 'F');
        if (!empty($memcache_features)) {
            $product_ids = array();
            foreach ($features_condition as $feature_id => $feature_data) {
                if (!empty($memcache_features[$feature_id])) {
                    if (!empty($feature_data['variant_id']) && !empty($memcache_features[$feature_id]['variants'][$feature_data['variant_id']])) {
                        $product_ids[] = $memcache_features[$feature_id]['variants'][$feature_data['variant_id']];
                    } elseif (!empty($feature_data['value']) && !empty($memcache_features[$feature_id]['values'][$feature_data['value']])) {
                        $product_ids[] = $memcache_features[$feature_id]['values'][$feature_data['value']];
                    } else {
                        $_prod_ids = array();
                        if (!empty($feature_data['not_variant']) && !empty($memcache_features[$feature_id]['variants'])) {
                            foreach ($memcache_features[$feature_id]['variants'] as $variant_id => $_product_ids) {
                                if (!empty($_product_ids) && $feature_data['not_variant'] != $variant_id) {
                                    $_prod_ids = array_merge($_prod_ids, $_product_ids);
                                }
                            }
                        } elseif ((!empty($feature_data['not_value']) || !empty($feature_data['min_value']) || !empty($feature_data['max_value'])) && !empty($memcache_features[$feature_id]['values'])) {
                            foreach ($memcache_features[$feature_id]['values'] as $variant_id => $_product_ids) {
                                if (!empty($_product_ids)) {
                                    $use = true;
                                    if (!empty($feature_data['min_value']) && $variant_id < $feature_data['min_value']) {
                                        $use = false;
                                    }
                                    if (!empty($feature_data['max_value']) && $variant_id > $feature_data['max_value']) {
                                        $use = false;
                                    }
                                    if ($use) {
                                        $_prod_ids = array_merge($_prod_ids, $_product_ids);
                                    }
                                }
                            }
                        }
                        $product_ids[] = $_prod_ids;
                    }
                }
            }
            if (!empty($product_ids)) {
                $result = array_shift($product_ids);
                foreach ($product_ids as $i => $pr_ids) {
                    $result = array_intersect($result, $pr_ids);
                }
                $product_ids = $result;
            }
            $condition .= db_quote(" AND products.product_id IN (?n) ", $product_ids);
        } else {
            foreach ($features_condition as $feature_id => $feature_data) {
                $join .= db_quote(" LEFT JOIN ?:product_features_values AS feature_?i ON feature_?i.product_id = products.product_id AND feature_?i.feature_id = ?i AND feature_?i.lang_code = ?s", $feature_id, $feature_id, $feature_id, $feature_id, $feature_id, CART_LANGUAGE);
                if (!empty($feature_data['variant_id'])) {
                    $condition .= db_quote(" AND feature_?i.variant_id = ?i ", $feature_id, $feature_data['variant_id']);
                } elseif (!empty($feature_data['value'])) {
                    $condition .= db_quote(" AND feature_?i.value = ?i ", $feature_id, $feature_data['value']);
                } elseif (!empty($feature_data['not_variant'])) {
                    $condition .= db_quote(" AND feature_?i.variant_id != ?i ", $feature_id, $feature_data['not_variant']);
                } elseif (!empty($feature_data['not_value'])) {
                    $condition .= db_quote(" AND feature_?i.value != ?i ", $feature_id, $feature_data['not_value']);
                } elseif (!empty($feature_data['min_value']) || !empty($feature_data['max_value'])) {
                    if (!empty($feature_data['min_value'])) {
                        $condition .= db_quote(" AND feature_?i.value_int >= ?d ", $feature_id, $feature_data['min_value']);
                    }
                    if (!empty($feature_data['max_value'])) {
                        $condition .= db_quote(" AND feature_?i.value_int <= ?d ", $feature_id, $feature_data['max_value']);
                    }
                }
            }
        }
    }
}