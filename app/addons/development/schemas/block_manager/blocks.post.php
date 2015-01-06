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
$schema['products']['content']['items']['fillings']['shoes_for_hard']['params'] = array (
    'sort_by' => 'timestamp',
    'sort_order' => 'desc',
    'request' => array (
        'shoes_for_hard' => 'Y'
    ),
);

return $schema;
