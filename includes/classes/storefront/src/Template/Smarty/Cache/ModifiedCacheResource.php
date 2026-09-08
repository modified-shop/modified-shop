<?php

namespace Modified\Storefront\Template\Smarty\Cache;

use InvalidArgumentException;
use Smarty\Cacheresource\KeyValueStore;
use Smarty\Smarty;

/**
 * Stores Smarty's output cache in the existing modified cache in a controlled manner.
 *
 * Smarty's KeyValueStore handles cache and compile identities, group invalidation, and locks.
 * This adapter maps only its logical keys to a dedicated cache namespace and delegates reads,
 * writes, and deletes to modified_cache.
 */
final class ModifiedCacheResource extends KeyValueStore
{
    private const KEY_PREFIX = 'smarty5_tpl_';
    private const CACHE_TAG = 'smarty5-template';

    private object $cache;

    /**
     * Creates an adapter for a cache object compatible with modified_cache.
     */
    public function __construct(object $cache)
    {
        $this->validateCacheCompatibility($cache);
        $this->cache = $cache;
    }

    /**
     * Normalizes unqualified names to the same resource identity Smarty uses when saving.
     */
    public function clear(Smarty $smarty, $resource_name, $cache_id, $compile_id, $exp_time): int
    {
        if (
            is_string($resource_name)
            && preg_match('/^[A-Za-z0-9_-]{2,}:/', $resource_name) !== 1
        ) {
            $resource_name = $smarty->default_resource_type . ':' . $resource_name;
        }

        return parent::clear($smarty, $resource_name, $cache_id, $compile_id, $exp_time);
    }

    /**
     * Reads each requested Smarty key from its namespaced modified cache entry.
     */
    protected function read(array $keys): array
    {
        $values = [];

        foreach ($keys as $key) {
            $this->cache->setID($this->storageKey((string) $key));
            $values[$key] = $this->cache->isHit()
                ? $this->cache->get()
                : null;
        }

        return $values;
    }

    /**
     * Writes Smarty values with the requested or configured lifetime.
     */
    protected function write(array $keys, $expire = null): bool
    {
        $lifetime = $this->lifetime($expire);
        $successful = true;

        foreach ($keys as $key => $value) {
            $this->cache->setID($this->storageKey((string) $key));
            if ($this->cache->set($value, $lifetime) === false) {
                $successful = false;
                continue;
            }
            if ($this->cache->setTags([self::CACHE_TAG]) === false) {
                $successful = false;
            }
        }

        return $successful;
    }

    /**
     * Deletes exactly the requested Smarty keys using the same mapping as read() and write().
     */
    protected function delete(array $keys): bool
    {
        $successful = true;

        foreach ($keys as $key) {
            if ($this->cache->delete($this->storageKey((string) $key)) === false) {
                $successful = false;
            }
        }

        return $successful;
    }

    private function storageKey(string $key): string
    {
        return self::KEY_PREFIX . sha1($key);
    }

    private function lifetime(mixed $expire): int
    {
        if ($expire === null) {
            $expire = defined('DB_CACHE_EXPIRE') && is_numeric(DB_CACHE_EXPIRE)
                ? DB_CACHE_EXPIRE
                : 3600;
        }

        return (int) $expire;
    }

    private function validateCacheCompatibility(object $cache): void
    {
        foreach (['setID', 'isHit', 'get', 'set', 'setTags', 'delete'] as $method) {
            if (!is_callable([$cache, $method])) {
                throw new InvalidArgumentException(sprintf(
                    'The cache object is not compatible with modified_cache: the method %s() is missing.',
                    $method
                ));
            }
        }
    }
}
