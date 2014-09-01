<?php
/**
 * The Baptizer ensures we have unique field names and IDs
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
 * KiTT_Baptizer
 *
 * @category  Payment
 * @package   KiTT
 * @author    MS Dev <ms.modules@klarna.com>
 * @copyright 2012 Klarna AB (http://klarna.com)
 * @license   http://opensource.org/licenses/BSD-2-Clause BSD-2
 * @link      http://integration.klarna.com/
 */
interface KiTT_Baptizer
{
    /**
     * Baptize a field name
     *
     * @param string $baseName original name of field
     *
     * @return string input baptized with prefix
     */
    public function nameField($baseName);

    /**
     * Baptize an ID
     *
     * @param string $baseName original id
     *
     * @return string input baptized with prefix
     */
    public function nameId($baseName);
}
