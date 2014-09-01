<?php
/**
 * Default implementation of the KlarnaFormatter interface
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
 * KiTT_DefaultFormatter
 *
 * Default implementation with hardcode and hopefully sane defaults
 *
 * @category  Payment
 * @package   KiTT
 * @author    MS Dev <ms.modules@klarna.com>
 * @copyright 2012 Klarna AB (http://klarna.com)
 * @license   http://opensource.org/licenses/BSD-2-Clause BSD-2
 * @link      http://integration.klarna.com/
 */
class KiTT_DefaultFormatter implements KiTT_Formatter
{

    /**
     * Format a price
     *
     * @param mixed       $price  Raw price
     * @param KiTT_Locale $locale The locale to format the prices for
     *
     * @return string price with currency sign added
     */
    public function formatPrice($price, KiTT_Locale $locale = null)
    {
        if ($locale === null) {
            return $price;
        }

        $before = "";
        $after = "";

        switch ($locale->getCountry() ) {
        case KlarnaCountry::SE:
        case KlarnaCountry::NO:
        case KlarnaCountry::DK:
            $after = " kr";
            break;
        case KlarnaCountry::FI;
            $after = '€';
            break;
        case KlarnaCountry::DE;
        case KlarnaCountry::NL;
            $before = "€";
            break;
        }

        return $before . $price . $after;
    }
}
