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

namespace Tygh\Ym;

use Tygh\Registry;

class Yml implements IYml
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
        $path = sprintf('%syandex_market/%s_yandex_market.yml',
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
        $yml_header = array(
            '<?xml version="1.0" encoding="' . $this->options['export_encoding'] . '"?>',
            '<!DOCTYPE yml_catalog SYSTEM "shops.dtd">',
            '<yml_catalog date="' . date('Y-m-d G:i') . '">',
            '<shop>'
        );

        $yml_data = array(
            'name' => $this->getShopName(),
            'company' => Registry::get('settings.Company.company_name'),
            'url' => Registry::get('config.http_location'),
            'platform' => 'Tennis-Cart',
            'version' => '1.0',
            'agency' => 'Agency',
            'email' => Registry::get('settings.Company.company_orders_department'),
        );

        $this->buildCurrencies($yml_data);

        $this->buildCategories($yml_data);

        if ($global_local_delivery = $this->options['global_local_delivery_cost']) {
            $yml_data['local_delivery_cost'] = $global_local_delivery;
        }

        fwrite($file, implode(PHP_EOL, $yml_header) . PHP_EOL);
        fwrite($file, fn_yandex_market_array_to_yml($yml_data));
        fwrite($file, '<offers>' . PHP_EOL);
    }

    protected function body($file)
    {
        $offered = array();

        if ($this->options['disable_cat_d'] == "Y") {
            $visible_categories = $this->getVisibleCategories();
        }

        $params = array(
            'yml_export_yes' => 'Y'
        );
        list($products, ) = fn_get_products($params);

        $offset = 0;
        while ($prods_slice = array_slice($products, $offset, self::ITERATION_ITEMS)) {
            $offset += self::ITERATION_ITEMS;
            $ids = array();
            foreach ($prods_slice as $k => &$product) {
                $ids[] = $product['product_id'];
            }
            $products_images_main = fn_get_image_pairs($ids, 'product', 'M', false, true, $this->lang_code);
            $products_images_additional = fn_get_image_pairs($ids, 'product', 'A', false, true, $this->lang_code);

            foreach ($prods_slice as $k => &$product) {
                $is_broken = false;

                $product['price'] = fn_parse_price($product['price']);

                if (empty($product['price'])) {
                    $is_broken = true;
                }

                if (in_array($product['main_category'], $this->disabled_category_ids)) {
                    $is_broken = true;
                }

                if ($this->options['disable_cat_d'] == 'Y' && !in_array($product['main_category'], $visible_categories)) {
                    $is_broken = true;
                }

                $product['product'] = $this->escape($product['product']);
                $product['full_description'] = $this->escape($product['full_description']);
                $product['product_features'] = $this->getProductFeatures($product);
                
                if (!empty($product['product_features'])) {
                    $product['full_description'] = '';
                    foreach ($product['product_features'] as $i => $f_data) {
                        $product['full_description'] .= (empty($product['full_description']) ? '' : '; ') . $f_data['description'] . ':' . $f_data['value'];
                    }
                }
                $product['brand'] = $this->getBrand($product);

                if ($this->options['export_type'] == 'vendor_model') {
                    if (empty($product['brand']) || empty($product['yml_model'])) {
                        $is_broken = true;
                    }
                }

                if ($product['tracking'] == 'O') {
                    $product['amount'] = db_get_field(
                        "SELECT SUM(amount) FROM ?:product_options_inventory WHERE product_id = ?i",
                        $product['product_id']
                    );
                }

                if ($this->options['export_stock'] == 'Y' && $product['amount'] <= 0) {
                    $is_broken = true;
                }

                if ($is_broken) {
                    unset($prods_slice[$k]);
                    continue;
                }
                $product['product_url'] = fn_url('products.view?product_id=' . $product['product_id']);

                // Images
                $images = array_merge(
                    $products_images_main[$product['product_id']],
                    $products_images_additional[$product['product_id']]
                );
                $product['images'] = array_slice($images, 0, self::IMAGES_LIMIT);

                if ($this->options['market_category'] == "Y" && empty($product['yml_market_category']) && !empty($product['all_path'])) {
                    $id_path = $cats = array();
                    foreach ($product['all_path'] as $i => $path) {
                        $cats[$i] = explode('/', $path);
                        $id_path = array_merge($id_path, explode('/', $path));
                    }
                    $main = $cats[$product['main_category']];
                    unset($cats[$product['main_category']]);
                    $invert = array();
                    foreach (array_reverse($main) as $i => $cid) {
                        $invert[$i][] = $cid;
                    }
                    if (!empty($cats)) {
                        foreach ($cats as $_i => $cids) {
                            foreach (array_reverse($cids) as $i => $cid) {
                                $invert[$i][] = $cid;
                            }
                        }
                    }
                    $_data = db_get_hash_array("SELECT category_id, yml_market_category FROM ?:categories WHERE category_id IN (?n)", 'category_id', array_unique($id_path));
                    foreach ($invert as $i => $cat_ids) {
                        foreach ($cat_ids as $n => $cat_id) {
                            if (!empty($_data[$cat_id]['yml_market_category'])) {
                                $product['yml_market_category'] = $_data[$cat_id]['yml_market_category'];
                                break 2;
                            }
                        }
                    }
                }

                if ($product['yml_cost'] == 0) {
                    if ($product['price'] < Registry::get('addons.development.free_shipping_cost')) {
                        $product['yml_cost'] = $this->options['global_local_delivery_cost'];
                    }
                }
                list($key, $value) = $this->offer($product);
                $offered[$key] = $value;
            }

            fwrite($file, fn_yandex_market_array_to_yml($offered));
            unset($offered);

        }
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

    protected function getShopName()
    {
        $shop_name = $this->options['shop_name'];

        if (empty($shop_name)) {
            if (fn_allowed_for('ULTIMATE')) {
                $shop_name = fn_get_company_name($this->company_id);
            } else {
                $shop_name = Registry::get('settings.Company.company_name');
            }
        }

        return strip_tags($shop_name);
    }

    protected function buildCurrencies(&$yml_data)
    {
        $currencies = Registry::get('currencies');

        if (CART_PRIMARY_CURRENCY != "RUB") {

            $rub_coefficient = !empty($currencies['RUB']) ? $currencies['RUB']['coefficient'] : 1;
            $primary_coefficient = $currencies[CART_PRIMARY_CURRENCY]['coefficient'];

            foreach ($currencies as $cur) {
                if ($this->currencyIsValid($cur['currency_code']) && $cur['status'] == 'A') {
                    if ($cur['currency_code'] == 'RUB') {
                        $coefficient = '1.0000';
                        $yml_data['currencies']['currency@id=' . $cur['currency_code'] . '@rate=' . $coefficient] = '';

                    } else {
                        $coefficient = $cur['coefficient'] * $primary_coefficient / $rub_coefficient;
                        $yml_data['currencies']['currency@id=' . $cur['currency_code'] . '@rate=' . $coefficient] = '';
                    }
                }
            }

        } else {
            foreach ($currencies as $cur) {
                if ($this->currencyIsValid($cur['currency_code']) && $cur['status'] == 'A') {
                    $yml_data['currencies']['currency@id=' . $cur['currency_code'] . '@rate=' . $cur['coefficient']] = '';
                }
            }
        }
    }

    protected function currencyIsValid($currency)
    {
        $currencies = array(
            'RUR',
            'RUB',
            'UAH',
            'BYR',
            'KZT',
            'USD',
            'EUR'
        );

        return in_array($currency, $currencies);
    }

    protected function buildCategories(&$yml_data)
    {
        $params = array (
            'simple' => false,
            'plain' => true,
            'skip_filter' => true
        );

        if ($this->options['disable_cat_d'] == "Y") {
            $params['status'] = array('A', 'H');
        }

        $disable_cat_full_list = $this->getDisabledCategories();

        list($categories_tree, ) = fn_get_categories($params, $this->lang_code);

        foreach ($categories_tree as $cat) {
            if (isset($cat['category_id']) && !in_array($cat['category_id'], $disable_cat_full_list)) {

                if ($cat['parent_id'] == 0) {
                    $yml_data['categories']['category@id=' . $cat['category_id']] = $cat['category'];

                } else {
                    $yml_data['categories']['category@id=' . $cat['category_id'] . '@parentId=' . $cat['parent_id']] = $cat['category'];
                }
            }
        }
    }

    protected function getDisabledCategories()
    {
        if (!isset($this->disabled_category_ids)) {
            $this->disabled_category_ids = array();
            $disable_categories_list = db_get_fields("SELECT id_path FROM ?:categories WHERE yml_disable_cat = ?s", 'Y');
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

            if ($this->options['disable_cat_d'] == "Y") {
                $params['plain'] = true;
                $params['status'] = array('A', 'H');
                list($categories_tree, ) = fn_get_categories($params);

                if (!empty($categories_tree)) {
                    foreach ($categories_tree as $value) {
                        if (isset($value['category_id'])) {
                            $visible_categories[] = $value['category_id'];
                        }
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

    protected function offer($product)
    {
        $yml_data = array();
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
