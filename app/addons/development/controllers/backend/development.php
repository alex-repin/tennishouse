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

use Tygh\Memcached;
use Tygh\Registry;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
}

if ($mode == 'calculate_balance') {

    $params = $_REQUEST;
    if (!empty($params['length']) && !empty($params['points']) && !empty($params['relation'])) {
        if (strtoupper($params['relation']) == 'HL') {
            $result = $params['length']/2 - $params['points'] * 1/8;
        } else {
            $result = $params['length']/2 + $params['points'] * 1/8;
        }
        $params['result'] = $result * 2.54;
    }
    Registry::get('view')->assign('params', $params);
    
} elseif ($mode == 'test_memcached') {
//     Memcached::instance()->set('ddd', '');
     fn_print_die(Memcached::instance()->validateSql( "SELECT  cscart_pages.*, cscart_page_descriptions.*  FROM cscart_pages LEFT JOIN cscart_page_descriptions ON cscart_pages.page_id = cscart_page_descriptions.page_id AND cscart_page_descriptions.lang_code = 'ru'  INNER JOIN cscart_ult_objects_sharing ON (cscart_ult_objects_sharing.share_object_id = cscart_pages.page_id AND cscart_ult_objects_sharing.share_company_id = 1 AND cscart_ult_objects_sharing.share_object_type = 'pages')WHERE  1 AND cscart_pages.id_path LIKE '53/%' AND cscart_pages.status IN ('A') AND cscart_pages.page_type IN ('T', 'L') AND (cscart_pages.usergroup_ids = '' OR FIND_IN_SET(0, cscart_pages.usergroup_ids) OR FIND_IN_SET(2, cscart_pages.usergroup_ids) OR FIND_IN_SET(1, cscart_pages.usergroup_ids)) AND (use_avail_period = 'N' OR (use_avail_period = 'Y' AND avail_from_timestamp <= 1421712562 AND avail_till_timestamp >= 1421712562))    ORDER BY cscart_pages.parent_id asc, cscart_pages.position asc, cscart_page_descriptions.page asc" ));
//Memcached::instance()->flush();
exit;
}