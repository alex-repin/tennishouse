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
use Tygh\BlockManager\Block;
use Tygh\BlockManager\RenderManager;
use Tygh\BlockManager\SchemesManager;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($mode == 'view') {

    $product = Registry::get('view')->gettemplatevars('product');
    if (__('product_page_title_' . $product['category_type']) != '_product_page_title_' . strtolower($product['category_type']) && __('product_page_title_' . $product['category_type']) != '') {
        Registry::get('view')->assign('page_title', $product['product'] . ' – ' . __('product_page_title_' . $product['category_type']));
    } else {
        Registry::get('view')->assign('page_title', $product['product'] . ' – ' . __('product_page_title'));
    }
    $features = array();
    if (!empty($product['product_features'])) {
        foreach ($product['product_features'] as $i => $f_data) {
            if ($f_data['feature_type'] == 'M' && !empty($f_data['variants'])) {
                foreach ($f_data['variants'] as $j => $var) {
                    if (!empty($var['selected'])) {
                        $features[$f_data['feature_id']]['variants'][] = array(
                            'variant_id' => $var['variant_id'],
                            'variant_name' => $var['variant']
                        );
                    }
                }
            } elseif (!empty($f_data['variant_id'])) {
                $features[$f_data['feature_id']] = array(
                    'variant_id' => $f_data['variant_id'],
                    'variant_name' => $f_data['variants'][$f_data['variant_id']]['variant']
                );
            } else {
                $features[$f_data['feature_id']] = array(
                    'value' => $f_data['value']
                );
            }
        }
        $gender = '';
        $brand_size_chart = false;
        if (!empty($product['product_features'][CLOTHES_GENDER_FEATURE_ID])) {
            $variant_id = $product['product_features'][CLOTHES_GENDER_FEATURE_ID]['variant_id'];
            if ($variant_id == C_GENDER_M_FV_ID) {
                $gender = 'mens';
            } elseif ($variant_id == C_GENDER_W_FV_ID) {
                $gender = 'womens';
            }
            $product['size_chart'] = $product['product_features'][CLOTHES_GENDER_FEATURE_ID]['variants'][$variant_id]['size_chart'];
            if (!empty($product['product_features'][CLOTHES_GENDER_FEATURE_ID]['variants'][$variant_id]['variant_code'])) {
                fn_set_store_gender_mode($product['product_features'][CLOTHES_GENDER_FEATURE_ID]['variants'][$variant_id]['variant_code']);
            }
        } elseif (!empty($product['product_features'][SHOES_GENDER_FEATURE_ID])) {
            $variant_id = $product['product_features'][SHOES_GENDER_FEATURE_ID]['variant_id'];
            if ($variant_id == S_GENDER_M_FV_ID) {
                $gender = 'mens';
            } elseif ($variant_id == S_GENDER_W_FV_ID) {
                $gender = 'womens';
            }
            $product['size_chart'] = $product['product_features'][SHOES_GENDER_FEATURE_ID]['variants'][$variant_id]['size_chart'];
            if (!empty($product['product_features'][SHOES_GENDER_FEATURE_ID]['variants'][$variant_id]['variant_code'])) {
                fn_set_store_gender_mode($product['product_features'][SHOES_GENDER_FEATURE_ID]['variants'][$variant_id]['variant_code']);
            }
        } elseif (!empty($product['product_features'][TYPE_FEATURE_ID])) {
            $variant_id = $product['product_features'][TYPE_FEATURE_ID]['variant_id'];
            if (!empty($product['product_features'][TYPE_FEATURE_ID]['variants'][$variant_id]['variant_code'])) {
                fn_set_store_gender_mode($product['product_features'][TYPE_FEATURE_ID]['variants'][$variant_id]['variant_code']);
            }
        }
        if (!empty($product['header_features'][BRAND_FEATURE_ID])) {
            $cat_type = '';
            $_SESSION['product_brand'] = $brand_id = $product['header_features'][BRAND_FEATURE_ID]['variant_id'];
            if ($product['category_type'] == 'A') {
                $cat_type = 'clothes';
            } elseif ($product['category_type'] == 'S') {
                $cat_type = 'shoes';
            }
            if (!empty($product['header_features'][BRAND_FEATURE_ID]['variants'][$brand_id][$gender . '_' . $cat_type . '_size_chart'])) {
                $brand_size_chart = true;
                $title = $product['header_features'][BRAND_FEATURE_ID]['variants'][$brand_id]['variant'];
                $product['size_chart'] = $product['header_features'][BRAND_FEATURE_ID]['variants'][$brand_id][$gender . '_' . $cat_type . '_size_chart'];
            }
        }
    }
    if (!empty($product['size_chart'])) {
        Registry::get('view')->assign('product', $product);
        if ($brand_size_chart) {
            $_tabs = Registry::get('navigation.tabs');
            $_tabs['size_chart']['title'] .= ' ' . $title;
            Registry::set('navigation.tabs', $_tabs);
        }
    } else {
        $tabs = Registry::get('view')->gettemplatevars('tabs');
        $_tabs = Registry::get('navigation.tabs');
        unset($tabs[SIZE_CHART_TAB_ID]);
        unset($_tabs['size_chart']);
        Registry::set('navigation.tabs', $_tabs);
        Registry::get('view')->assign('tabs', $tabs);
    }
    if (!empty($product['header_features'])) {
        foreach ($product['header_features'] as $i => $f_data) {
            if (!empty($f_data['variant_id'])) {
                $features[$f_data['feature_id']] = array(
                    'variant_id' => $f_data['variant_id'],
                    'variant_name' => $f_data['variants'][$f_data['variant_id']]['variant']
                );
            } else {
                $features[$f_data['feature_id']] = array(
                    'value' => $f_data['value']
                );
            }
        }
    }
    $inters = array_intersect(array(PRO_RACKETS_CATEGORY_ID, CLUB_RACKETS_CATEGORY_ID, BEGINNERS_RACKETS_CATEGORY_ID, KIDS_RACKETS_CATEGORY_ID), $product['category_ids']);
    $_SESSION['product_category'] = (!empty($inters[0])) ? $inters[0] : $product['main_category'];
    $_SESSION['main_product_category'] = $product['category_main_id'];
    $_SESSION['product_features'] = $features;
    $_SESSION['category_type'] = $product['category_type'];
    
    if ($product['category_main_id'] == RACKETS_CATEGORY_ID) {
        Registry::get('view')->assign('show_racket_finder', true);
    }

    $blocks = Block::instance()->getList(
        array('?:bm_blocks.*', '?:bm_blocks_descriptions.*'),
        array(PRODUCT_BLOCK_TABS_GRID_ID),
        array(),
        '',
        " AND ?:bm_snapping.status = 'A' "
    );
    $block_tabs = array();
    if (!empty($blocks[PRODUCT_BLOCK_TABS_GRID_ID])) {
        shuffle($blocks[PRODUCT_BLOCK_TABS_GRID_ID]);
        foreach ($blocks[PRODUCT_BLOCK_TABS_GRID_ID] as $i => $block_data) {
            if ($block_data['properties']['template'] == 'addons/development/blocks/products/th_products_block.tpl') {
                $block_tabs['tabs']['block_tab_' . $block_data['block_id']] = array(
                    'title' => $block_data['name'],
                    'js' => true
                );
            }
        }
    }
    Registry::get('view')->assign('block_tabs', $block_tabs);
}