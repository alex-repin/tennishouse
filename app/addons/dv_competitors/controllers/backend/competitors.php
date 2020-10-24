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

$_REQUEST['competitor_id'] = empty($_REQUEST['competitor_id']) ? 0 : $_REQUEST['competitor_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Define trusted variables that shouldn't be stripped
    fn_trusted_vars (
        'competitor_data',
        'competitors_data'
    );

    //
    // Create/update competitor
    //
    if ($mode == 'update') {

        $competitor_id = fn_update_competitor($_REQUEST['competitor_data'], $_REQUEST['competitor_id']);

        if (!empty($competitor_id)) {
            $suffix = ".update?competitor_id=$competitor_id" . (!empty($_REQUEST['competitor_data']['block_id']) ? "&selected_block_id=" . $_REQUEST['competitor_data']['block_id'] : "");
        } else {
            $suffix = '.manage';
        }
    }

    //
    // Processing deleting of multiple competitor elements
    //
    if ($mode == 'm_delete') {

        if (isset($_REQUEST['competitor_ids'])) {
            foreach ($_REQUEST['competitor_ids'] as $v) {
                if (fn_allowed_for('MULTIVENDOR') || (fn_allowed_for('ULTIMATE') && fn_check_company_id('competitors', 'competitor_id', $v))) {
                    fn_delete_competitor($v);
                }
            }
        }

        unset($_SESSION['competitor_ids']);

        fn_set_notification('N', __('notice'), __('text_competitors_have_been_deleted'));
        $suffix = ".manage";
    }

    if ($mode == 'm_update') {
        if (!empty($_REQUEST['competitors_data'])) {
            foreach ($_REQUEST['competitors_data'] as $k => $v) {
                fn_update_competitor($v, $k);
            }
        }
        $suffix = ".manage";
    }

    if ($mode == 'eqialize_prices') {
        if (!empty($_REQUEST['product_ids'])) {
            $result = $data = array();
            foreach ($_REQUEST['product_ids'] as $pair) {
                $_pair = explode(',', $pair);
                $result[$_pair[0]] = $_pair[1];
            }
            if (!empty($action) && $action == 'discounts') {
                $prices = db_get_hash_single_array("SELECT product_id, price FROM ?:product_prices WHERE product_id IN (?n) AND lower_limit = '1'", array('product_id', 'price'), array_keys($result));
                if (!empty($prices)) {
                    foreach ($prices as $prod_id => $prc) {
                        db_query("UPDATE ?:products SET list_price = IF (list_price > ?i, list_price, ?i) WHERE product_id = ?i", $prc, $prc, $prod_id);
                    }
                }
            }
            $price = db_get_hash_single_array("SELECT item_id, price FROM ?:competitive_prices WHERE item_id IN (?n)", array('item_id', 'price'), array_values($result));
            foreach ($result as $product_id => $item_id) {
                $data[] = array(
                    'product_id' => $product_id,
                    'price' => $price[$item_id],
                    'lower_limit' => 1,
                    'usergroup_id' => 0
                );
            }
            db_query("REPLACE INTO ?:product_prices ?m", $data);
        }

        $suffix = ".prices";
    }

    if ($mode == 'add_pairs') {

        if (!empty($_REQUEST['pairs'])) {
            $data = $to_delete = array();
            foreach ($_REQUEST['pairs'] as $product_id => $_dt) {
                if (!empty($_dt['obj_id'])) {
                    if ($item_id == '-') {
                        $to_delete[] = $product_id;
                    } elseif (!empty($_dt['c_id'])) {
                        $data[] = array(
                            'competitive_id' => $_dt['obj_id'],
                            'product_id' => $product_id,
                            'competitor_id' => $_dt['c_id']
                        );
                    }
                }
            }
            if (!empty($to_delete)) {
                db_query("DELETE FROM ?:competitive_pairs WHERE product_id IN (?n)", $to_delete);
            }
            if (!empty($data)) {
                db_query("REPLACE INTO ?:competitive_pairs ?m", $data);
            }
        }
        $suffix = ".prices?mode=" . $_REQUEST['mode'];
    }

    if ($mode == 'autocomplete_cproducts') {

        if (!empty($_REQUEST['q']) && !empty($_REQUEST['c_id'])) {

            $params['q'] = trim($_REQUEST['q']);
            $pieces = fn_explode(' ', $params['q']);
            $search_type = ' AND ';

            foreach ($pieces as $piece) {
                if (strlen($piece) == 0) {
                    continue;
                }

                $_condition[] = db_quote("name LIKE ?l", '%' . $piece . '%');
            }

            $_cond = implode($search_type, $_condition);
            $_cond .= db_quote(" AND competitor_id = ?i", $_REQUEST['c_id']);

            $products = db_get_array("SELECT * FROM ?:competitive_prices WHERE $_cond ORDER BY item_id DESC");

            Registry::get('view')->assign('results', $products);
            Registry::get('view')->assign('product_id', $_REQUEST['id']);
            Registry::get('view')->display('addons/dv_competitors/views/competitors/prices_results.tpl');
            exit();
        }
    }

    return array(CONTROLLER_STATUS_OK, "competitors$suffix");
}

