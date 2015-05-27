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

$_REQUEST['category_id'] = empty($_REQUEST['category_id']) ? 0 : $_REQUEST['category_id'];

if ($mode == 'catalog') {
    fn_add_breadcrumb(__('catalog'));

    $root_categories = fn_get_subcategories(0);

    foreach ($root_categories as $k => $v) {
        $root_categories[$k]['main_pair'] = fn_get_image_pairs($v['category_id'], 'category', 'M');
    }

    Registry::get('view')->assign('root_categories', $root_categories);

} elseif ($mode == 'view') {

    $_statuses = array('A', 'H');
    $_condition = fn_get_localizations_condition('localization', true);
    $preview = fn_is_preview_action($auth, $_REQUEST);

    if (!$preview) {
        $_condition .= ' AND (' . fn_find_array_in_set($auth['usergroup_ids'], 'usergroup_ids', true) . ')';
        $_condition .= db_quote(' AND status IN (?a)', $_statuses);
    }

    if (fn_allowed_for('ULTIMATE')) {
        $_condition .= fn_get_company_condition('?:categories.company_id');
    }

    $category_exists = db_get_field(
        "SELECT category_id FROM ?:categories WHERE category_id = ?i ?p",
        $_REQUEST['category_id'],
        $_condition
    );

    if (!empty($category_exists)) {
        // [tennishouse]

        if (!empty($_REQUEST['features_hash'])) {
            $_REQUEST['features_hash'] = fn_correct_features_hash($_REQUEST['features_hash']);
        }

        // Save current url to session for 'Continue shopping' button
        $_SESSION['continue_url'] = "categories.view?category_id=$_REQUEST[category_id]";

        // Save current category id to session
        $_SESSION['current_category_id'] = $_SESSION['breadcrumb_category_id'] = $_REQUEST['category_id'];

        // Get subcategories list for current category
        Registry::get('view')->assign('subcategories', fn_get_subcategories($_REQUEST['category_id']));

        // Get full data for current category
        $category_data = fn_get_category_data($_REQUEST['category_id'], CART_LANGUAGE, '*', true, false, $preview);

        $category_parent_ids = fn_explode('/', $category_data['id_path']);
        $main_parent_id = reset($category_parent_ids);
        array_pop($category_parent_ids);

        if (!empty($category_data['meta_description']) || !empty($category_data['meta_keywords'])) {
            Registry::get('view')->assign('meta_description', $category_data['meta_description']);
            Registry::get('view')->assign('meta_keywords', $category_data['meta_keywords']);
        }

        $params = $_REQUEST;

        if (!empty($_REQUEST['items_per_page'])) {
            $_SESSION['items_per_page'] = $_REQUEST['items_per_page'];
        } elseif (!empty($_SESSION['items_per_page'])) {
            $params['items_per_page'] = $_SESSION['items_per_page'];
        }

        $params['cid'] = $_REQUEST['category_id'];
        $params['extend'] = array('categories', 'description');
        $params['subcats'] = '';
        if (!empty($category_data['tabs_categorization'])) {
            $params['tabs_categorization'] = $category_data['tabs_categorization'];
        }
        if (!empty($category_data['subtabs_categorization'])) {
            $params['subtabs_categorization'] = $category_data['subtabs_categorization'];
        }
        if (!empty($category_data['sections_categorization'])) {
            $params['sections_categorization'] = $category_data['sections_categorization'];
        }
        if (Registry::get('settings.General.show_products_from_subcategories') == 'Y') {
            $params['subcats'] = 'Y';
        }

        list($products, $search) = fn_get_products($params, Registry::get('settings.Appearance.products_per_page'), CART_LANGUAGE);

        if (isset($search['page']) && ($search['page'] > 1) && empty($products)) {
            return array(CONTROLLER_STATUS_NO_PAGE);
        }

        fn_gather_additional_products_data($products, array(
            'get_icon' => true,
            'get_detailed' => true,
            'get_additional' => true,
            'get_options' => true,
            'get_discounts' => true,
            'get_features' => false
        ));
        if (!empty($products)) {
            if (!empty($category_data['tabs_categorization']) && (empty($category_data['brand']) || $category_data['brand']['feature_id'] != $category_data['tabs_categorization'])) {
                $tb_feature = fn_get_product_feature_data($category_data['tabs_categorization'], true);
                if (!empty($tb_feature['variants'])) {
                    $tabs_categorization = array();
                    foreach ($products as $i => $product) {
                        if (!empty($product['tabs_categorization'])) {
                            $tabs_categorization[$product['tabs_categorization']][] = $product;
                        } else {
                            $tabs_categorization['other'][] = $product;
                        }
                    }
                    foreach ($tb_feature['variants'] as $key => $vr_data) {
                        if (empty($tabs_categorization[$key])) {
                            unset($tb_feature['variants'][$key]);
                        }
                    }
                    if (!empty($tabs_categorization['other'])) {
                        $tb_feature['variants']['other'] = array('variant' => __("other"));
                    }
                    if (!empty($_REQUEST['tc_id'])) {
                        $_SESSION['tc_id'][$main_parent_id] = $params['tc_id'] = $_REQUEST['tc_id'];
                        // Store gender mode
                        if (!empty($tb_feature['variants'][$_REQUEST['tc_id']]['variant_code'])) {
                            fn_set_store_gender_mode($tb_feature['variants'][$_REQUEST['tc_id']]['variant_code']);
                        }
                    }
                    if (empty($params['tc_id'])) {
                        $gender_mode = fn_get_store_gender_mode();
                        if (!empty($gender_mode)) {
                            foreach ($tb_feature['variants'] as $j => $vt_data) {
                                if (!empty($vt_data['variant_code']) && ($vt_data['variant_code'] == $gender_mode || ($gender_mode == 'K' && in_array($vt_data['variant_code'], array('B', 'G'))) || (in_array($gender_mode, array('B', 'G')) && $vt_data['variant_code'] == 'K')) && !empty($tabs_categorization[$vt_data['variant_id']])) {
                                    $params['tc_id'] = $_SESSION['tc_id'][$main_parent_id] = $vt_data['variant_id'];
                                    fn_set_store_gender_mode($vt_data['variant_code']);
                                    break;
                                }
                            }
                        }
                        if (empty($params['tc_id'])) {
                            if (!empty($_SESSION['tc_id'][$main_parent_id]) && !empty($tabs_categorization[$_SESSION['tc_id'][$main_parent_id]])) {
                                $params['tc_id'] = $_SESSION['tc_id'][$main_parent_id];
                            } elseif (!empty($tb_feature['variants'])) {
                                $keys = array_keys($tb_feature['variants']);
                                $params['tc_id'] = $_SESSION['tc_id'][$main_parent_id] = reset($keys);
                            }
                            if (!empty($tb_feature['variants'][$params['tc_id']]['variant_code'])) {
                                fn_set_store_gender_mode($tb_feature['variants'][$params['tc_id']]['variant_code']);
                            }
                        }
                    }
                    if (!empty($params['tc_id']) && !empty($tabs_categorization[$params['tc_id']])) {
                        $products = $tabs_categorization[$params['tc_id']];
                    }
        
                    Registry::get('view')->assign('tb_feature', $tb_feature);
                    Registry::get('view')->assign('active_tab', $params['tc_id']);
                }
            }

            if (!empty($category_data['subtabs_categorization']) && (empty($category_data['brand']) || $category_data['brand']['feature_id'] != $category_data['subtabs_categorization'])) {
                $stb_feature = fn_get_product_feature_data($category_data['subtabs_categorization'], true, true);
                if (!empty($stb_feature['variants'])) {
                    $subtabs_categorization = array();
                    foreach ($products as $i => $product) {
                        if (!empty($product['subtabs_categorization'])) {
                            $subtabs_categorization[$product['subtabs_categorization']][] = $product;
                        } else {
                            $subtabs_categorization['other'][] = $product;
                        }
                    }
                    foreach ($stb_feature['variants'] as $key => $vr_data) {
                        if (empty($subtabs_categorization[$key])) {
                            unset($stb_feature['variants'][$key]);
                        }
                    }
                    if (!empty($subtabs_categorization['other'])) {
                        $stb_feature['variants']['other'] = array('variant' => __("other"));
                    }
                    if (!empty($_REQUEST['stc_id'])) {
                        $_SESSION['stc_id'][$main_parent_id] = $_REQUEST['stc_id'];
                    }
                    if (empty($params['stc_id'])) {
                        if (!empty($_SESSION['stc_id'][$main_parent_id]) && !empty($subtabs_categorization[$_SESSION['stc_id'][$main_parent_id]])) {
                            $params['stc_id'] = $_SESSION['stc_id'][$main_parent_id];
                        } elseif (!empty($stb_feature['variants'])) {
                            $params['stc_id'] = $_SESSION['stc_id'][$main_parent_id] = reset(array_keys($stb_feature['variants']));
                        }
                    }
                    if (!empty($params['stc_id']) && !empty($subtabs_categorization[$params['stc_id']])) {
                        $products = $subtabs_categorization[$params['stc_id']];
                    }
        
                    Registry::get('view')->assign('stb_feature', $stb_feature);
                    Registry::get('view')->assign('active_subtab', $params['stc_id']);
                }
            }
            if (!empty($category_data['sections_categorization'])) {
                $sections_categorization = array();
                $sc_feature = fn_get_product_feature_data($category_data['sections_categorization'], true);
                if (!empty($sc_feature['variants'])) {
                    foreach ($products as $i => $product) {
                        if (!empty($product['sections_categorization'])) {
                            $sections_categorization[$product['sections_categorization']][] = $product;
                        } else {
                            $sections_categorization['other'][] = $product;
                        }
                    }
                    Registry::get('view')->assign('sections_categorization', $sections_categorization);
                    Registry::get('view')->assign('sc_feature', $sc_feature);
                }
            }
        }
        // [tennishouse]
    
        $show_no_products_block = ((!empty($params['features_hash']) || !empty($params['features_condition'])) && !$products);
        Registry::get('view')->assign('show_no_products_block', $show_no_products_block);

        $selected_layout = fn_get_products_layout($_REQUEST);
        Registry::get('view')->assign('show_qty', true);
        Registry::get('view')->assign('products', $products);
        Registry::get('view')->assign('search', $search);
        Registry::get('view')->assign('selected_layout', $selected_layout);

        Registry::get('view')->assign('category_data', $category_data);

        // If page title for this category is exist than assign it to template
        if (!empty($category_data['page_title'])) {
             Registry::get('view')->assign('page_title', $category_data['page_title']);
        }

        fn_define('FILTER_CUSTOM_ADVANCED', true); // this constant means that extended filtering should be stayed on the same page

        list($filters) = fn_get_filters_products_count($_REQUEST);
        Registry::get('view')->assign('filter_features', $filters);

        // [Breadcrumbs]
        if (!empty($category_parent_ids)) {
            Registry::set('runtime.active_category_ids', $category_parent_ids);
            $cats = fn_get_category_name($category_parent_ids);
            $display_subheader = true;
            foreach ($category_parent_ids as $i => $c_id) {
                if ($i == 0 && fn_display_subheaders($c_id)) {
                    $display_subheader = false;
                }
                if ($i != 1 || $display_subheader) {
                    fn_add_breadcrumb($cats[$c_id], "categories.view?category_id=$c_id");
                } else {
                    fn_add_breadcrumb($cats[$c_id]);
                }
            }
        }

        fn_add_breadcrumb($category_data['category'], (empty($_REQUEST['features_hash']) && empty($_REQUEST['advanced_filter'])) ? '' : "categories.view?category_id=$_REQUEST[category_id]");

        if (!empty($params['features_hash'])) {
            fn_add_filter_ranges_breadcrumbs($params, "categories.view?category_id=$_REQUEST[category_id]");
        } elseif (!empty($_REQUEST['advanced_filter'])) {
            fn_add_breadcrumb(__('advanced_filter'));
        }
        // [/Breadcrumbs]
    } else {
        return array(CONTROLLER_STATUS_NO_PAGE);
    }

} elseif ($mode == 'picker') {

    $category_count = db_get_field("SELECT COUNT(*) FROM ?:categories");
    if ($category_count < CATEGORY_THRESHOLD) {
        $params = array (
            'simple' => false
        );
         list($categories_tree, ) = fn_get_categories($params);
         Registry::get('view')->assign('show_all', true);
    } else {
        $params = array (
            'category_id' => $_REQUEST['category_id'],
            'current_category_id' => $_REQUEST['category_id'],
            'visible' => true,
            'simple' => false
        );
        list($categories_tree, ) = fn_get_categories($params);
    }

    if (!empty($_REQUEST['root'])) {
        array_unshift($categories_tree, array('category_id' => 0, 'category' => $_REQUEST['root']));
    }
    Registry::get('view')->assign('categories_tree', $categories_tree);
    if ($category_count < CATEGORY_SHOW_ALL) {
        Registry::get('view')->assign('expand_all', true);
    }
    if (defined('AJAX_REQUEST')) {
        Registry::get('view')->assign('category_id', $_REQUEST['category_id']);
    }
    Registry::get('view')->display('pickers/categories/picker_contents.tpl');
    exit;
}
