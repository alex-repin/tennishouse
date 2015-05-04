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

// TennisHouse

use Tygh\Registry;
use Tygh\Http;

if (defined('PAYMENT_NOTIFICATION')) {
    // [tennishouse]
    if (isset($_REQUEST['order_id'])) {
        $ordernumber = explode('_', $_REQUEST['order_id']);
        $order_id = reset($ordernumber);

    } elseif (isset($_REQUEST['order'])) {
        $orderNumber = explode('_', $_REQUEST['order']);
        $order_id = reset($orderNumber);
    } else {
        $order_id = 0;
    }
    // [tennishouse]

    if ($mode == 'notify') {

        $pp_response['order_status'] = 'P';
        if (fn_check_payment_script('qiwi.php', $order_id)) {
            fn_finish_payment($order_id, $pp_response, false);
        }

        fn_order_placement_routines('route', $order_id);
        
    } elseif ($mode == 'error') {

        $pp_response['order_status'] = 'F';
        $pp_response["reason_text"] = __('text_transaction_cancelled');

        if (fn_check_payment_script('qiwi.php', $order_id)) {
            fn_finish_payment($order_id, $pp_response, false);
        }

        fn_order_placement_routines('route', $order_id);
    }
    exit;
}

if (!defined('BOOTSTRAP')) {
//     if (!empty($_REQUEST['parameter']) && $_REQUEST['parameter'] == 'update') {
//         require './init_payment.php';
// 
//         class Response
//         {
//             public $updateBillResult;
//         }
//         class Param
//         {
//             public $login;
//             public $password;
//             public $txn;
//             public $status;
//         }
// 
//         class UpdateServer
//         {
//             function updateBill($param)
//             {
//                 if (!is_object($param)) {
//                     return false;
//                 }
// 
//                 $order_info = fn_get_order_info($param->txn, false, true, true, true);
//                 $temp = '';
//                 if (!empty($order_info['payment_method']['processor_params']['notification_password']) && !empty($order_info['payment_method']['processor_params']['shop_id'])) {
//                     $txn = fn_convert_encoding('utf-8', 'windows-1251', $param->txn);
//                     $password = fn_convert_encoding('utf-8', 'windows-1251', $order_info['payment_method']['processor_params']['notification_password']);
//                     $crc = strtoupper(md5($txn . strtoupper(md5($password))));
// 
//                     if ($param->login == $order_info['payment_method']['processor_params']['shop_id'] && $param->password == $crc) {
//                         $pp_response = array();
//                         $status = 'qiwi_order_status_' . $param->status;
//                         if ($param->status == 60) {
//                             $pp_response['order_status'] = 'P';
//                         } elseif ($param->status >= 50 && $param->status < 60) {
//                             $pp_response['order_status'] = 'O';
//                         } else {
//                             $pp_response['order_status'] = 'F';
//                         }
// 
//                         $pp_response['reason_text'] = __($status);
//                         fn_finish_payment($param->txn, $pp_response);
// 
//                         $temp = new Response();
//                         $temp->updateBillResult = 0;
//                     }
//                 }
// 
//                 return $temp;
//             }
//         }
//         $server = new SoapServer('./qiwi_files/IShopClientWS.wsdl', array('classmap' => array('tns:updateBill' => 'Param', 'tns:updateBillResponse' => 'Response')));
//         $server->setClass('UpdateServer');
//         $server->handle();
//     } else {
//         die('Access denied');
//     }
} else {
    $dame_format = 'Y-m-d\TH:i:s';
    //C0CRmNJC0FGlmU8SHh3e
    
    $_order_id = $order_info['repaid'] ? ($order_info['order_id'] . '_' . $order_info['repaid']) : $order_info['order_id'];
    $_order_total = fn_format_rate_value($order_info['total'], 'F', 2, '.', '', '');
    $_lifetime = date($dame_format, time() + ($processor_data['processor_params']['lifetime'] * 60));
    $url = "https://qiwi.com/api/v2/prv/" . $processor_data['processor_params']['shop_id'] . "/bills/" . $_order_id;
    
    $data = array(
        "user" => "tel:+" . fn_qiwi_convert_phone($order_info['payment_info']['phone']),
        "amount" => $_order_total,
        "ccy" => $order_info['secondary_currency'],
        "comment" => (!empty($order_info['notice']) ? $order_info['notice'] : ''),
        "lifetime" => $_lifetime,
        "pay_source" => "qw",
        "prv_name" => "TennisHouse"
    );
    $extra = array(
        'basic_auth' => array($processor_data['processor_params']['login'] . ':' . $processor_data['processor_params']['passwd']),
        'headers' => array('Accept: application/json')
    );
    $result = json_decode(Http::put($url, $data, $extra));
    $pp_response['reason_text'] = __('qiwi_result_status_' . $result->response->result_code);

    if ($result->response->result_code == 0) {
        $pp_response['order_status'] = 'O';
    } else {
        $pp_response['order_status'] = 'F';
    }

    fn_finish_payment($order_id, $pp_response);
    $idata = array (
        'order_id' => $_order_id,
        'type' => 'S',
        'data' => TIME
    );
    db_query("REPLACE INTO ?:order_data ?e", $idata);
    
    if ($result->response->result_code == 0) {
        $redirect_url = "https://qiwi.com/order/external/main.action?shop=" . $processor_data['processor_params']['shop_id'] . '&transaction=' . $_order_id . '&successUrl=' . urlencode(fn_url("payment_notification.notify?payment=qiwi&order_id=$_order_id", AREA)) . '&failUrl=' . urlencode(fn_url("payment_notification.error?payment=qiwi&order_id=$_order_id", AREA));
        fn_redirect($redirect_url, true);
    } else {
        fn_order_placement_routines('route', $order_id, false);
    }
    
}

function fn_qiwi_convert_phone($phone)
{
    $phone = str_replace(array('+', ' ', '(', ')', '-'), '', $phone);

    if (strlen($phone) > 11) {
        $phone = substr($phone, -11);
    }

    return $phone;
}
