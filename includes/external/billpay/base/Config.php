<?php
/**
 * object which contains the shop system specific config
 *
 * @category   Billpay
 * @package    Billpay\Config
 * @link       https://www.billpay.de/
 */
class Billpay_Base_Config
{
    /**
     * @var string default config file to load
     */
    var $DEFAULT_CONFIG_FILE = 'billpay_conf.php';

    /**
     * @var string default separator for the key
     */
    var $KEY_SEPARATOR = '.';

    /**
     * @var array holding the loaded configuration array
     */
    var $config = array();

    /**
     * @var array used to cache config values
     */
    var $configCache = array();

    /**
     * constructor which accepts a filename as parameter
     *
     * the filename must either contain a absolute path or describe the relative path inside the billpay base folder
     *
     * @param null|string $configFile
     */
    function __construct($configFile = null)
    {
        if ($configFile === null) {
            $configFile = $this->DEFAULT_CONFIG_FILE;
        }

        // if we have a absolute path we simply include it
        // otherwise we use the billpay folder as base
        if (strpos($configFile, '/') !== 0) {
            $configFile = dirname(__FILE__) . '/../' . $configFile;
        }

        if (is_file($configFile) === true) {
            /** @noinspection PhpIncludeInspection */
            $this->config = include($configFile);
        }
    }

    /**
     * returns the configuration value for the given key or the whole configuration array if ne $key parameter is null.
     * when no value is found for the specified key the $default parameter is returned
     *
     * @param null|string $key
     * @param mixed $default
     *
     * @return mixed
     */
    function get($key = null, $default = null)
    {
        if ($key === null) {
            return $this->config;
        }

        if (($result = $this->getFromCache($key)) !== null) {
            return $result;
        }

        $result = $this->findValueForKey($key);

        if ($result !== null) {
            $this->setToCache($key, $result);

            return $result;
        }
        return $default;
    }

    /**
     * splits the given $key parameter by using the $KEY_SEPARATOR
     *
     * @param string|array $key
     *
     * @return array
     */
    function parseKey($key)
    {
        if (is_array($key) === false) {
            $key = explode($this->KEY_SEPARATOR, $key);
        }

        return $key;
    }

    /**
     * combines all parts of the $key parameter by using the $KEY_SEPARATOR
     *
     * @param string|array $key
     *
     * @return string
     */
    function buildKey($key)
    {
        if (is_array($key) === true) {
            $key = implode($this->KEY_SEPARATOR, $key);
        }
        return $key;
    }

    /**
     * run through the configuration array and searches for the value of the given $key parameter
     *
     * @param string|array $key
     * @param bool         $strict uses a type strict compare of the array keys which should only be useful for
     *                             special cases
     *
     * @return mixed
     * @throws Exception if the $key contains more segments than the config array or no entry could be found for the $key
     */
    function findValueForKey($key, $strict = false)
    {
        $key = $this->parseKey($key);
        $subtree = $this->config;

        foreach ($key as $keySegment) {
            // seams like the key has a greater depth than the configuration array
            if (is_array($subtree) === false) {
                return null;
            }
            foreach ($subtree as $configKey => $configValue) {

                // check if we got a matching key
                if (($strict === true && $configKey === $keySegment)
                    || $configKey == $keySegment
                ) {
                    $subtree = $configValue;

                    // search for the next key segment
                    continue 2;
                }
            }
            // ERROR
            return null;
        }

        return $subtree;
    }

    /**
     * searches for a cache entry and returns it.
     * if its not found null is returned
     *
     * @param string|array $key
     *
     * @return mixed
     */
    function getFromCache($key)
    {
        $key = $this->buildKey($key);
        if (isset($this->configCache[$key]) === true) {
            return $this->configCache[$key];
        }
        return null;
    }

    /**
     * writes a cache entry
     *
     * @param string|array $key
     * @param mixed        $value
     *
     * @return Billpay_Base_Config
     */
    function setToCache($key, $value)
    {
        $key = $this->buildKey($key);
        $this->configCache[$key] = $value;

        return $this;
    }

    /**
     * resets a cache entry or the whole cache when the $key parameter is nul
     *
     * @param null|string|array $key
     *
     * @return Billpay_Base_Config
     */
    function flushCache($key = null)
    {
        $key = $this->buildKey($key);
        if ($key === null) {
            $this->configCache = array();

        } elseif (isset($this->configCache[$key])) {
            unset($this->configCache[$key]);
        }

        return $this;
    }
}