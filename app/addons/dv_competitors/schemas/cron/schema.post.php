<?php

$schema['C'] = array(
    'frequency' => array(
        'N' => '3',
        'H' => '02',
    ),
    'function' => 'fn_update_competitive_catalog',
    'name' => 'update_competitive_catalog'
);
$schema['I'] = array(
    'frequency' => array(
        'N' => '1,2,4,5,6,7',
        'H' => '02'
    ),
    'function' => 'fn_update_competitive_prices',
    'name' => 'update_competitive_prices'
);
// $schema['A'] = array(
//     'frequency' => array(
//         'H' => '05'
//     ),
//     'function' => 'fn_actualize_prices',
//     'name' => 'actualize_prices'
// );

return $schema;
