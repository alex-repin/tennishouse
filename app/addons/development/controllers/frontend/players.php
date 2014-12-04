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

    if (!empty($player_data['gear'])) {
        list($player_data['gear'],) = fn_get_products(array('item_ids' => implode(',', $player_data['gear'])));
        fn_gather_additional_products_data($player_data['gear'], array(
            'get_icon' => true,
            'get_detailed' => true,
            'get_additional' => true,
            'get_options' => true,
            'get_discounts' => true,
            'get_features' => true
        ));
        fn_print_die($player_data['gear']);
    }
    
    if (!empty($player_data['rss_link'])) {
        $xml = @simplexml_load_string(fn_get_contents($player_data['rss_link']));
        $news_feed = array();
        foreach ($xml->channel->item as $item) {
            $news = array();
            $news['title'] = (string) $item->title;
            $news['link'] = (string) $item->link;
            $news['description'] = (string) $item->description;
            $aResult = strptime((string) $item->pubDate, '%a, %d %b %Y %T %z');
            $news['timestamp'] = gmmktime($aResult['tm_hour'], $aResult['tm_min'], $aResult['tm_sec'], $aResult['tm_mon'] + 1, $aResult['tm_mday'], $aResult['tm_year'] + 1900);
            $news_feed[] = $news;
        }
        $player_data['news_feed'] = $news_feed;
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
