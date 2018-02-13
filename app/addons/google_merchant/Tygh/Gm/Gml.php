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

namespace Tygh\Gm;

use Tygh\Registry;

class Gml implements IGml
{

    const ITERATION_ITEMS = 100;
    const IMAGES_LIMIT = 10;

    protected $company_id;
    protected $options = array();
    protected $lang_code = DESCR_SL;
    protected $page = 0;
    protected $debug = false;

    protected $disabled_category_ids = array();

    public function __construct($company_id, $options = array(), $lang_code = DESCR_SL, $page = 0, $debug = false)
    {
        $this->company_id = $company_id;
        $this->options    = $options;
        $this->lang_code  = $lang_code;
        $this->page       = $page;
        $this->debug      = $debug;
    }

    public function get()
    {
        $filename = $this->getFileName();

        if (!file_exists($filename) || $this->debug) {
            $this->generate($filename);
        }

        $this->sendResult($filename);
    }

    public function getFileName()
    {
        $path = sprintf('%sgoogle_merchant/%s_google_merchant.yml',
            fn_get_cache_path(false, 'C', $this->company_id),
            $this->company_id
        );

        return $path;
    }

    public function clearCache()
    {
        return fn_rm($this->getFileName());
    }

    public static function clearCaches($company_ids = null)
    {
        if (is_null($company_ids)) {
            if (Registry::get('runtime.company_id') || Registry::get('runtime.simple_ultimate')) {
                $company_ids = Registry::get('runtime.company_data.company_id');
            } else {
                $company_ids = array_keys(fn_get_short_companies());
            }
        }

        foreach ((array) $company_ids as $company_id) {
            $self = new self($company_id);
            $self->clearCache();
        }
    }

    public function generate($filename)
    {
        @ignore_user_abort(1);
        @set_time_limit(0);

        fn_mkdir(dirname($filename));
        $file = fopen($filename, 'wb');

        $this->head($file);
        $this->body($file);
        $this->bottom($file);

        fclose($file);
    }

    protected function head($file)
    {
        $gml_header = array(
            '<?xml version="1.0" encoding="' . $this->options['export_encoding'] . '"?>',
            '<rss xmlns:g="http://base.google.com/ns/1.0" version="2.0">',
            '<channel>'
        );

        $gml_data = array(
            'title' => Registry::get('settings.Company.company_name'),
            'link' => Registry::get('config.http_location'),
            'description' => 'Tennis-Cart',
        );

        $this->getDisabledCategories();
        
        fwrite($file, implode(PHP_EOL, $gml_header) . PHP_EOL);
        fwrite($file, fn_array_to_xml($gml_data));
        fwrite($file, '<offers>' . PHP_EOL);
    }

    protected function body($file)
    {
        $offered = array();

        if ($this->options['hide_disabled_categories'] == "Y") {
            $visible_categories = $this->getVisibleCategories();
        }

        $params = array(
            'gml_disable_product' => 'N'
        );
        list($products, ) = fn_get_products($params);

        fn_gather_additional_products_data($products, array(
            'get_icon' => false,
            'get_detailed' => true,
            'get_additional' => true,
            'get_options'=> false,
            'get_discounts' => true,
            'get_features' => true,
            'get_title_features' => false,
            'allow_duplication' => false
        ));
        $brands = fn_get_product_feature_data(BRAND_FEATURE_ID, true, true);
// fn_print_die($products);
        $offset = 0;
        while ($prods_slice = array_slice($products, $offset, self::ITERATION_ITEMS)) {
            $offset += self::ITERATION_ITEMS;

            foreach ($prods_slice as $k => &$product) {
                $new_product = array();
                $is_broken = false;

                $google_product_category = !empty($product['gml_product_category']) ? $product['gml_product_category'] : fn_get_product_global_data($product, array('gml_product_category'));
                $brand = $product['product_features'][BRAND_FEATURE_ID]['variant'];
                if (empty($product['price']) || in_array($product['main_category'], $this->disabled_category_ids) || ($this->options['hide_disabled_categories'] == 'Y' && !in_array($product['main_category'], $visible_categories)) || ($this->options['in_stock_only'] == 'Y' && $product['amount'] <= 0) || empty($google_product_category) || empty($product['main_pair']['detailed']['http_image_path']) || empty($brand)) {
                    $is_broken = true;
                }

                // Basic product data
                $new_product['g:id'] = $product['product_id'];
                $new_product['g:title'] = $this->escape($product['product']);
                $new_product['g:description'] = !empty($product['full_description']) ? $this->escape($product['full_description']) : (!empty($product['short_description']) ? $this->escape($product['short_description']) : '');
                $new_product['g:link'] = fn_url('products.view?product_id=' . $product['product_id']);
                $new_product['g:image_​​link'] = $product['main_pair']['detailed']['http_image_path'];
                if (!empty($product['image_pairs'])) {
                    $additional = array_slice($product['image_pairs'], 0, 10);
                    foreach ($additional as $i => $pair)  {
                        if (!empty($pair['detailed']['http_image_path'])) {
                            $new_product['g:additional_​​​image_​​​link'] = $pair['detailed']['http_image_path'];
                        }
                    }
                }
                
                // Price & availability
                $new_product['g:availability'] = $product['amount'] > 0 ? 'in stock' : 'out of stock';
                if (!empty($product['discount'])) {
                    $new_product['g:price'] = $this->formatPrice($product['base_price']);
                    $new_product['g:sale_price'] = $this->formatPrice($product['price']);
                } elseif (!empty($product['list_discount'])) {
                    $new_product['g:price'] = $this->formatPrice($product['list_price']);
                    $new_product['g:sale_price'] = $this->formatPrice($product['price']);
                } else {
                    $new_product['g:price'] = $this->formatPrice($product['price']);
                }

                // Product category
                $new_product['g:google_product_category'] = $google_product_category;
                
                // Product identifiers
                $new_product['g:google_product_category'] = $product['product_id'];
                $new_product['g:brand'] = $brand;
                $new_product['g:identifier_​exists'] = 'no';
                
                // Detailed product description
                $new_product['g:condition'] = 'new';
                $new_product['g:adult'] = 'no';
                
                fn_print_die(fn_array_to_xml($new_product));

                if ($is_broken) {
                    unset($prods_slice[$k]);
                    continue;
                }

                list($key, $value) = $this->item($product);
                $offered[$key] = $value;
            }

            fwrite($file, fn_array_to_xml($offered));
            unset($offered);

        }
        fn_print_die($products);
    }

