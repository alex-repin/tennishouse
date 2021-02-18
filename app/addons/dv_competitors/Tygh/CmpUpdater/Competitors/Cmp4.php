<?php
/***************************************************************************
 *                                                                          *
 *   (c) 2020 PaulDreda    *
 *                                                                          *
 * This  is  commercial  software,  only  users  who have purchased a valid *
 * license  and  accept  to the terms of the  License Agreement can install *
 * and use this program.                                                    *
 *                                                                          *
 ****************************************************************************
 * PLEASE READ THE FULL TEXT  OF THE SOFTWARE  LICENSE   AGREEMENT  IN  THE *
 * "copyright.txt" FILE PROVIDED WITH THIS DISTRIBUTION PACKAGE.            *
 ****************************************************************************/


namespace Tygh\CmpUpdater\Competitors;

class Cmp4 extends Competitor
{
    public function __construct()
    {
        parent::__construct(RACKETS_COMPETITOR_ID);
        $this->new_links = array(
        );
    }

    protected function prsProduct($content)
    {
        $product = array();

        if (preg_match('/<form name="product".*?>(.*?)<\/form>/', preg_replace(array('/[\r\n\t]/', '/\>\s+\</m'), array('', '><'), $content), $section)) {

            if (preg_match('/<h1 class="title">(.*?)<\/h1>/', $section[1], $match)) {
                $product['name'] = $match[1];

                $parts = explode(' ', $product['name']);
                $tmp = array_pop($parts);
                if (!empty($this->codes[$tmp])) {
                    $product['code'] = $tmp;
                } else {
                    $product['code'] = '';
                }
            }

            if (preg_match('/<div class="prod_price"><span id="block_price">(.*?)<\/span>/', $section[1], $match)) {
                $product['price'] = (int)preg_replace('/[^0-9]/', '', $match[1]);
            }

            $product['in_stock'] = 'Y';
        }

        if (!empty($product['name']) && /*!empty($product['code']) &&*/ !empty($product['price']) && !empty($product['in_stock'])) {
            $product['link'] = $this->current_link;
            return $product;
        }

        return false;
    }

}
