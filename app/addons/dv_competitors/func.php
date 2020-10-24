<?php

use Tygh\Registry;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

function fn_get_competitors($params = array())
{
    $fields = array (
        '?:competitors.*',
    );

    $condition = $join = '';

    if (!empty($params['item_ids'])) {
        $condition .= db_quote(' AND ?:competitors.competitor_id IN (?n)', explode(',', $params['item_ids']));
    }

    if (!empty($params['except_id']) && (empty($params['item_ids']) || !empty($params['item_ids']) && !in_array($params['except_id'], explode(',', $params['item_ids'])))) {
        $condition .= db_quote(' AND ?:competitors.competitor_id != ?i', $params['except_id']);
    }

    $limit = '';

    if (!empty($params['limit'])) {
        $limit = db_quote(' LIMIT 0, ?i', $params['limit']);
    }

    $competitors = db_get_hash_array('SELECT ' . implode(',', $fields) . " FROM ?:competitors ?p WHERE 1 ?p ORDER BY ?:competitors.competitor_id ASC ?p", 'competitor_id', $join, $condition, $limit);

    if (empty($competitors)) {
        return array(array(), $params);
    }

    return array($competitors, $params);
}

function fn_update_competitor($competitor_data, $competitor_id = 0)
{
    $_data = $competitor_data;

    // create new competitor
    if (empty($competitor_id)) {

        $create = true;

        $competitor_id = db_query("INSERT INTO ?:competitors ?e", $_data);

    // update existing competitor
    } else {

        $arow = db_query("UPDATE ?:competitors SET ?u WHERE competitor_id = ?i", $_data, $competitor_id);

        if ($arow === false) {
            fn_set_notification('E', __('error'), __('object_not_found', array('[object]' => __('competitor'))),'','404');
            $competitor_id = false;
        }
    }

    if (!empty($competitor_id) && isset($_data['brand_ids'])) {

        // Log competitor add/update
        fn_log_event('competitors', !empty($create) ? 'create' : 'update', array(
            'competitor_id' => $competitor_id,
        ));

    }

    return $competitor_id;

}

function fn_get_competitor_data($competitor_id)
{
    $field_list = "?:competitors.*";

    $competitor_data = db_get_row("SELECT $field_list FROM ?:competitors ?p WHERE ?:competitors.competitor_id = ?i ?p", $join, $competitor_id, $condition);

    return (!empty($competitor_data) ? $competitor_data : false);
}

function fn_delete_competitor($competitor_id)
{
    if (empty($competitor_id)) {
        return false;
    }

    // Log competitor deletion
    fn_log_event('competitors', 'delete', array(
        'competitor_id' => $competitor_id,
    ));

    // Deleting competitor
    db_query("DELETE FROM ?:competitors WHERE competitor_id = ?i", $competitor_id);
    db_query("DELETE FROM ?:competitive_pairs WHERE competitor_id = ?i", $competitor_id);
    db_query("DELETE FROM ?:competitive_prices WHERE competitor_id = ?i", $competitor_id);

    return true;
}
