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

$cart = & $_SESSION['cart'];

if ($mode == 'checkout') {
    $total = Registry::get('settings.General.min_order_amount_type') == 'products_with_shippings' ? $cart['total'] : $cart['subtotal'];
    fn_set_hook('allow_place_order', $total, $cart);

    $cart['amount_failed'] = (Registry::get('settings.General.min_order_amount') > $total && floatval($total));
    if (!empty($cart['amount_failed'])) {
        fn_set_notification('W', __('attention'), __('text_min_order_amount_required', array('[order_limit]' => fn_format_price_by_currency(Registry::get('settings.General.min_order_amount')))));
//         return array(CONTROLLER_STATUS_REDIRECT, !empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : "checkout.cart");
    }
}