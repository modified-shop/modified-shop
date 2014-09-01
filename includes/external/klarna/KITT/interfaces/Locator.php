<?php
/**
 * The Locator interface
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
 * KiTT_Locator
 *
 * Interface to locate customers based on certain parameters.
 *
 * @category  Payment
 * @package   KiTT
 * @author    MS Dev <ms.modules@klarna.com>
 * @copyright 2012 Klarna AB (http://klarna.com)
 * @license   http://opensource.org/licenses/BSD-2-Clause BSD-2
 * @link      http://integration.klarna.com/
 */
interface KiTT_Locator
{
    /**
     * Determines country of customer from input
     *
     * @param string     $currency  iso-4217 currency code
     * @param string     $language  iso-639-1 language code
     * @param KlarnaAddr $address   Customers KlarnaAddr object
     * @param string     $ipAddress Customer IPAddress for eventual geoIP lookup
     *
     * @return string country code
     */
    public function locate(
        $currency = null, $language = null, $address = null, $ipAddress = null
    );
}
