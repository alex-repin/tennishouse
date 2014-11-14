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

use Tygh\Enum\ProductTracking;
use Tygh\Registry;
use Tygh\BlockManager\SchemesManager;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($_SERVER['REQUEST_METHOD'] != 'POST' && $mode == 'update') {

    $tabs = Registry::get('navigation.tabs');
    
    $players_tab = array (
        'title' => __('players'),
        'js' => true
    );
    $tabs = fn_insert_before_key($tabs, 'seo', 'players', $players_tab);
    
    if (!empty($tabs['features'])) {
        $features_tab = $tabs['features'];
        unset($tabs['features']);
        $tabs = fn_insert_before_key($tabs, 'seo', 'features', $features_tab);
    }
    $options_tab = $tabs['options'];
    unset($tabs['options']);
    $tabs['options'] = $options_tab;
    
    
    // [/Product tabs]
    Registry::set('navigation.tabs', $tabs);
    // [/Page sections]
}
