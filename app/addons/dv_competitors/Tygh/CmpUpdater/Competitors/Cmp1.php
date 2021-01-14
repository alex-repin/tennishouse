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

use Tygh\Http;

class Cmp1 extends Competitor
{
    public function __construct()
    {
        parent::__construct(RACKETLON_COMPETITOR_ID);
        // $this->new_links = array(
        //     // 'https://racketlon.ru/tennis/racket/head/tennis-racket-head-graphene-360-extreme-s/?variation_id=26293' => true
        //     // 'https://racketlon.ru/tennis/racket/babolat/pure-aero/babolat-pure-aero-lite-2019/' => true,
        //     // 'https://racketlon.ru/tennis/products/tennis-court-equipment/baskets-and-carts/korzina-wilson-tennis-teaching-cart-150-black/' => true,
        //     // 'https://racketlon.ru/tennis/racket/babolat/pure-strike-ru/pure-control/' => true,
        //     // 'https://racketlon.ru/tennis/racket/junior/babolat/21/tennisnaya-raketka-babolat-pure-drive-junior-black-blue-21/' => true,
        // );
    }

    private function prsVariations($content)
    {
        $result = array();
        preg_match_all('/<input.*?data-ca-product-url=["\'](.*?)["\'].*?>/is', $content, $result);

        return $result[1];
    }

    protected function prsProduct($content, $check_variations = true)
    {
        $product = array();
        if (preg_match('/itemtype="http:\/\/schema\.org\/Product">(.*?)<\/body>/', preg_replace('/[\r\n\t]/', '', $content), $section)) {
            if (preg_match('/<label itemprop=\'name\'>(.*?)<\/label>/', $section[1], $match)) {
                $product['name'] = $match[1];
            }
            if (preg_match('/id="sku_update_\d+".*?>.*?<span class="ty-control-group__item.*?>(.*?)<.*?<\/div>/', $section[1], $match)) {
                $product['code'] = $match[1];
            }
            if (preg_match('/id="sec_discounted_price_\d+".*?>(.*?)<\/span>/', $section[1], $match)) {
                $product['price'] = floatval(str_replace('&nbsp;', '', $match[1]));
            }
            if (preg_match('/id="(in|out_of)_stock_info_\d+".*?>(.*?)<\/span>/', $section[1], $match)) {
                $in_stock_trim = trim($match[2]);
                if ($in_stock_trim == 'В наличии') {
                    $product['in_stock'] = 'Y';
                } else {
                    $product['in_stock'] = 'N';
                }
            }
        }

        if (!empty($product['name']) && !empty($product['code']) && !empty($product['price']) && !empty($product['in_stock'])) {

            if (!empty($check_variations)) {
                $variations = $this->prsVariations($content);
                if (!empty($variations)) {
                    $extra = array(
                        'request_timeout' => 10
                    );
                    $codes = array($product['code']);
                    foreach ($variations as $v_link) {
                        if ($v_link != $this->current_link) {
                            $result = Http::get($v_link, array(), $extra);
                            if (Http::getStatus() == Http::STATUS_OK && !empty($result)) {
                                $_product = $this->prsProduct($result, false);
                                $codes[] = $_product['code'];
                                if (!empty($_product)) {
                                    if ($product['in_stock'] == 'N' && $_product['in_stock'] == 'Y') {
                                        $product['in_stock'] = 'Y';
                                    }
                                    if ($_product['price'] < $product['price']) {
                                        $product['price'] = $_product['price'];
                                    }
                                }
                            }
                            $this->checked_links[$v_link] = Http::getStatus();
                            $this->checked_statuses[Http::getStatus()]++;
                            $this->pages_number++;
                        }
                    }
                    $product['code'] = $this->findCode($codes);
                }
            }

            return $product;
        }

        return false;
    }

    private function findCode($words)
    {
        $sort_by_strlen = create_function('$a, $b', 'return (strlen($a) < strlen($b)) ? -1 : 1;');
        usort($words, $sort_by_strlen);

        $common_substring = '';
        $shortest_string = str_split(array_shift($words));

        foreach ($shortest_string as $ci => $char) {
            foreach ($words as $wi => $word) {
                if (!strstr($word, $common_substring . $char)) {
                    break 2;
                }
            }
            $common_substring .= $char;
        }

        return $common_substring;
    }
}
