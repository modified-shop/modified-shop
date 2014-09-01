<?php
/**
 * Interface for database results.
 *
 * PHP Version 5.2
 *
 * @category Payment
 * @package  Klarna_Module_XtCommerce
 * @author   MS Dev <ms.modules@klarna.com>
 * @license  http://opensource.org/licenses/BSD-2-Clause BSD2
 * @link     http://integration.klarna.com
 */

/**
 * Interface for database results.
 *
 * @category Payment
 * @package  Klarna_Module_XtCommerce
 * @author   MS Dev <ms.modules@klarna.com>
 * @license  http://opensource.org/licenses/BSD-2-Clause BSD2
 * @link     http://integration.klarna.com
 */
interface KlarnaDBResult
{
    /**
     * Count number of rows
     *
     * @return int
     */
    public function count();

    /**
     * Pop an associative array representing the first row in the result.
     *
     * @return array
     */
    public function getArray();
}
