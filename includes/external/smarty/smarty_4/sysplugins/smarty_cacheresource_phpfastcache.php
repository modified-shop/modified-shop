<?php

/**
 * phpFastCache CacheResource
 *
 * CacheResource Implementation based on the KeyValueStore API to use
 * phpFastCache as the storage resource for Smarty's output caching.
 * *
 * @package CacheResource-examples
 */
require_once(DIR_FS_CATALOG.'includes/classes/modified_cache.php');

class Smarty_CacheResource_Phpfastcache extends Smarty_CacheResource_KeyValueStore {
    
    private $cache = null;
    
    public function __construct()
    {
        global $modified_cache;

        include(DIR_FS_CATALOG.'includes/modified_cache.php');

        $this->cache = $modified_cache;
        $this->prefix = 'tpl_';
    }

    /**
     * Read values for a set of keys from cache
     *
     * @param array $keys list of keys to fetch
     * @return array list of values with the given keys used as indexes
     * @return boolean true on success, false on failure
     */
    protected function read(array $keys)
    {
        $_keys = $_res = array();
        foreach ($keys as $k) {
            $_k = $this->prefix.sha1($k);
            $this->cache->setID($_k);
            $_res[$k] = $this->cache->get();
        }
        return $_res;
    }
    
    /**
     * Save values for a set of keys to cache
     *
     * @param array $keys list of values to save
     * @param int $expire expiration time
     * @return boolean true on success, false on failure
     */
    protected function write(array $keys, $expire = DB_CACHE_EXPIRE)
    {
       foreach ($keys as $k => $v) {
            $_k = $this->prefix.sha1($k);
            $this->cache->setID($_k);
            $this->cache->set($v, $expire);
            $this->cache->setTags(array('template'));
        }
        return true;
    }

    /**
     * Remove values from cache
     *
     * @param array $keys list of keys to delete
     * @return boolean true on success, false on failure
     */
    protected function delete(array $keys)
    {
        foreach ($keys as $k) {
            $this->cache->delete($this->prefix.$k);
        }
        return true;
    }

    /**
     * Remove *all* values from cache
     *
     * @return boolean true on success, false on failure
     */
    protected function purge()
    {        
        return $this->cache->clear();
    }
}
