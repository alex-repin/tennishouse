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
    private static $LastDBKeySet;
    private static $LastDBKeyGet;
    private static $Memcached;
    private static $MemcachedServer;
    private static $_defaultDuration = 300;

    public static function instance()
    {
        if (empty(self::$Memcached)) {
            self::$Memcached = new Memcached();
            self::$MemcachedServer = new \Memcached();
            self::$MemcachedServer->addServer("localhost", 11211);
            // GC
            self::$Memcached->gcAll();
        }

        return self::$Memcached;
    }

    public function delete($key)
    {
        if ( !empty($key) ) {
            self::$MemcachedServer->delete($key);
        }
    }

    public function getAllKeys()
    {
        return self::$MemcachedServer->getAllkeys();
    }

    private function flush($delay = 0) {
        self::$MemcachedServer->flush($delay);
    }
    
    private function gcAll()
    {
        $contents = unserialize(self::$MemcachedServer->get('contents'));
        if (!empty($contents)) {
            foreach ($contents as $type => $content) {
                foreach ($content as $key => $exp) {
                    if (!empty($exp) && $exp < time()) {
                        unset($contents[$type][$key]);
                    }
                }
            }
        }
        self::$MemcachedServer->set('contents', serialize($contents));
    }
    
    private function gc($type)
    {
        $contents = unserialize(self::$MemcachedServer->get('contents'));
        if (!empty($contents[$type])) {
            foreach ($contents[$type] as $key => $exp) {
                if (!empty($exp) && $exp < time()) {
                    unset($contents[$type][$key]);
                }
            }
        }
        self::$MemcachedServer->set('contents', serialize($contents));
    }
    
    public function clear($type = 'D')
    {
        $contents = unserialize(self::$MemcachedServer->get('contents'));
        if (!empty($contents[$type])) {
            foreach ($contents[$type] as $key => $exp) {
                self::$MemcachedServer->delete($key);
            }
            unset($contents[$type]);
        }
        self::$MemcachedServer->set('contents', serialize($contents));
    }
    
    public function get($key, $type = '')
    {
        if (!empty($type)) {
            self::$Memcached->gc($type);
            if ($type == 'D') {
                if ($key != 'found_rows') {
                    self::$LastDBKeyGet = $key;
                } else {
                    return unserialize(self::$MemcachedServer->get(self::$LastDBKeyGet . $key));
                }
            }
            $status = unserialize(self::$MemcachedServer->get($type . '_status'));
            if (empty($status)) {
                return false;
            }
        }
        return unserialize(self::$MemcachedServer->get($key));
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
        $ignore_list = array(DEFAULT_TABLE_PREFIX . 'stored_sessions', DEFAULT_TABLE_PREFIX . 'logs', DEFAULT_TABLE_PREFIX . 'sessions', DEFAULT_TABLE_PREFIX . 'stat_', DEFAULT_TABLE_PREFIX . 'user_data', DEFAULT_TABLE_PREFIX . 'user_session_products', DEFAULT_TABLE_PREFIX . 'users', DEFAULT_TABLE_PREFIX . 'views', DEFAULT_TABLE_PREFIX . 'storage_data');

        foreach($ignore_list as $string) {
            if ( strpos($query, $string) !== false ) {
                return false;
            }
        }

        if ( strpos($query, "FOUND_ROWS()") !== false) {
            return 'found_rows';
        } elseif ( preg_match("/^SELECT\s+/i",trim($query)) ) {
            $hash = md5($query);
            return $hash;
        }

        return false;
    }

    public function set($key, $value, $type = '', $duration = 0)
    {
        if ($type == 'D' && $key == 'found_rows') {
            $result = self::$MemcachedServer->set(self::$LastDBKeySet . $key, serialize($value), $duration);
        } else {
            $result = self::$MemcachedServer->set($key, serialize($value), $duration);
        }
        if ($result) {
            self::$MemcachedServer->set($type . '_status', serialize(true));
            if ($type == 'D' && $key != 'found_rows') {
                self::$LastDBKeySet = $key;
            }
            $contents = unserialize(self::$MemcachedServer->get('contents'));
            $contents[$type][$key] = $duration;
            self::$MemcachedServer->set('contents', serialize($contents));
        } else {
            self::$MemcachedServer->set($type . '_status', serialize(false));
        }
        return $result;
    }
}