    protected function bottom($file)
    {
        fwrite($file, '</offers>' . PHP_EOL);
        fwrite($file, '</shop>' . PHP_EOL);
        fwrite($file, '</yml_catalog>' . PHP_EOL);
    }

    protected function sendResult($filename)
    {
        header("Content-Type: text/xml;charset=" . $this->options['export_encoding']);

        readfile($filename);
        exit;
    }

    protected function formatPrice($price, $currency_code = CART_SECONDARY_CURRENCY)
    {
        $currencies = Registry::get('currencies');
        $currency = $currencies[$currency_code];
        $result = fn_format_rate_value($price, 'F', $currency['decimals'], $currency['decimals_separator'], $currency['thousands_separator'], $currency['coefficient']);
        if ($currency['after'] == 'Y') {
            $result .= ' ' . $currency['currency_code'];
        } else {
            $result = $currency['currency_code'] . $result;
        }
    
        return $result;
    }
    
    protected function getDisabledCategories()
    {
        if (!isset($this->disabled_category_ids)) {
            $this->disabled_category_ids = array();
            $disable_categories_list = db_get_fields("SELECT id_path FROM ?:categories WHERE gml_disable_cat = ?s", 'Y');
            if (!empty($disable_categories_list)) {
                $like_path = "id_path LIKE '" . implode("%' OR id_path LIKE '", $disable_categories_list) . "%'"; // id_path LIKE '166/196%' OR id_path LIKE '203/204/212%' ...
                $this->disabled_category_ids = db_get_fields("SELECT category_id FROM ?:categories WHERE ?p", $like_path);
            }
        }

        return $this->disabled_category_ids;
    }

