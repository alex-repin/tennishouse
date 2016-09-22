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

if (!defined('BOOTSTRAP')) { die('Access denied'); }

use Tygh\Registry;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if ($mode == 'request') {

        if (fn_image_verification('use_for_call_request', $_REQUEST) == false) {
            return array(CONTROLLER_STATUS_REDIRECT);
        }
        if (!empty($_REQUEST['call_data'])) {
            if ($res = fn_do_call_request($_REQUEST['call_data'], $_SESSION['cart'], $_SESSION['auth'], $_REQUEST['product_data'])) {
                if (!empty($res['error'])) {
                    fn_set_notification('E', __('error'), $res['error']);
                } elseif (!empty($res['notice'])) {
                    fn_set_notification('N', __('notice'), $res['notice']);
                }
            }
        }
        return array(CONTROLLER_STATUS_OK, !empty($_REQUEST['redirect_url'])? $_REQUEST['redirect_url'] : fn_url());
    }

    return array(CONTROLLER_STATUS_OK);
}
