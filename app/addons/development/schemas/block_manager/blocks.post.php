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

return $schema;
