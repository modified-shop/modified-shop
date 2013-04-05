<?php
/**
 * xtCommerce 3.0.4 implementation of database results interface.
 *
 * PHP Version 5.2
 *
 * @category Payment
 * @package  Klarna_Module_XtCommerce
 * @author   MS Dev <ms.modules@klarna.com>
 * @license  http://opensource.org/licenses/BSD-2-Clause BSD2
 * @link     http://integration.klarna.com
 */

require_once 'interface.KlarnaDBResult.php';

/**
 * xtCommerce 3.0.4 implementation of database results interface.
 *
 * @category Payment
 * @package  Klarna_Module_XtCommerce
 * @author   MS Dev <ms.modules@klarna.com>
 * @license  http://opensource.org/licenses/BSD-2-Clause BSD2
 * @link     http://integration.klarna.com
 */
class XtcDBResult implements KlarnaDBResult
{

    /**
     * @var mysql_result
     */
    private $_result;

    /**
     * Create a reslut object
     *
     * @param mysql_result $mysql_result result of a database query
     */
    public function __construct($mysql_result)
    {
        $this->_result = $mysql_result;
    }

    /**
     * Count number of rows
     *
     * @return int
     */
    public function count()
    {
        return xtc_db_num_rows($this->_result);
    }

    /**
     * Pop an associative array representing the first row in the result.
     *
     * @return array
     */
    public function getArray()
    {
        return xtc_db_fetch_array($this->_result);
    }
}
