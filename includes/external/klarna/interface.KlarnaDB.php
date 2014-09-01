<?php
/**
 * Interface for database functions.
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
 * Interface for database functions.
 *
 * @category Payment
 * @package  Klarna_Module_XtCommerce
 * @author   MS Dev <ms.modules@klarna.com>
 * @license  http://opensource.org/licenses/BSD-2-Clause BSD2
 * @link     http://integration.klarna.com
 */
interface KlarnaDB
{
    /**
     * Perform a query
     *
     * @param string $string sql string
     *
     * @return KlarnaDBResult
     */
    public function query($string);

    /**
     * Wrap xtc_perform and similar
     *
     * @param string $table database table
     * @param array  $data  associative array to insert into the table
     *
     * @return mysql_result
     */
    public function perform($table, $data);
}
