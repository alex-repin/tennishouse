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

if (defined('HTTPS')) {
    if (!isset($_SESSION['display_ssl_tooltip'])) {
        $_SESSION['display_ssl_tooltip'] = 'Y';
    } elseif ($_SESSION['display_ssl_tooltip'] == 'Y') {
        $_SESSION['display_ssl_tooltip'] = 'N';
    }
}

if (empty($_SESSION['hide_anouncement'])) {
    $anouncement = db_get_field("SELECT text FROM ?:anouncements WHERE start_timestamp <= ?i AND end_timestamp + 86399 >= ?i ORDER BY priority ASC", TIME, TIME);
    if (!empty($anouncement)) {
        Registry::get('view')->assign('anouncement', $anouncement);
    }
}