    /**
     * Export product features
     */
    protected function getProductFeatures($product)
    {
        static $features;

        $lang_code = $this->lang_code;

        if (!isset($features[$lang_code])) {
            list($features[$lang_code]) = fn_get_product_features(array('plain' => true), 0, $lang_code);
        }

        $product = array(
            'product_id' => $product['product_id'],
            'main_category' => $product['main_category']
        );

        $product_features = fn_get_product_features_list($product, 'A', $lang_code);

        $result = array();

        if (!empty($product_features)) {
            foreach ($product_features as $f) {
                $display_on_catalog = $features[$lang_code][$f['feature_id']]['display_on_catalog'];
                $display_on_product = $features[$lang_code][$f['feature_id']]['display_on_product'];

                if ($display_on_catalog == "Y" || $display_on_product == "Y") {
                    if ($f['feature_type'] == "C") {
                        $result[] = array(
                            'description' => $f['description'],
                            'feature_id' => $f['feature_id'],
                            'value' => ($f['value'] == "Y") ? __("yes") : __("no")
                        );
                    } elseif ($f['feature_type'] == "S" && !empty($f['variant'])) {
                        $result[] = array(
                            'description' => $f['description'],
                            'feature_id' => $f['feature_id'],
                            'value' => $f['variant']
                        );
                    } elseif ($f['feature_type'] == "T" && !empty($f['value'])) {
                        $result[] = array(
                            'description' => $f['description'],
                            'feature_id' => $f['feature_id'],
                            'value' => $f['value']
                        );
                    } elseif ($f['feature_type'] == "M") {
                        if (!empty($f['variants'])) {
                            $_value = '';
                            $counter = count($f['variants']);
                            foreach ($f['variants'] as $_variant) {
                                if ($counter > 1) {
                                    $_value .= $_variant['variant'] . ', ';
                                } else {
                                    $_value = $_variant['variant'];
                                }
                            }
                            $_value = ($counter > 1) ? substr($_value, 0, -2) : $_value;
                            $result[] = array(
                                'description' => $f['description'],
                                'feature_id' => $f['feature_id'],
                                'value' => $_value
                            );
                        }
                    } elseif ($f['feature_type'] == "N") {
                        $result[] = array(
                            'description' => $f['description'],
                            'feature_id' => $f['feature_id'],
                            'value' => $f['variant']
                        );
                    } elseif ($f['feature_type'] == "O") {
                        $result[] = array(
                            'description' => $f['description'],
                            'feature_id' => $f['feature_id'],
                            'value' => $f['value_int']
                        );
                    } elseif ($f['feature_type'] == "E") {
                        $result[] = array(
                            'description' => $f['description'],
                            'feature_id' => $f['feature_id'],
                            'value' => $f['variant']
                        );
                    }
                }
            }
        }

        return !empty($result) ? $result : '';
    }

    protected function getImageUrl($image_pair)
    {
        $url = '';

        if ($this->options['image_type'] == 'detailed') {
            $url = $image_pair['detailed']['image_path'];
        } else {
            $image_data = fn_image_to_display(
                $image_pair,
                $this->options['thumbnail_width'],
                $this->options['thumbnail_height']
            );

            if (!empty($image_data) && strpos($image_data['image_path'], '.php')) {
                $image_data['image_path'] = fn_generate_thumbnail(
                    $image_data['detailed_image_path'],
                    $image_data['width'],
                    $image_data['height']
                );
            }

            if (!empty($image_data['image_path'])) {
                $url = $image_data['image_path'];
            }
        }

        $url = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');

        $url = fn_yandex_market_c_encode($url);

        return str_replace('–', urlencode('–'), $url);
    }

    protected function getVisibleCategories()
    {
        $visible_categories = null;

        if (!isset($visible_categories)) {
            $visible_categories = array();

            $params = array(
                'plain' => true,
                'skip_default_condition' => true,
                'status' => array('A', 'H')
            );
            list($categories_tree, ) = fn_get_categories($params);

            if (!empty($categories_tree)) {
                foreach ($categories_tree as $value) {
                    if (isset($value['category_id'])) {
                        $visible_categories[] = $value['category_id'];
                    }
                }
            }
        }

        return $visible_categories;
    }

    protected function getMarketCategories()
    {
        static $market_categories = null;

        if (!isset($market_categories)) {
            $market_categories = array();

            if ($this->options['market_category'] == "Y" && $this->options['market_category_object'] == "category") {
                $market_categories = db_get_hash_single_array(
                    "SELECT category_id, yml_market_category FROM ?:categories WHERE yml_market_category != ?s",
                    array('category_id', 'yml_market_category'), ''
                );
            }
        }

        return $market_categories;
    }

    protected function getBrand($product)
    {
        $brand = '';

        if (!empty($product['yml_brand'])) {
            $brand = $product['yml_brand'];

        } elseif (!empty($product['product_features'])) {

            $feature_for_brand = $this->options['feature_for_brand'];
            $brands = array();

            if (!empty($feature_for_brand)) {

                foreach ($feature_for_brand as $brand_name => $check) {
                    if ($check == 'Y') {
                        $brands[] = $brand_name;
                    }
                }
                $brands = array_unique($brands);
            }

            foreach ($product['product_features'] as $feature) {
                if (in_array($feature['feature_id'], $brands)) {
                    $brand = $feature['value'];
                    break;
                }
            }
        }

        return $brand;
    }

