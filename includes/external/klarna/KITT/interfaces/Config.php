<?php
/**
 * Configuration management interface
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
 * Raised when trying to access a configuration option that is not set
 *
 * @category  Payment
 * @package   KiTT
 * @author    MS Dev <ms.modules@klarna.com>
 * @copyright 2012 Klarna AB (http://klarna.com)
 * @license   http://opensource.org/licenses/BSD-2-Clause BSD-2
 * @link      http://integration.klarna.com/
 */
class KiTT_MissingConfigurationException extends Exception
{
    /**
     * The missing configuration key
     *
     * @var string
     */
    public $key;

    /**
     * Construct KiTT_MissingConfigurationException
     *
     * @param string $key the missing configuration key
     */
    public function __construct($key)
    {
        parent::__construct("Missing configuration key: {$key}");
        $this->key = $key;
    }
}

/**
 * Holds configuration options
 *
 * @category  Payment
 * @package   KiTT
 * @author    MS Dev <ms.modules@klarna.com>
 * @copyright 2012 Klarna AB (http://klarna.com)
 * @license   http://opensource.org/licenses/BSD-2-Clause BSD-2
 * @link      http://integration.klarna.com/
 */
interface KiTT_Config
{
    /**
     * Get a configuration value
     *
     * @param string $name the name of the configuration value to get
     *
     * @return mixed
     */
    public function get($name);
}
