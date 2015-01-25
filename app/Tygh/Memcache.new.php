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

class Memcache
{
    private static $_instance;
    private $MemcachedServer;
    private $LastDBKeySet;
    private $LastDBKeyGet;

    public static function instance()
    {
        if (empty(self::$_instance)) {
            self::$_instance = new Memcache();
            if (class_exists('Memcache')) {
                self::$_instance->MemcachedServer = new \Memcache();
                self::$_instance->MemcachedServer->addServer("localhost", 11211);
                // GC
                self::$_instance->call('gcAll');
            }
        }

        return self::$_instance;
    }

    public function call($func)
    {
        if (!$this->MemcachedServer) {
            return false;
        }
        return call_user_func_array(array($this, $func), array_slice(func_get_args(), 1));
    }
    
    private function delete($key)
    {
        if ( !empty($key) ) {
            $this->MemcachedServer->delete($key);
        }
    }

    private function getAllKeys()
    {
        return $this->MemcachedServer->getAllkeys();
    }

    public function getMemcacheKeys($limit = 10000)
    {
        $keysFound = array();

        $slabs = $this->MemcachedServer->getExtendedStats('slabs');
        if (!empty($slabs)) {
            foreach ($slabs as $serverSlabs) {
                if (!empty($serverSlabs)) {
                    foreach ($serverSlabs as $slabId => $slabMeta) {
                        try {
                            $cacheDump = $this->MemcachedServer->getExtendedStats('cachedump', (int) $slabId, 1000);
                        } catch (Exception $e) {
                            continue;
                        }

                        if (!is_array($cacheDump)) {
                            continue;
                        }

                        if (!empty($cacheDump)) {
                            foreach ($cacheDump as $dump) {

                                if (!is_array($dump)) {
                                    continue;
                                }

                                if (!empty($dump)) {
                                    foreach ($dump as $key => $value) {
                                        $keysFound[$key] = $value[1];

                                        if (count($keysFound) == $limit) {
                                            return $keysFound;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        fn_print_die($keysFound);

        return $keysFound;
    }
    
    private function flush()
    {
        $this->MemcachedServer->flush();
    }
    
    private function gcAll()
    {
        $contents = unserialize($this->MemcachedServer->get('contents'));
        if (!empty($contents)) {
            foreach ($contents as $type => $content) {
                foreach ($content as $key => $exp) {
                    if (!empty($exp) && $exp < time()) {
                        unset($contents[$type][$key]);
                    }
                }
            }
        }
        $this->MemcachedServer->set('contents', serialize($contents), MEMCACHE_COMPRESSED);
    }
    
    private function clear($type = 'D')
    {
        $contents = unserialize($this->MemcachedServer->get('contents'));
        if (!empty($contents[$type])) {
            foreach ($contents[$type] as $key => $exp) {
                $this->MemcachedServer->delete($key);
            }
            unset($contents[$type]);
        }
        $this->MemcachedServer->set('contents', serialize($contents), MEMCACHE_COMPRESSED);
    }
    
    private function get($key, $type = '')
    {
        if (!empty($type)) {
            if ($type == 'D') {
                if ($key != 'found_rows') {
                    $this->LastDBKeyGet = $key;
                } else {
                    return unserialize($this->MemcachedServer->get($this->LastDBKeyGet . $key));
                }
            }
            $status = unserialize($this->MemcachedServer->get($type . '_status'));
            if (empty($status)) {
                return false;
            }
        }
        
        return unserialize($this->MemcachedServer->get($key));
    }

//     private function getResultCode()
//     {
//         return $this->MemcachedServer->getResultCode();
//     }
// 
//     private function getResultMessage()
//     {
//         return $this->MemcachedServer->getResultMessage();
//     }
// 
    private function validateSql($query)
    {
        // $ignore_list = array("?:logs","?:product_prices","?:sessions","?:settings_","?:stat_","?:storage_data","?:stored_sessions","?:ult_","?:user_data","?:user_session_products");
        $ignore_list = array(DEFAULT_TABLE_PREFIX . 'logs', DEFAULT_TABLE_PREFIX . 'stored_sessions', DEFAULT_TABLE_PREFIX . 'sessions', DEFAULT_TABLE_PREFIX . 'stat_', DEFAULT_TABLE_PREFIX . 'user_session_products', DEFAULT_TABLE_PREFIX . 'views', DEFAULT_TABLE_PREFIX . 'storage_data', DEFAULT_TABLE_PREFIX . 'user_data');

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

    private function set($key, $value, $type = '', $duration = 0)
    {
        if ($type == 'D' && $key == 'found_rows') {
            $result = $this->MemcachedServer->set($this->LastDBKeySet . $key, serialize($value), MEMCACHE_COMPRESSED, $duration);
        } else {
            $result = $this->MemcachedServer->set($key, serialize($value), MEMCACHE_COMPRESSED, $duration);
        }
        if ($result) {
            $this->MemcachedServer->set($type . '_status', serialize(true), MEMCACHE_COMPRESSED);
            if ($type == 'D' && $key != 'found_rows') {
                $this->LastDBKeySet = $key;
            }
            $contents = unserialize($this->MemcachedServer->get('contents'));
            $contents[$type][$key] = $duration;
            $this->MemcachedServer->set('contents', serialize($contents), MEMCACHE_COMPRESSED);
        } else {
            $this->MemcachedServer->set($type . '_status', serialize(false), MEMCACHE_COMPRESSED);
        }
        return $result;
    }
}
