<?php

/**
 * phpFastCache CacheResource
 *
 * CacheResource Implementation based on the KeyValueStore API to use
 * phpFastCache as the storage resource for Smarty's output caching.
 * *
 * @package CacheResource-examples
 */
class Smarty_CacheResource_Phpfastcache extends Smarty_CacheResource_KeyValueStore {
    
    protected $phpfastcache = null;
    
    public function __construct()
    {
        require_once (DIR_FS_EXTERNAL . 'phpfastcache/phpfastcache.php');
        $this->phpfastcache = phpFastCache();        
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
            $_k = sha1($k);
            $_res[$k] = $this->phpfastcache->get($_k);
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
    protected function write(array $keys, $expire=null)
    {
       foreach ($keys as $k => $v) {
            $k = sha1($k);
            $this->phpfastcache->set($k, $v, $expire);
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
            $this->phpfastcache->delete($k);
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
        return $this->phpfastcache->clean();
    }
}
