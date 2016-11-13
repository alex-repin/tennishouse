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
use Tygh\BlockManager\Block;
use Tygh\BlockManager\RenderManager;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($mode == 'get_block') {
    if (!empty($_REQUEST['b_id'])) {
        $block = Block::instance()->getById($_REQUEST['b_id'], $_REQUEST['s_id'], $_REQUEST['dynamic_object']);
        if (!empty($_REQUEST['extra_properties'])) {
            $block['extra_properties'] = $_REQUEST['extra_properties'];
        }
        $content = RenderManager::renderBlockContent($block);
        echo '<div id="ajax_block_content_' . $_REQUEST['b_id'] . '">' . $content . '<!--ajax_block_content_' . $_REQUEST['b_id'] . '--></div>';
    }
    exit;
}