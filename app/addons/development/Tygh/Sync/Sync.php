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

namespace Tygh\Sync;

use Tygh\Http;

class Sync
{
    protected $source;
    protected $feed;
    protected $agent;

    public function __construct($ag_id)
    {
        $this->agent = $ag_id;
        if ($this->agent == DRIADA_WAREHOUSE_ID) {
            $this->source = 'http://driada-sport.ru/data/files/XML_prise.xml';
        }
    }

    public function getFeed()
    {
        $result = Http::get($this->source);
        if (!empty($result)) {
            libxml_use_internal_errors(true);
            $xml = @simplexml_load_string($result);
            if ($xml !== false) {
                $this->feed = $xml;
                $parse_function  = 'parseAgent' . $this->agent;
                $parsed_data = $this->{$parse_function}();
            }
        }
    }

    private function parseAgent4()
    {
        $shop = $this->feed->shop;
        $result = array();
        if (!empty($shop)) {
            foreach($shop->categories->category as $cat) {
                $attributes = array();
                foreach($cat->attributes() as $key => $val) {
                    $attributes[(string) $key] = (int) $val;
                }
                $result['categories'][$attributes['id']] = $attributes;
                $result['categories'][$attributes['id']]['name'] = (string)$cat;
            }

            foreach($shop->offers->offer as $offer) {
                fn_print_die($offer);
            }
        }
        fn_print_die($result);
    }
}
