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

if ($mode == 'update_rub_rate') {
    $rates = fn_get_currency_exchange_rates();
    if ($rates) {
        foreach ($rates as $code => $rate) {
            $currency_data = array('coefficient' => $rate);
            db_query("UPDATE ?:currencies SET ?u WHERE currency_code = ?s", $currency_data, $code);
        }
    }

    exit;
}