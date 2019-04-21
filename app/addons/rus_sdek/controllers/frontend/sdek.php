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

use Tygh\FeaturesCache;
use Tygh\Http;
use Tygh\Registry;
use Tygh\Shippings\RusSdek;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($mode = 'select_office') {
    if (!empty($_REQUEST['country']) && !empty($_REQUEST['state']) && !empty($_REQUEST['city'])) {
        $city_code = RusSdek::SdekCityId($_REQUEST);
        if (!empty($city_code)) {
            $params = array(
                'cityid' => $city_code
            );
            $offices = RusSdek::SdekPvzOffices($params);
            Registry::get('view')->assign('offices', $offices);
            Registry::get('view')->assign('country', $_REQUEST['country']);
            Registry::get('view')->assign('city', $_REQUEST['city']);
            Registry::get('view')->assign('state', $_REQUEST['state']);
            Registry::get('view')->assign('city_id', $_REQUEST['city_id']);
            Registry::get('view')->display('addons/rus_sdek/components/select_office.tpl');
        }
    }
    exit;
}