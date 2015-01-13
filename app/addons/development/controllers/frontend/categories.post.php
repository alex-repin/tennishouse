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

if ($mode == 'view') {
    $category_data = Registry::get('view')->gettemplatevars('category_data');
    $products = Registry::get('view')->gettemplatevars('products');
    $subcategories = Registry::get('view')->gettemplatevars('subcategories');
    if (empty($subcategories) && empty($products)) {
        $cat_ids = explode('/', $category_data['id_path']);
        $main_parent = reset($cat_ids);
        if (!empty($main_parent)) {
            return array(CONTROLLER_STATUS_REDIRECT, 'categories.view?category_id=' . $main_parent);
        }
    }
}
