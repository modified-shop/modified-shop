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
 * KiTT_DefaultBaptizer
 *
 * Baptizes with a prefix
 *
 * @category  Payment
 * @package   KiTT
 * @author    MS Dev <ms.modules@klarna.com>
 * @copyright 2012 Klarna AB (http://klarna.com)
 * @license   http://opensource.org/licenses/BSD-2-Clause BSD-2
 * @link      http://integration.klarna.com/
 */
class KiTT_DefaultBaptizer implements KiTT_Baptizer
{
    /**
     * @var string
     */
    protected $prefix;

    /**
     * Construct a Baptizer
     *
     * @param string $prefix value to prepend names with e.g a payment code
     */
    public function __construct($prefix)
    {
        $this->prefix = $prefix;
    }

    /**
     * Baptize a field name
     *
     * @param string $baseName original name of field
     *
     * @return string input baptized with prefix
     */
    public function nameField($baseName)
    {
        return "{$this->prefix}_{$baseName}";
    }

    /**
     * Baptize an ID
     *
     * @param string $baseName original id
     *
     * @return string input baptized with prefix
     */
    public function nameId($baseName)
    {
        return "{$this->prefix}_{$baseName}";
    }
}
