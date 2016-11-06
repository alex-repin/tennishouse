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


if ($mode == 'add' || $mode == 'update') {

    if (!empty($_SESSION['approx_shipping'])) {
        $user_data = Registry::get('view')->getTemplateVars('user_data');

        if (!empty($_SESSION['approx_shipping']['city'])) {
            if (empty($user_data['b_city'])) {
                $user_data['b_city'] = $_SESSION['approx_shipping']['city'];
            }
            if (empty($user_data['s_city'])) {
                $user_data['s_city'] = $_SESSION['approx_shipping']['city'];
            }
        }
        if (!empty($_SESSION['approx_shipping']['state'])) {
            if (empty($user_data['b_state'])) {
                $user_data['b_state'] = $_SESSION['approx_shipping']['state'];
            }
            if (empty($user_data['s_state'])) {
                $user_data['s_state'] = $_SESSION['approx_shipping']['state'];
            }
        }
        if (!empty($_SESSION['approx_shipping']['country'])) {
            if (empty($user_data['b_country'])) {
                $user_data['b_country'] = $_SESSION['approx_shipping']['country'];
            }
            if (empty($user_data['s_country'])) {
                $user_data['s_country'] = $_SESSION['approx_shipping']['country'];
            }
        }
        Registry::get('view')->assign('user_data', $user_data);
    }
}
