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

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($mode == 'view') {
    $category_data = Registry::get('view')->gettemplatevars('category_data');
    if (empty($category_data['brand_id']) && !empty($category_data['parent_id'])) {
        $cat_ids = explode('/', $category_data['id_path']);
        $main_parent = reset($cat_ids);
        if (!empty($main_parent)) {
            $category_data['main_pair'] = fn_get_image_pairs($main_parent, 'category', 'M', true, true, CART_LANGUAGE);
        }
    } elseif (!empty($category_data['brand_id'])) {
        unset($category_data['main_pair']);
    }
    if (!empty($category_data['main_pair'])) {
        Registry::get('view')->assign('image_title', true);
    }
    $products = Registry::get('view')->gettemplatevars('products');
    $params = array (
        'category_id' => $category_data['category_id'],
        'visible' => true,
        'get_images' => true,
        'skip_filter' => true
    );
    list($subcategories, ) = fn_get_categories($params, CART_LANGUAGE);

    if (empty($subcategories) && empty($products)) {
        $cat_ids = explode('/', $category_data['id_path']);
        $main_parent = reset($cat_ids);
        if (!empty($main_parent)) {
            return array(CONTROLLER_STATUS_REDIRECT, 'categories.view?category_id=' . $main_parent);
        }
    }
    
    if (!empty($category_data['categorize_by_feature_id']) && !empty($products)) {
        $feature_categorization = array();
        $feature_category = fn_get_product_feature_data($category_data['categorize_by_feature_id'], true);
        if (!empty($feature_category['variants'])) {
            foreach ($products as $i => $product) {
                if (!empty($product['category_feature_id'])) {
                    $feature_categorization[$product['category_feature_id']][] = $product;
                } else {
                    $feature_categorization['other'][] = $product;
                }
            }
            Registry::get('view')->assign('feature_categorization', $feature_categorization);
            Registry::get('view')->assign('feature_category', $feature_category);
        }
    }
    
    Registry::get('view')->assign('subcategories', $subcategories);
    Registry::get('view')->assign('category_data', $category_data);
}
