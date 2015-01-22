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
    private static $cache_levels = array(
        'lang_code',
        'feature_id',
        'type',
        'variant',
        'product_id'
    );
    
    public static function generate($lang_code = '')
    {
        $feature_values = db_get_array("SELECT * FROM ?:product_features_values");
        $result = array();
        self::formatResults($result, $feature_values, $lang_code);
        Memcached::instance()->set('features', $result, 'F');
    }

    public static function formatResults(&$result, $feature_values, $lang_code = '')
    {
        if (!empty($feature_values)) {
            foreach ($feature_values as $i => $feature_value) {
                if (empty($lang_code) || $feature_value['lang_code'] == $lang_code) {
                    if (!empty($feature_value['value_int']) && (empty($result[$feature_value['feature_id']][$feature_value['value_int']]) || !in_array($feature_value['product_id'], $result[$feature_value['feature_id']][$feature_value['value_int']]))) {
                        $result[$feature_value['lang_code']][$feature_value['feature_id']]['values'][$feature_value['value_int']][] = $feature_value['product_id'];
                    }
                    if (!empty($feature_value['variant_id']) && (empty($result[$feature_value['feature_id']][$feature_value['variant_id']]) || !in_array($feature_value['product_id'], $result[$feature_value['feature_id']][$feature_value['variant_id']]))) {
                        $result[$feature_value['lang_code']][$feature_value['feature_id']]['variants'][$feature_value['variant_id']][] = $feature_value['product_id'];
                    }
                }
            }
        }
    }
    
    public static function updateFeatureValueInt($feature_id, $old_value_int, $new_value_int, $lang_code)
    {
        $memcache_features = Memcached::instance()->get('features', 'F');
        if (!empty($memcache_features[$lang_code][$feature_id]['values'][$old_value_int])) {
            $tmp = $memcache_features[$lang_code][$feature_id]['values'][$old_value_int];
            unset($memcache_features[$lang_code][$feature_id]['values'][$old_value_int]);
            $memcache_features[$lang_code][$feature_id]['values'][(string)number_format($new_value_int, 2, '.', '')] = $tmp;
        }
        Memcached::instance()->set('features', $memcache_features, 'F');
    }
    
    public static function clearoutFeatures($params)
    {
        $memcache_features = Memcached::instance()->get('features', 'F');
        if (!empty($memcache_features)) {
            self::clearoutFeatureValues($params, $memcache_features);
        }
        Memcached::instance()->set('features', $memcache_features, 'F');
    }
    
    public static function clearoutFeatureValues($params, &$memcache_features, $level = 0)
    {
        if (!empty($memcache_features)) {
            foreach ($memcache_features as $i => $j) {
                if (!empty($params['delete'][self::$cache_levels[$level]]) && in_array($i, $params['delete'][self::$cache_levels[$level]])) {
                    unset($memcache_features[$i]);
                } elseif (empty($params['condition'][self::$cache_levels[$level]]) || (!empty($params['condition'][self::$cache_levels[$level]]) && in_array($i, $params['condition'][self::$cache_levels[$level]]))) {
                    if (is_array($j)) {
                        self::clearoutFeatureValues($params, $memcache_features[$i], $level + 1);
                    } elseif ((!empty($params['delete'][self::$cache_levels[$level]]) && in_array($j, $params['delete'][self::$cache_levels[$level]])) || (!empty($params['delete']['not_' . self::$cache_levels[$level]]) && !in_array($j, $params['delete']['not_' . self::$cache_levels[$level]]))) {
                        unset($memcache_features[$i]);
                    }
                }
            }
        }
    }
    
    public static function deleteFeature($feature_id)
    {
        $memcache_features = Memcached::instance()->get('features', 'F');
        if (!empty($memcache_features)) {
            $params = array(
                'delete' => array('feature_id' => array($feature_id))
            );
            self::clearoutFeatureValues($params, $memcache_features);
        }
        Memcached::instance()->set('features', $memcache_features, 'F');
    }
    
    public static function deleteVariants($variant_ids)
    {
        $memcache_features = Memcached::instance()->get('features', 'F');
        if (!empty($memcache_features)) {
            $value_ints = db_get_array("SELECT feature_id, value_int FROM ?:product_features_values WHERE variant_id IN (?n)", $variant_ids);
            if (!empty($value_ints)) {
                foreach ($value_ints as $i => $data) {
                    if (!empty($data['value_int'])) {
                        $params = array(
                            'delete' => array('variant' => array($data['value_int'])),
                            'condition' => array('feature_id' => array($data['feature_id']))
                        );
                        self::clearoutFeatureValues($params, $memcache_features);
                    }
                }
            }
            $params = array(
                'delete' => array('variant' => $variant_ids)
            );
            self::clearoutFeatureValues($params, $memcache_features);
        }
        Memcached::instance()->set('features', $memcache_features, 'F');
    }
    
    public static function deleteProduct($product_id)
    {
        $memcache_features = Memcached::instance()->get('features', 'F');
        if (!empty($memcache_features)) {
            $params = array(
                'delete' => array('product_id' => array($product_id))
            );
            self::clearoutFeatureValues($params, $memcache_features);
        }
        Memcached::instance()->set('features', $memcache_features, 'F');
    }
    
    public static function updateProductFeaturesValue($product_id, $features, $lang_code = CART_LANGUAGE)
    {
        $memcache_features = Memcached::instance()->get('features', 'F');
        if (!empty($memcache_features)) {
            $params = array(
                'delete' => array('product_id' => array($product_id))
            );
            self::clearoutFeatureValues($params, $memcache_features);
        }
        if (!empty($features)) {
            self::formatResults($memcache_features, $features, $lang_code);
        }
        Memcached::instance()->set('features', $memcache_features, 'F');
    }
    
    public static function getProductsConditions($features_condition, &$join, &$condition, $lang_code = CART_LANGUAGE)
    {
        $memcache_features = Memcached::instance()->get('features', 'F');
        if (!empty($memcache_features[$lang_code])) {
            $memcache_features = $memcache_features[$lang_code];
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
                $join .= db_quote(" LEFT JOIN ?:product_features_values AS feature_?i ON feature_?i.product_id = products.product_id AND feature_?i.feature_id = ?i AND feature_?i.lang_code = ?s", $feature_id, $feature_id, $feature_id, $feature_id, $feature_id, $lang_code);
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