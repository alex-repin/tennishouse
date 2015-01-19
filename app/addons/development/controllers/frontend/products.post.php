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
    $features = array();
    if (!empty($product['product_features'])) {
        foreach ($product['product_features'] as $i => $f_data) {
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
    $_SESSION['product_category'] = $product['main_category'];
    $_SESSION['product_features'] = $features;
    $_SESSION['category_type'] = $product['category_type'];

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
            if ($block_data['properties']['template'] == 'addons/development/blocks/products/products_scroller_capture.tpl') {
                $block_tabs['tabs']['block_tab_' . $block_data['block_id']] = array(
                    'title' => $block_data['name'],
                    'js' => true
                );
            }
        }
    }
    Registry::get('view')->assign('block_tabs', $block_tabs);
}