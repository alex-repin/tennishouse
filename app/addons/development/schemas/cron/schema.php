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

// wday = Пн - 1, Вт - 2, Ср - 3, Чт - 4, Пт - 5, Сб - 6, Вс - 7
return array(
    'F' => array(
        'frequency' => 60 * 60 * 24,
        'function' => 'fn_generate_features_cash'
    ),
    'R' => array(
        'frequency' => 60 * 60 * 24,
        'function' => 'fn_update_rankings',
        'wday' => '2'
    ),
    'E' => array(
        'frequency' => 60 * 60 * 24,
        'function' => 'fn_check_expired_points'
    ),
    'P' => array(
        'frequency' => 60 * 60 * 24,
        'function' => 'fn_update_rub_rate'
    ),
);
