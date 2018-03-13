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

fn_enable_checkout_mode();

fn_define('ORDERS_TIMEOUT', 60);

// Cart is empty, create it
if (empty($_SESSION['cart'])) {
    fn_clear_cart($_SESSION['cart']);
}

$cart = & $_SESSION['cart'];

if (!empty($_REQUEST['order_id']) && strpos($_REQUEST['order_id'], '-') !== false) {
    $_REQUEST['order_id'] = db_get_field("SELECT order_id FROM ?:orders WHERE order_number = ?s", $_REQUEST['order_id']);
}
if ($mode == 'checkout') {
    $total = Registry::get('settings.General.min_order_amount_type') == 'products_with_shippings' ? $cart['total'] : $cart['subtotal'];
    fn_set_hook('allow_place_order', $total, $cart);

    $cart['amount_failed'] = (Registry::get('settings.General.min_order_amount') > $total && floatval($total));
    if (!empty($cart['amount_failed'])) {
        fn_set_notification('W', __('attention'), __('text_min_order_amount_required', array('[order_limit]' => fn_format_price_by_currency(Registry::get('settings.General.min_order_amount')))));
//         return array(CONTROLLER_STATUS_REDIRECT, !empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : "checkout.cart");
    }
}
if ($mode == 'auto_save_user') {

    $user_data = !empty($_REQUEST['user_data']) ? $_REQUEST['user_data'] : array();
    unset($user_data['user_type']);

    if (!empty($user_data)) {
        $user_data = fn_fill_contact_info_from_address($user_data);

        $cart['user_data'] = fn_array_merge($cart['user_data'], $user_data);

        // Fill shipping info with billing if needed
        if (empty($_REQUEST['ship_to_another'])) {
            $profile_fields = fn_get_profile_fields('O');
            fn_fill_address($cart['user_data'], $profile_fields);
        }
        fn_save_cart_content($cart, $auth['user_id']);
    }
    exit;
}