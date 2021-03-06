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

namespace Tygh\Shippings;

use Tygh\Http;
use Tygh\Registry;

class RusSdek
{
    public static $url;

    public static function arraySimpleXml($name, $data, $type = 'simple')
    {
        $xml = '<'.$name.' ';
        foreach ($data as $key => $value) {
            if (isset($value)) {
                $value = fn_html_escape($value);
                $xml .= $key .'="' . $value .'" ';
            }
        }

        if ($type == 'open') {
            $xml .= '>';
        } else {
            $xml .= '/>';
        }

        return $xml;
    }

    public static function resultXml($response)
    {
        $result = array(
            'error' => false,
            'msg' => false,
            'number' => false,
        );

        $xml_result = simplexml_load_string($response);

        if (empty($xml_result)) {
            $result['error'] = true;
            
            return $result;
        }
        $attribut = $xml_result->children()->getName();

        $_result = json_decode(json_encode((array) $xml_result), true);

        foreach ($_result[$attribut] as $k) {
            $data = !empty($k['@attributes']) ? $k['@attributes'] : $k;

            if (!empty($data['Msg'])) {
                if (!empty($data['ErrorCode'])) {
                    $result['msg'] = $data['Msg'];
                    fn_set_notification('E', __('notice'), $data['Msg']);
                    if ($data['ErrorCode'] == 'ERR_ORDER_NOTFIND' || $data['ErrorCode'] == 'ERR_ORDER_DUBL_EXISTS') {
                        $result['error'] = false;
                    } else {
                        $result['error'] = true;
                    }
                } else {
                    $result['msg'] = $data['Msg'];
                    fn_set_notification('N', __('notice'), $data['Msg']);
                }

            } elseif (!empty($data['DispatchNumber'])) {
                $result['number'] = $data['DispatchNumber'];
            }
        }

        return $result;
    }

    public static function orderStatusXml($data_auth, $order_id = 0, $shipment_id = 0)
    {
        $data_status = array();
        if (!empty($order_id)) {
            unset($data_auth['Number']);
            unset($data_auth['OrderCount']);
        } else {
            $period_sdek = $data_auth['ChangePeriod'];
            unset($data_auth['ChangePeriod']);
        }

        $data_auth['ShowHistory'] = "1";

        $xml = '            ' . RusSdek::arraySimpleXml('StatusReport', $data_auth, 'open');

        if (!empty($order_id)) {
            $order_sdek = array (
                'Number' => $order_id . '_' . $shipment_id,
                'Date' => $data_auth['Date']
            );
            $xml .= '            ' . RusSdek::arraySimpleXml('Order', $order_sdek);
        } elseif (!empty($period_sdek)) {
            $xml .= '            ' . RusSdek::arraySimpleXml('ChangePeriod', $period_sdek);
        }

        $xml .= '            ' . '</StatusReport>';

        $response = RusSdek::SdekXmlRequest('https://integration.cdek.ru/status_report_h.php', $xml, $data_auth);

        $_result = json_decode(json_encode((array) simplexml_load_string($response)), true);

        if (!empty($_result['Order'])) {
            $_result = empty($_result['Order']['@attributes']) ? $_result['Order'] : array($_result['Order']);
            foreach ($_result as $data_order) {
                if (empty($data_order['@attributes']['ErrorCode'])) {
                    $order = explode('_', $data_order['@attributes']['Number']);
                    $d_status = !empty($data_order['Status']['State']) ? $data_order['Status']['State'] : $data_order['Status'];
                    if (!empty($d_status['@attributes'])) {
                        unset($d_status['@attributes']);
                    }
                    foreach ($d_status as $state) {
                        $data_status[$order[0] . '_' . $order[1] . '_' . $state['@attributes']['Code']] = array(
                            'id' => $state['@attributes']['Code'],
                            'order_id' => $order[0],
                            'shipment_id' => $order[1],
                            'timestamp' => strtotime($state['@attributes']['Date']),
                            'status' => $state['@attributes']['Description'],
                            'city_code' => $state['@attributes']['CityCode'],
                            'city_name' => db_get_field("SELECT a.city FROM ?:rus_city_sdek_descriptions as a LEFT JOIN ?:rus_cities_sdek as b ON a.city_id=b.city_id WHERE b.city_code = ?s", $state['@attributes']['CityCode']),
                            'date' => date("d-m-Y", strtotime($state['@attributes']['Date'])),
                        );
                    }
                }
            }
        }

        return $data_status;
    }

