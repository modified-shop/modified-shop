<?php
/**
 * Interface for pluggable formatter implementations
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
 * KiTT_Formatter
 *
 * @category  Payment
 * @package   KiTT
 * @author    MS Dev <ms.modules@klarna.com>
 * @copyright 2012 Klarna AB (http://klarna.com)
 * @license   http://opensource.org/licenses/BSD-2-Clause BSD-2
 * @link      http://integration.klarna.com/
 */
interface KiTT_Formatter
{
    /**
     * Format the price with proper currency symbols etc
     *
     * @param mixed       $price  Raw price
     * @param KiTT_Locale $locale The locale to format the price for
     *
     * @return string formatted price
     */
    public function formatPrice($price, KiTT_Locale $locale = null);
}
