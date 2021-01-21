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

class Competitor
{
    protected $competitor;
    protected $checked_links;
    protected $checked_statuses = array(
        Http::STATUS_OK => 0,
        Http::STATUS_NOT_FOUND => 0,
        Http::STATUS_FORBIDDEN => 0,
        Http::STATUS_SERVICE_UNAVAILABLE => 0,
    );
    protected $new_links;
    protected $products = array();
    protected $current_link;
    protected $pages_number = 0;

    private static $parse_page_limit = 0;
    private static $parse_page_step = 10;
    private static $update_price_frequency = 60 * 60 * 10;
    private static $update_duration_limit = 60 * 60 * 3;

    private $log = array(
        'update_status' => '',
        'total' => 0,
        'memory_usage' => 0,
        'products_total' => 0,
        'statuses' => array(),
        'links' => array(),
        'products' => array()
    );

    public function __construct($competitor_id)
    {
        $this->competitor = db_get_row("SELECT * FROM ?:competitors WHERE competitor_id = ?i", $competitor_id);
    }

    private function prsLinksDom($content)
    {
        $result = array();
        $dom = new \DOMDocument();
        $dom->loadHtml($content);
        $links = $dom->getElementsByTagName('a');

        foreach ($links as $link) {
            $result[] = $link->getAttribute('href');
        }

        return $result;
    }

    private function prsLinksExp($content)
    {
        $result = array();
        $links = explode('<a',$content);
		unset($links[0]);
		foreach ($links as $hrefsItem) {
			$arHref = explode('href="', $hrefsItem);
			$arHref = explode('"', $arHref[1]);
			$result[] = $arHref[0];
		}

        return $result;
    }

    private function prsLinksReg($content)
    {
        $result = array();
        preg_match_all('/<a.*?href=["\'](.*?)["\'].*?>/is', $content, $result);

        return $result[1];
    }

    private function trimLinks(&$links)
    {
        $links = array_unique($links);
        $domain = ($this->competitor['link'][-1] == '/') ? mb_substr($this->competitor['link'], 0, -1) : $this->competitor['link'];
        $parsed_home = parse_url($domain);
        $result = array();
        foreach ($links as $link) {
            $parsed_link = parse_url($link);
            if (empty($parsed_link) || empty($parsed_link['path']) || $parsed_link['path'] == '/' || (!empty($parsed_link['host']) && $parsed_link['host'] != $parsed_home['host']) || (!empty($parsed_link['scheme']) && !in_array($parsed_link['scheme'], array('http', 'https'))) || substr($parsed_link['path'], -4, 4 ) == '.jpg') {
                continue;
            }
            $result[] = $domain . $parsed_link['path'];
        }

        $links = array_unique($result);
    }

    private function sortLinks($links)
    {
        foreach ($links as $link) {
            if (empty($this->checked_links[$link]) && empty($this->new_links[$link])) {
                $this->new_links[$link] = true;
            }
        }
    }

    private function parsePage($link)
    {
        $extra = array(
            'request_timeout' => 10
        );
        $product = array();
        $response = Http::get($link, array(), $extra);

        if (Http::getStatus() == Http::STATUS_OK && !empty($response)) {
            $product = $this->prsProduct($response);
            $product['link'] = $link;
            $this->products[] = $product;

            // $links = $this->prsLinksDom($result);
            // $links = $this->prsLinksExp($result);
            $links = $this->prsLinksReg($response);
            // fn_print_r(microtime());
            $this->trimLinks($links);
            // fn_print_r(microtime());
            $this->sortLinks($links);
            // fn_print_r(microtime());
        }

        return Http::getStatus();
    }