    protected function item($product)
    {
        $gml_data = array();
        $offer_attrs = '';

        if (!empty($product['yml_bid'])) {
            $offer_attrs .= '@bid=' . $product['yml_bid'];
        }

        if (!empty($product['yml_cbid'])) {
            $offer_attrs .= '@cbid=' . $product['yml_cbid'];
        }

        $price_fields = array('price', 'yml_cost', 'list_price');

        if (CART_PRIMARY_CURRENCY != "RUB") {
            $currencies = Registry::get('currencies');
            if (isset($currencies['RUB'])) {
                $currency = $currencies['RUB'];
                foreach ($price_fields as $field) {
                    $product[$field] = fn_format_rate_value(
                        $product[$field],
                        'F',
                        $currency['decimals'],
                        $currency['decimals_separator'],
                        $currency['thousands_separator'],
                        $currency['coefficient']
                    );
                }
            }
        }

        foreach ($price_fields as $field) {
            $product[$field] = floatval($product[$field]) ? $product[$field] : fn_parse_price($product[$field]);
        }

        $yml_data['url'] = $product['product_url'];

        $yml_data['price'] = $product['price'];
        if (!empty($product['list_price']) && $product['price'] < $product['list_price']) {
            $yml_data['oldprice'] = $product['list_price'];
        }
        $yml_data['currencyId'] = "RUB";
        $yml_data['categoryId@type=Own'] = $product['main_category'];

        if ($this->options['market_category'] == "Y" && !empty($product['yml_market_category'])) {
            $yml_data['market_category'] = $product['yml_market_category'];
        }

        // Images
        $picture_index = 0;
        while ($image = array_shift($product['images'])) {
            $key = 'picture';
            if ($picture_index) {
                $key .= '+' . $picture_index;
            }
            $yml_data[$key] = $this->getImageUrl($image);

            $picture_index ++;
        }

        $yml_data['store'] = ($product['yml_store'] == 'Y' ? 'true' : 'false');
        $yml_data['pickup'] = ($product['yml_pickup'] == 'Y' ? 'true' : 'false');
        $yml_data['delivery'] = ($product['yml_delivery'] == 'Y' ? 'true' : 'false');
        if ($this->options['local_delivery_cost'] == "Y") {
            $yml_data['local_delivery_cost'] = $product['yml_cost'];
        }

        $type = '';
        if ($this->options['export_type'] == 'vendor_model') {

            $type = '@type=vendor.model';

            if ($this->options['type_prefix'] == "Y") {
                if (!empty($product['yml_type_prefix'])) {
                    $yml_data['typePrefix'] = $product['yml_type_prefix'];

                } else {
                    $yml_data['typePrefix'] = $product['category'];
                }
            }

            $yml_data['vendor'] = $product['brand'];
            if ($this->options['export_vendor_code'] == 'Y' && !empty($product['product_code'])) {
                $yml_data['vendorCode'] = $product['product_code'];
            }
            $yml_data['model'] = !empty($product['yml_model']) ? $product['yml_model'] : '';

        } elseif ($this->options['export_type'] == 'simple') {
            $yml_data['name'] = $product['product'];

            if (!empty($product['brand'])) {
                $yml_data['vendor'] = $product['brand'];
            }

            if ($this->options['export_vendor_code'] == 'Y' && !empty($product['product_code'])) {
                $yml_data['vendorCode'] = $product['product_code'];
            }
        }

        if (!empty($product['full_description'])) {
            $yml_data['description'] = $product['full_description'];
        }

        if (!empty($product['yml_sales_notes'])) {
            $yml_data['sales_notes'] = $product['yml_sales_notes'];
        }

        if (!empty($product['yml_origin_country']) && fn_yandex_market_check_country($product['yml_origin_country'])) {
            $yml_data['country_of_origin'] = $product['yml_origin_country'];
        }

        if (!empty($product['yml_manufacturer_warranty'])) {
            $yml_data['manufacturer_warranty'] = $product['yml_manufacturer_warranty'];
        }

        if (!empty($product['yml_seller_warranty'])) {
            $yml_data['seller_warranty'] = $product['yml_seller_warranty'];
        }

        if (!empty($product['product_features'])) {
            foreach ($product['product_features'] as $feature) {
                $yml_data['param@name=' . $this->escape($feature['description'])] = $feature['value'];
            }
        }

        $avail = 'true';

        return array(
            'offer@id=' . $product['product_id'] . $type . '@available=' . $avail . $offer_attrs,
            $yml_data
        );
    }

    protected function getVendorCode($product)
    {
        if (!empty($product['product_features'])) {
            foreach ($product['product_features'] as $feature) {
                if ($feature['description'] == $this->options['feature_for_vendor_code']) {
                    return $feature['value'];
                }
            }
        }

        return '';
    }

    protected function escape($data)
    {
        return htmlspecialchars(strip_tags($data), ENT_QUOTES, 'UTF-8');
    }

}
