<?php
/**
 * Exceptions for use with the Klarna Integration ToolkiT
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
 * base KiTT_Exception class
 *
 * @category  Payment
 * @package   KiTT
 * @author    MS Dev <ms.modules@klarna.com>
 * @copyright 2012 Klarna AB (http://klarna.com)
 * @license   http://opensource.org/licenses/BSD-2-Clause BSD-2
 * @link      http://integration.klarna.com/
 */
class KiTT_Exception extends Exception
{
    /**
     * Construct Exception
     *
     * @param string    $message  pretty message
     * @param int       $code     error code
     * @param Exception $previous the previous exception
     */
    public function __construct($message, $code = 0, $previous = null)
    {
        if (version_compare(PHP_VERSION, '5.3.0') >= 0) {
            parent::__construct($message, $code, $previous);
        } else {
            parent::__construct($message, $code);
        }
    }

    /**
     * Format error message
     *
     * @return string
     */
    public function __toString()
    {
        return "{$this->code}: {$this->message}";
    }
}
