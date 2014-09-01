<?php
/**
 * Utilities for accessing GET and POST variables
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
 * HTTP Context translater. Making sure data is set to the correct type.
 *
 * @category  Payment
 * @package   KiTT
 * @author    MS Dev <ms.modules@klarna.com>
 * @copyright 2012 Klarna AB (http://klarna.com)
 * @license   http://opensource.org/licenses/BSD-2-Clause BSD-2
 * @link      http://integration.klarna.com/
 */
class KiTT_HTTPContext
{
    /**
     * Get value from user input as string
     *
     * @param string $name               key for value
     * @param mixed  $defaultReturnValue default value if value is missing
     *
     * @return string value or default
     */
    public static function toString($name, $defaultReturnValue = null)
    {
        $args = array_merge($_GET, $_POST, $_REQUEST);

        if (array_key_exists($name, $args)) {
            return (string) $args[$name];
        }
        return (string) $defaultReturnValue;
    }

    /**
     * Get value from user input as boolean
     *
     * @param string $name               key for value
     * @param mixed  $defaultReturnValue default value if value is missing
     *
     * @return bool value or default
     */
    public static function toBoolean($name, $defaultReturnValue = null)
    {
        $args = array_merge($_GET, $_POST, $_REQUEST);

        $val = $defaultReturnValue;
        if (array_key_exists($name, $args)) {
            $val = $args[$name];
        }

        // String comparison of 0 == "true" always returns true.
        if ($val === 0) {
            return false;
        }

        if (($val == 'true')
            || ($val == '1')
            || ($val === true)
            || ($val === 1)
        ) {
            return true;
        }

        if (($val == 'false')
            || ($val == '0')
            || ($val === false)
        ) {
            return false;
        }

        return (bool) $val;
    }

    /**
     * Get value from user input as integer
     *
     * @param string $name               key for value
     * @param mixed  $defaultReturnValue default value if value is missing
     *
     * @return integer value or default
     */
    public static function toInteger($name, $defaultReturnValue = null)
    {
        $args = array_merge($_GET, $_POST, $_REQUEST);

        if (array_key_exists($name, $args)) {
            return (integer) $args[$name];
        }
        return (integer) $defaultReturnValue;
    }

    /**
     * Get value from user input as float
     *
     * @param string $name               key for value
     * @param mixed  $defaultReturnValue default value if value is missing
     *
     * @return float value or default
     */
    public static function toFloat($name, $defaultReturnValue = null)
    {
        $args = array_merge($_GET, $_POST, $_REQUEST);

        if (array_key_exists($name, $args)) {
            return floatval($args[$name]);
        }
        return floatval($defaultReturnValue);
    }

    /**
     * Get value from user input as float
     *
     * @param string $name               key for value
     * @param mixed  $defaultReturnValue default value if value is missing
     *
     * @return array value or default
     */
    public static function toArray($name, $defaultReturnValue = null)
    {
        $args = array_merge($_GET, $_POST, $_REQUEST);

        if (array_key_exists($name, $args)) {
            return (array) $args[$name];
        }
        return (array) $defaultReturnValue;
    }

    /**
     * Get value from files posted
     *
     * @param string $name               key for value
     * @param mixed  $defaultReturnValue default value if value is missing
     *
     * @return string value or default
     */
    public static function toFile($name, $defaultReturnValue = null)
    {
        if (array_key_exists($name, $_FILES)) {
            return $_FILES[$name];
        }
        return $defaultReturnValue;
    }
}
