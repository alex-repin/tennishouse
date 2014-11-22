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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

}

if ($mode == 'view') {

    $player_data = !empty($_REQUEST['player_id']) ? fn_get_player_data($_REQUEST['player_id']) : array();

    if (empty($player_data) || empty($player_data['status']) || !empty($player_data['status']) && $player_data['status'] != 'A') {
        return array(CONTROLLER_STATUS_NO_PAGE);
    }

    fn_add_breadcrumb(__('professionals'), 'players.list');
    fn_add_breadcrumb($player_data['player']);

    Registry::get('view')->assign('player_data', $player_data);

} elseif ($mode == 'list') {

    fn_add_breadcrumb(__('professionals'));

    $params = $_REQUEST;

    list($players, $search) = fn_get_players($params);

    Registry::get('view')->assign('players', $players);
    Registry::get('view')->assign('search', $search);

}