    public static function SdekCityId($location)
    {
        // [tennishouse]
        $_result = '';
        
        if (!empty($location['country']) && !empty($location['city'])) {
        // [tennishouse]
            if (preg_match('/^[a-zA-Z]+$/',$location['city'])) {
                $lang_code = 'en';
            } else {
                $lang_code = 'ru';
            }

            if (($lang_code == 'en') && (Registry::get('addons.rus_cities.status') == 'A')) {
                $d_city = db_get_field("SELECT a.city FROM ?:rus_city_descriptions as a LEFT JOIN ?:rus_city_descriptions as b ON a.city_id = b.city_id WHERE a.lang_code = ?s and b.lang_code = ?s and b.city LIKE ?l ", 'ru', $lang_code, $location['city']);
                if (!empty($d_city)) {
                    $location['city'] = $d_city;
                    $lang_code = 'ru';
                }
            }
            
            $condition = db_quote(" d.lang_code = ?s AND d.city LIKE ?l AND c.status = ?s", $lang_code , $location['city'] . "%", 'A');
            if (!empty($location['country'])) {
                $condition .= db_quote(" AND c.country_code = ?s", $location['country']);
            }

            $result = db_get_hash_array("SELECT c.city_code, c.state_code, d.city FROM ?:rus_city_sdek_descriptions as d LEFT JOIN ?:rus_cities_sdek as c ON c.city_id = d.city_id WHERE ?p", 'city_code', $condition);

            if (count($result) == 1) {
                reset($result);
                $_result = key($result);
            } elseif (count($result) > 1) {
                if (!empty($location['state'])) {
                    foreach ($result as $c_code => $c_data) {
                        if ($location['state'] != $c_data['state_code']) {
                            unset($result[$c_code]);
                        }
                    }
                }
                if (count($result) == 1) {
                    reset($result);
                    $_result = key($result);
                } elseif (count($result) > 1) {
                    $max_match = $city_id = 0;
                    foreach ($result as $c_code => $c_data) {
                        $prc = round(strlen($location['city'])/strlen($c_data['city']), 2) * 100;
                        if ($prc > $max_match) {
                            $max_match = $prc;
                            $city_id = $c_code;
                        }
                    }
                    $_result = $city_id;
                }
            }

            if (empty($_result)) {
//                     fn_set_notification('E', __('notice'), __('shippings.sdek.city_error'));
                $data = array(
                    'q' => $location['city'],
                    'limit' => 10
                );
                $extra = array(
                    'request_timeout' => 2,
                    'timeout' => 1
                );
                $result = json_decode(Http::get('http://api.cdek.ru/city/getListByTerm/json.php', $data, $extra), true);
                if (!empty($result['geonames'])) {
                    if (count($result['geonames']) == 1) {
                        $city = reset($result['geonames']);
                        $_result = $city['id'];
                    } elseif (count($result['geonames']) > 1) {
                        foreach ($result['geonames'] as $i => $c_data) {
                            if ($c_data['countryIso'] != $location['country']) {
                                unset($result['geonames'][$i]);
                            }
                        }
                        if (count($result['geonames']) == 1) {
                            $city = reset($result['geonames']);
                            $_result = $city['id'];
                        } elseif (count($result['geonames']) > 1) {
                            $max_match = $city_id = 0;
                            foreach ($result['geonames'] as $c_code => $c_data) {
                                $prc = round(strlen($location['city'])/strlen($c_data['cityName']), 2) * 100;
                                if ($prc > $max_match) {
                                    $max_match = $prc;
                                    $city_id = $c_data['id'];
                                }
                            }
                            $_result = $city_id;
                        }
                    }
                }
                $exists = db_get_field("SELECT city_code FROM ?:rus_cities_sdek WHERE city_code = ?i", $_result);
                if (!empty($exists)) {
                    $_result = $exists;
                    db_query("UPDATE ?:rus_cities_sdek SET country_code = ?s, state_code = ?s WHERE city_code = ?i", $location['country'], $location['state'], $_result);
                } elseif (!empty($_result) && !empty($location['country']) && !empty($location['city']) && (!empty($location['state']) || $location['country'] != 'RU')) {
                    $_data = array(
                        'country_code' => $location['country'],
                        'state_code' => $location['state'],
                        'city_code' => $_result,
                        'state' => 'A'
                    );
                    $_city_id = db_query("REPLACE INTO ?:rus_cities_sdek ?e", $_data);
                    if (!empty($_city_id)) {
                        $_data = array(
                            'city_id' => $_city_id,
                            'lang_code' => $lang_code,
                            'city' => $location['city']
                        );
                        db_query("REPLACE INTO ?:rus_city_sdek_descriptions ?e", $_data);
                    }
                }
            }
        }

        return $_result;
    }

