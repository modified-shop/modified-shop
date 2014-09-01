<?php
/**
 * Error Message Handler
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
 * KiTT_ErrorMessage
 *
 * @category  Payment
 * @package   KiTT
 * @author    MS Dev <ms.modules@klarna.com>
 * @copyright 2012 Klarna AB (http://klarna.com)
 * @license   http://opensource.org/licenses/BSD-2-Clause BSD-2
 * @link      http://integration.klarna.com/
 */
class KiTT_ErrorMessage
{
    private static $_errorCache = array();

    /**
     * add an error message
     *
     * @param string $string error message to add
     *
     * @return void
     */
    public function add($string)
    {
        $_SESSION['kitt']['errors'][] = $string;
    }

    /**
     * Remove the errors from session and cache them.
     * Return a string of the array imploded with <br> tags.
     *
     * @return string
     */
    public function render()
    {
        if (isset($_SESSION['kitt']['errors'])) {
            self::$_errorCache = $_SESSION['kitt']['errors'];
            unset($_SESSION['kitt']['errors']);
        }
        return addslashes(implode("<br />", self::$_errorCache));
    }

    /**
     * Clear stored error messages
     *
     * @return void
     */
    public function clear()
    {
        self::$_errorCache = array();
    }

    /**
     * Return a string representation of the stored errors.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->render();
    }
}
