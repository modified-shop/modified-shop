<?php
/**
 * xtCommerce 3.0.4 implementation of database interface.
 *
 * PHP Version 5.2
 *
 * @category Payment
 * @package  Klarna_Module_XtCommerce
 * @author   MS Dev <ms.modules@klarna.com>
 * @license  http://opensource.org/licenses/BSD-2-Clause BSD2
 * @link     http://integration.klarna.com
 */

require_once 'interface.KlarnaDB.php';
require_once 'class.xtcDBResult.php';

/**
 * xtCommerce 3.0.4 implementation of database interface.
 *
 * @category Payment
 * @package  Klarna_Module_XtCommerce
 * @author   MS Dev <ms.modules@klarna.com>
 * @license  http://opensource.org/licenses/BSD-2-Clause BSD2
 * @link     http://integration.klarna.com
 */
class XtcKlarnaDB implements KlarnaDB
{
    /**
     * Perform a query
     *
     * @param string $string sql string
     *
     * @return KlarnaDBResult
     */
    public function query($string)
    {
        return new XtcDBResult(xtc_db_query($string));
    }

    /**
     * Wrap xtc_perform and similar
     *
     * @param string $table database table
     * @param array  $data  associative array to insert into the table
     *
     * @return mysql_result
     */
    public function perform($table, $data)
    {
        return xtc_db_perform($table, $data);
    }
}