//
// 'Add new competitor' page
//
if ($mode == 'add') {

    // [Page sections]
    Registry::set('navigation.tabs', array (
        'detailed' => array (
            'title' => __('general'),
            'js' => true
        ),
    ));
    // [/Page sections]

//
// 'competitor update' page
//
} elseif ($mode == 'update') {

    $competitor_id = $_REQUEST['competitor_id'];
    // Get current competitor data
    $competitor_data = fn_get_competitor_data($competitor_id);

    if (empty($competitor_data)) {
        return array(CONTROLLER_STATUS_NO_PAGE);
    }


    // [Page sections]
    $tabs = array (
        'detailed' => array (
            'title' => __('general'),
            'js' => true
        ),
    );

    Registry::set('navigation.tabs', $tabs);
    // [/Page sections]
    Registry::get('view')->assign('competitor_data', $competitor_data);

}
//
// Delete competitor
//
elseif ($mode == 'delete') {

    if (!empty($_REQUEST['competitor_id'])) {
        fn_delete_competitor($_REQUEST['competitor_id']);
    }

    fn_set_notification('N', __('notice'), __('text_competitor_has_been_deleted'));

    return array(CONTROLLER_STATUS_REDIRECT, "competitors.manage");

//
// 'Management' page
//
} elseif ($mode == 'manage') {

    $params = $_REQUEST;
    list($competitors, $search) = fn_get_competitors($params);

    Registry::get('view')->assign('competitors', $competitors);
    Registry::get('view')->assign('search', $search);

} elseif ($mode == 'prices') {

    $cp_mode = empty($_REQUEST['mode']) ? 'D' : $_REQUEST['mode'];

    if ($cp_mode == 'N') {
        $params = array(
            'hide_out_of_stock' => 'Y',
            'competition' => 'N',
            'sort_by' => 'price',
            'sort_order' => 'desc'
        );
    } elseif ($cp_mode == 'D') {
        $params = array(
            'hide_out_of_stock' => 'Y',
            'competition' => 'D'
        );
    } else {
        $params = array(
            'competition' => 'A',
            'hide_out_of_stock' => 'Y'
        );
    }

    list($products, $search) = fn_get_products($params);

    $result = $_result = array();
    if (!empty($products)) {
        foreach ($products as $product) {
            if (empty($result[$product['main_category']])) {
                $result[$product['main_category']]['category_id'] = $product['main_category'];
            }
            $result[$product['main_category']]['products'][] = $product;
        }
        $sorting = db_get_fields("SELECT category_id FROM ?:categories WHERE category_id IN (?n) ORDER BY parent_id, position", array_keys($result));
        foreach ($sorting as $cid) {
            $_result[$cid] = $result[$cid];
        }
    }
    list($competitors,) = fn_get_competitors();

    Registry::get('view')->assign('mode', $cp_mode);
    Registry::get('view')->assign('competitive_prices', $_result);
    Registry::get('view')->assign('competitors', $competitors);

}
