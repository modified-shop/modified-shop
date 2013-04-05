<?php
/**
 * Configuration management simple implementation
 *
 * PHP Version 5.3
 *
 * @category  Payment
 * @package   KiTT
 * @author    MS Dev <ms.modules@klarna.com>
 * @copyright 2012 Klarna AB (http://klarna.com)
 * @license   http://opensource.org/licenses/BSD-2-Clause BSD-2
 * @link      http://integration.klarna.com/
 */

/**
 * A basic configuration implementation that does not do any persistance
 *
 * @category  Payment
 * @package   KiTT
 * @author    MS Dev <ms.modules@klarna.com>
 * @copyright 2012 Klarna AB (http://klarna.com)
 * @license   http://opensource.org/licenses/BSD-2-Clause BSD-2
 * @link      http://integration.klarna.com/
 */
class KiTT_SimpleConfig implements KiTT_Config
{
    private $_data = array();

    private function _resolve($path, $value = null)
    {
        $context = &$this->_data;
        $parts = explode('/', $path);
        $last = array_pop($parts);

        // Traverse the configuration for any intermediate steps
        foreach ($parts as $name) {
            // give up if it's not possible to index into the context
            if (!is_array($context)) {
                throw new KiTT_Exception(
                    "Can not access {$name} on {$context}"
                );
            }

            // Make sure the next step exists throws when reading
            // creates the step as a empty array when writing
            if (!array_key_exists($name, $context)) {
                if ($value === null) {
                    throw new KiTT_MissingConfigurationException($path);
                }
                $context[$name] = array();
            }

            $context = &$context[$name];
        }

        // Access the config value
        if ($value !== null) {
            $context[$last] = $value;
        } else if (!array_key_exists($last, $context)) {
            throw new KiTT_MissingConfigurationException($path);
        }
        return $context[$last];
    }

    /**
     * Get a configuration value
     *
     * @param string $name the name of the configuration value to get
     *
     * @return mixed
     */
    public function get($name)
    {
        return $this->_resolve($name, null);
    }

    /**
     * Set a configuration option
     * Internal use only all configuration should happen through the KiTT facade
     *
     * @param string $name  name of option to set
     * @param string $value value to set option to
     *
     * @return void
     */
    public function set($name, $value)
    {
        return $this->_resolve($name, $value);
    }

    /**
     * Check if a configuration option exists
     *
     * @param string $name name of option to set
     *
     * @return boolean
     */
    public function has($name)
    {
        try {
            $this->_resolve($name, null);
        } catch (KiTT_MissingConfigurationException $e) {
            return false;
        }
        return true;
    }

    /**
     * Magic getter used by Mustache
     * Wraps the get function.
     *
     * @param string $key the name of the configuration value to get
     *
     * @return mixed
     */
    public function __get($key)
    {
        return $this->get($key);
    }

    /**
     * Magic isset used by Mustache
     * Wraps array_key_exists
     *
     * @param string $key config value to check for
     *
     * @return bool
     */
    public function __isset($key)
    {
        return $this->has($key);
    }
}