    public static function SdekXmlRequest($url, $xml, $params_request)
    {
        $url = $url 
            . '?account=' . $params_request['Account'] 
            . '&secure=' . $params_request['Secure'] 
            . '&datefirst=' . $params_request['Date'];

        $xml_request = array(
            'xml_request' => '<?xml version="1.0" encoding="UTF-8" ?>' . $xml
        );

        if (empty($params_request['wait'])) {
            $extra = array(
                'request_timeout' => 2,
                'timeout' => 1
            );
        }

        $response = Http::post($url, $xml_request, $extra);

        return $response;
    }

    public static function SdekPvzOffices($city)
    {
        $extra = array();
        if (AREA == 'C') {
            $extra = array(
                'request_timeout' => 2,
                'timeout' => 1
            );
        } else {
            $extra = array(
                'request_timeout' => 3,
                'timeout' => 3
            );
        }
        $result = Http::get('https://integration.cdek.ru/pvzlist/v1/xml', $city, $extra);
        $xml = simplexml_load_string($result);
        if (!empty($xml)) {
            $count = count($xml->Pvz);
            if ($count != 0) {
                $offices = array();
                if ($count == 1) {
                    foreach($xml->Pvz->attributes() as $_key => $_value){
                        $code = (string) $xml->Pvz['Code'];
                        $offices[$code][$_key] = (string) $_value;
                    }
                } else {
                    foreach($xml->Pvz as $key => $office) {
                        $code = (string) $office['Code'];
                        foreach($office->attributes() as $_key => $_value){
                            $offices[$code][$_key] = (string) $_value;
                        }
                    }
                }
            }
        }

        return $offices;
    }

    public static function SdekAddStatusOrders($date_status)
    {
        foreach ($date_status as $d_status) {
            $status_id = db_get_row('SELECT id FROM ?:rus_sdek_status WHERE id = ?i and order_id = ?i and shipment_id = ?i ', $d_status['id'], $d_status['order_id'], $d_status['shipment_id']);
            if (empty($status_id)) {
                $register_id = db_query('INSERT INTO ?:rus_sdek_status ?e', $d_status);
            }
        }
    }

    public static function SdekDataAuth($params_shipping)
    {
        $data_auth = array();
        $data_shipping = fn_get_shipping_info($params_shipping['shipping_id'], DESCR_SL);
        $account = $data_shipping['service_params']['authlogin'];
        $secure_password = $data_shipping['service_params']['authpassword'];

        if (!empty($secure_password) && !empty($account)) {
            $secure = md5($params_shipping['Date'] . '&' . $secure_password);
            $data_auth['Date'] = $params_shipping['Date'];
            $data_auth['Account'] = $account;
            $data_auth['Secure'] = $secure;
        } else {
            fn_set_notification('E', __('notice'), __('shippings.sdek.account_password_error'));
        }

        return $data_auth;
    }
}
