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

namespace Tygh;

class Memcached
{
    private static $Memcached;
    private static $MemcachedServer;
    private static $_defaultDuration = 300;

    public static function instance()
    {
        if (empty(self::$Memcached)) {
            self::$Memcached = new Memcached();
            self::$MemcachedServer = new \Memcached();
            self::$MemcachedServer->addServer("localhost", 11211);
        }

        return self::$Memcached;
    }

    public function delete($key)
    {
        if ( !empty($key) ) {
            self::$MemcachedServer->delete($key);
        }
    }

    public function flush($delay = 0) {
        self::$MemcachedServer->flush($delay);
    }
    
    public function get($variable) {
        return self::$MemcachedServer->get($variable);
    }

    public function getResultCode() {
        return self::$MemcachedServer->getResultCode();
    }

    public function getResultMessage() {
        return self::$MemcachedServer->getResultMessage();
    }

    public function validateSql($query)
    {
        // $ignore_list = array("?:logs","?:product_prices","?:sessions","?:settings_","?:stat_","?:storage_data","?:stored_sessions","?:ult_","?:user_data","?:user_session_products");
        $ignore_list = array(DEFAULT_TABLE_PREFIX . 'stored_sessions', DEFAULT_TABLE_PREFIX . 'logs', DEFAULT_TABLE_PREFIX . 'sessions', DEFAULT_TABLE_PREFIX . 'stat_', DEFAULT_TABLE_PREFIX . 'user_data', DEFAULT_TABLE_PREFIX . 'user_session_products', DEFAULT_TABLE_PREFIX . 'users', DEFAULT_TABLE_PREFIX . 'views');

        foreach($ignore_list as $string) {
            if ( strpos($query, $string) !== false ) {
                return NULL;
            }
        }

        if ( preg_match("/^SELECT\s+/i",trim($query)) ) {
            $hash = md5($query);
            return $hash;
        }

        return NULL;
    }

    public function set($key, $value, $duration = 0)
    {
        if ( !( $result = self::$MemcachedServer->set($key, $value, $duration) ) ) {
        }
        return $result;
    }
}
