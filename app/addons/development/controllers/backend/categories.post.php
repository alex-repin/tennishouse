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

if ($mode == 'update' || $mode == 'add') {

    $params = array(
        'variants' => false,
        'plain' => false,
    );

    list($filter_features) = fn_get_product_features($params, 0, DESCR_SL);
    Registry::get('view')->assign('filter_features', $filter_features);
    $section_features = array();
    foreach ($filter_features as $i => $feature) {
        if ($feature['feature_type'] != 'G') {
            $section_features[$feature['feature_id']] = (!empty($feature['group_description']) ? $feature['group_description'] . ': ' : '') . $feature['description'];
        } elseif (!empty($feature['subfeatures'])) {
            foreach ($feature['subfeatures'] as $j => $subfeature) {
                $section_features[$subfeature['feature_id']] = (!empty($subfeature['group_description']) ? $subfeature['group_description'] . ': ' : '') . $subfeature['description'];
            }
        }
    }
    $category_data = Registry::get('view')->gettemplatevars('category_data');
    if (!empty($category_data['sections_categorization'])) {
        $f_ids = array();
        foreach ($category_data['sections_categorization'] as $j => $f_id) {
            $f_ids[$f_id] = $section_features[$f_id];
            unset($section_features[$f_id]);
        }
        $category_data['sections_categorization'] = $f_ids;
    }
    Registry::get('view')->assign('category_data', $category_data);
    Registry::get('view')->assign('section_features', $section_features);
}
