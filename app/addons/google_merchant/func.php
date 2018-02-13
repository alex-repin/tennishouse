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

function fn_google_merchant_get_products($params, &$fields, $sortings, &$condition, &$join, $sorting, $group_by, $lang_code, $having)
{
    if (!empty($params['gml_disable_product'])) {
        $condition .= db_quote(" AND products.gml_disable_product = ?s", $params['gml_disable_product']);
    }
}

function fn_google_merchant_clear_url_info()
{
    $storefront_url = Registry::get('config.http_location');
    if (fn_allowed_for('ULTIMATE')) {
        if (Registry::get('runtime.company_id') || Registry::get('runtime.simple_ultimate')) {
            $company = Registry::get('runtime.company_data');
            $storefront_url = 'http://' . $company['storefront'];
        } else {
            $storefront_url = '';
        }
    }

    if (!empty($storefront_url)) {
        $gm_available_in_customer = __('gm_available_in_customer', array(
            '[http_location]' => $storefront_url,
            '[gm_url]' => fn_url('google_merchant.view', 'C', 'http'),
        ));
    } else {
        $gm_available_in_customer = '';
    }

    return __('gm_clear_cache_info', array(
        '[clear_cache_url]' =>  fn_url('addons.update?addon=google_merchant?cc'),
        '[gm_available_in_customer]' => $gm_available_in_customer
    ));
}

function fn_google_auth_error($msg)
{
    header('WWW-Authenticate: Basic realm="Authorization required"');
    header('HTTP/1.0 401 Unauthorized');
    fn_echo($msg);
    exit;
}

function fn_google_auth()
{
    $options = Registry::get('addons.google_merchant');
    if (!empty($_SERVER['PHP_AUTH_USER']) && $options['username'] == $_SERVER['PHP_AUTH_USER'] && !empty($_SERVER['PHP_AUTH_PW']) && $_SERVER['PHP_AUTH_PW'] == $options['password']) {
        return true;
    } else {
        fn_google_auth_error(__("error"));
    }
}

function fn_get_gmarket_categories()
{
    return fn_get_schema('google_merchant', 'categories');
}

