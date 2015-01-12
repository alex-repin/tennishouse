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

$schema['products']['content']['items']['fillings']['similar_products']['params'] = array (
    'sort_by' => 'timestamp',
    'sort_order' => 'desc',
    'request' => array (
        'similar_pid' => '%PRODUCT_ID%',
        'exclude_pid' => '%PRODUCT_ID%'
    ),
);
$schema['products']['content']['items']['fillings']['same_brand_products']['params'] = array (
    'sort_by' => 'timestamp',
    'sort_order' => 'desc',
    'request' => array (
        'same_brand_pid' => '%PRODUCT_ID%',
        'exclude_pid' => '%PRODUCT_ID%'
    ),
);
$schema['pages']['content']['items']['fillings']['dynamic_content'] = array (
    'params' => array (
        'status' => 'A',
        'request' => array (
            'parent_id' => '%PAGE_ID%'
        ),
    ),
);
$schema['products']['settings']['all_items_url'] = array (
    'type' => 'input',
    'default_value' => ''
);
$schema['products']['content']['items']['fillings']['allcourt_shoes']['params'] = array (
    'sort_by' => 'timestamp',
    'sort_order' => 'desc',
    'shoes_surface' => 'allcourt'
);
$schema['products']['content']['items']['fillings']['shoes_for_clay']['params'] = array (
    'sort_by' => 'timestamp',
    'sort_order' => 'desc',
    'shoes_surface' => 'clay'
);
$schema['products']['content']['items']['fillings']['shoes_for_grass']['params'] = array (
    'sort_by' => 'timestamp',
    'sort_order' => 'desc',
    'shoes_surface' => 'grass'
);
$schema['products']['content']['items']['fillings']['power_rackets']['params'] = array (
    'sort_by' => 'timestamp',
    'sort_order' => 'desc',
    'rackets_type' => 'power'
);
$schema['products']['content']['items']['fillings']['club_rackets']['params'] = array (
    'sort_by' => 'timestamp',
    'sort_order' => 'desc',
    'rackets_type' => 'club'
);
$schema['products']['content']['items']['fillings']['pro_rackets']['params'] = array (
    'sort_by' => 'timestamp',
    'sort_order' => 'desc',
    'rackets_type' => 'pro'
);
$schema['products']['content']['items']['fillings']['heavy_head_light_rackets']['params'] = array (
    'sort_by' => 'timestamp',
    'sort_order' => 'desc',
    'rackets_type' => 'heavy_head_light'
);
$schema['products']['content']['items']['fillings']['light_head_heavy_rackets']['params'] = array (
    'sort_by' => 'timestamp',
    'sort_order' => 'desc',
    'rackets_type' => 'light_head_heavy'
);
$schema['products']['content']['items']['fillings']['stiff_rackets']['params'] = array (
    'sort_by' => 'timestamp',
    'sort_order' => 'desc',
    'rackets_type' => 'stiff'
);
$schema['products']['content']['items']['fillings']['soft_rackets']['params'] = array (
    'sort_by' => 'timestamp',
    'sort_order' => 'desc',
    'rackets_type' => 'soft'
);
$schema['products']['content']['items']['fillings']['regular_head_rackets']['params'] = array (
    'sort_by' => 'timestamp',
    'sort_order' => 'desc',
    'rackets_type' => 'regular_head'
);
$schema['products']['content']['items']['fillings']['regular_length_rackets']['params'] = array (
    'sort_by' => 'timestamp',
    'sort_order' => 'desc',
    'rackets_type' => 'regular_length'
);
$schema['products']['content']['items']['fillings']['closed_pattern_rackets']['params'] = array (
    'sort_by' => 'timestamp',
    'sort_order' => 'desc',
    'rackets_type' => 'closed_pattern'
);
$schema['products']['content']['items']['fillings']['open_pattern_rackets']['params'] = array (
    'sort_by' => 'timestamp',
    'sort_order' => 'desc',
    'rackets_type' => 'open_pattern'
);
$schema['products']['content']['items']['fillings']['natural_gut_strings']['params'] = array (
    'sort_by' => 'timestamp',
    'sort_order' => 'desc',
    'strings_type' => 'natural_gut'
);
$schema['products']['content']['items']['fillings']['nylon_strings']['params'] = array (
    'sort_by' => 'timestamp',
    'sort_order' => 'desc',
    'strings_type' => 'nylon'
);
$schema['products']['content']['items']['fillings']['polyester_strings']['params'] = array (
    'sort_by' => 'timestamp',
    'sort_order' => 'desc',
    'strings_type' => 'polyester'
);
$schema['products']['content']['items']['fillings']['hybrid_strings']['params'] = array (
    'sort_by' => 'timestamp',
    'sort_order' => 'desc',
    'strings_type' => 'hybrid'
);
$schema['products']['content']['items']['fillings']['monofil_strings']['params'] = array (
    'sort_by' => 'timestamp',
    'sort_order' => 'desc',
    'strings_type' => 'monofil'
);
$schema['products']['content']['items']['fillings']['multifil_strings']['params'] = array (
    'sort_by' => 'timestamp',
    'sort_order' => 'desc',
    'strings_type' => 'multifil'
);
$schema['products']['content']['items']['fillings']['textured_strings']['params'] = array (
    'sort_by' => 'timestamp',
    'sort_order' => 'desc',
    'strings_type' => 'textured'
);
$schema['products']['content']['items']['fillings']['synthetic_gut_strings']['params'] = array (
    'sort_by' => 'timestamp',
    'sort_order' => 'desc',
    'strings_type' => 'synthetic_gut'
);

return $schema;