    private function parsePages($link)
    {
        $this->current_link = $link;
        $status = $this->parsePage($link);

        unset($this->new_links[$link]);
        $this->checked_links[$link] = $status;
        $this->checked_statuses[$status]++;
        $this->pages_number++;

        $this->log['total'] = $this->pages_number;
        $this->log['statuses'] = $this->checked_statuses;
        $this->log['links'] = $this->checked_links;
        $this->log['memory_usage'] = memory_get_usage();
        db_query("UPDATE ?:competitors SET update_log = ?s WHERE competitor_id = ?i", serialize($this->log), $this->competitor['competitor_id']);

        fn_echo(' . ');

        if (!empty(self::$parse_page_step) && count($this->products) == self::$parse_page_step) {
            $this->saveProducts();
        }

        if ((empty(self::$parse_page_limit) || $this->pages_number < self::$parse_page_limit) && !empty($this->new_links)) {
            $this->parsePages(key($this->new_links));
        }

    }

    private function saveProducts()
    {
        if (!empty($this->products)) {
            $data = array();
            foreach ($this->products as $product) {
                $data[] = array_merge($product, array(
                    'timestamp' => TIME,
                    'competitor_id' => $this->competitor['competitor_id']
                ));
            }
            db_query("REPLACE INTO ?:competitive_prices ?m", $data);

            $this->log['products_total'] += count($this->products);
            $this->log['products'] = array_merge($this->log['products'], $this->products);
            $this->products = array();
        }
    }

    public function updateCompetitor()
    {
        $success = false;

        $this->log['update_status'] = 'Started';
        $this->parsePages($this->competitor['link']);

        $this->saveProducts();

        $this->log['total'] = $this->pages_number;
        $this->log['statuses'] = $this->checked_statuses;
        $this->log['links'] = $this->checked_links;
        $this->log['update_status'] = 'Finished';

        db_query("UPDATE ?:competitors SET last_update = ?i, update_log = ?s WHERE competitor_id = ?i", TIME, serialize($this->log), $this->competitor['competitor_id']);

        return array($success, $this->log);
    }

    public function updatePrices($data = array())
    {
        $success = false;
        $extra = array(
            'request_timeout' => 10
        );

        if (empty($data)) {
            $data = db_get_array("SELECT * FROM ?:competitive_prices WHERE competitor_id = ?i AND timestamp < ?i ORDER BY timestamp", $this->competitor['competitor_id'], TIME - self::$update_price_frequency);
        }

        $to_delete = array();
        foreach ($data as $_dt) {

            $result = Http::get($_dt['link'], array(), $extra);
            $this->log['statuses'][$_dt['link']] = Http::getStatus();

            if (Http::getStatus() == Http::STATUS_OK && !empty($result) && $product = $this->prsProduct($result)) {
                $product['item_id'] = $_dt['item_id'];
                $product['link'] = $_dt['link'];
                if ($product['price'] != $_dt['price']) {
                    $product['old_price'] = $_dt['price'];
                } else {
                    $product['old_price'] = $_dt['old_price'];
                }
                $this->products[] = $product;
            } else {
                $to_delete[] = $_dt['item_id'];
            }
            if (!empty(self::$parse_page_step) && count($this->products) == self::$parse_page_step) {
                $this->saveProducts();
            }
            if (!empty(self::$parse_page_step) && count($to_delete) == self::$parse_page_step) {
                db_query("DELETE FROM ?:competitive_prices WHERE item_id IN (?n)", $to_delete);
                db_query("DELETE FROM ?:competitive_pairs WHERE competitive_id IN (?n)", $to_delete);
                $to_delete = array();
            }
            fn_echo(' . ');
            if (!empty($_SESSION['cmp_update_start']) && !empty(self::$update_duration_limit) && $_SESSION['cmp_update_start'] + self::$update_duration_limit < time()) {
                break;
            }
        }

        $this->saveProducts();
        if (!empty($to_delete)) {
            db_query("DELETE FROM ?:competitive_prices WHERE item_id IN (?n)", $to_delete);
            db_query("DELETE FROM ?:competitive_pairs WHERE competitive_id IN (?n)", $to_delete);
        }

        return array($success, $this->log);
    }
}
