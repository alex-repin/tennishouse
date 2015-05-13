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

use \Tygh\Registry;

$schema['top']['administration']['items']['development'] = array(
    'href' => 'development.index',
    'type' => 'title',
    'position' => 1500,
    'subitems' => array(
        'calculate_racket_balance' => array(
            'href' => 'development.calculate_balance',
            'position' => 100,
        ),
        'update_exchange_rates' => array(
            'href' => fn_url('development.update_rub_rate', 'C'),
            'ajax' => true,
            'position' => 100,
        ),
        'update_rankings' => array(
            'href' => fn_url('development.update_rankings', 'C'),
            'ajax' => true,
            'position' => 100,
        ),
    ),
);
$active = fn_get_memcached_status();
if ($active) {
    $schema['top']['administration']['items']['development']['subitems']['cache_features'] = array(
        'href' => fn_url('development.generate_features_cache', 'C'),
        'position' => 200,
    );
}

$schema['central']['products']['items']['players'] = array(
    'href' => 'players.manage',
    'position' => 350,
);

$schema['top']['administration']['items']['import_data']['subitems']['supplier_stocks'] = array(
    'href' => 'development.supplier_stocks',
    'position' => 1000,
);
return $schema;
