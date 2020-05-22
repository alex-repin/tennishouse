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
            'position' => 200,
        ),
        'update_rrp' => array(
            'href' => 'development.update_rrp',
            'position' => 120,
        ),
    ),
);
$schema['top']['administration']['items']['cron_scripts'] = array(
    'href' => 'cron.manage',
    'position' => 800,
);

$schema['central']['products']['items']['players'] = array(
    'href' => 'players.manage',
    'position' => 350,
);

$schema['central']['products']['items']['technologies'] = array(
    'href' => 'technologies.manage',
    'position' => 360,
);

$schema['central']['products']['items']['warehouses'] = array(
    'href' => 'warehouses.manage',
    'position' => 210,
);
$schema['central']['products']['items']['competitive_prices'] = array(
    'href' => 'development.competitive_prices',
    'position' => 220,
);

// $schema['top']['administration']['items']['import_data']['subitems']['supplier_stocks'] = array(
//     'href' => 'development.supplier_stocks',
//     'position' => 1000,
// );
//
$schema['central']['marketing']['items']['saving_system'] = array(
    'attrs' => array(
        'class'=>'is-addon'
    ),
    'href' => 'development.saving_system',
    'position' => 910
);
$schema['central']['website']['items']['anouncements'] = array(
    'attrs' => array(
        'class'=>'is-addon'
    ),
    'href' => 'anouncements.manage',
    'position' => 110
);

return $schema;
