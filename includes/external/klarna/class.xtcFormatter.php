<?php
/**
 * Currency formatter.
 *
 * PHP Version 5.2
 *
 * @category Payment
 * @package  Klarna_Module_XtCommerce
 * @author   MS Dev <ms.modules@klarna.com>
 * @license  http://opensource.org/licenses/BSD-2-Clause BSD2
 * @link     http://integration.klarna.com
 */
require_once 'class.KlarnaCore.php';
require_once DIR_KLARNA . 'KITT/interfaces/Formatter.php';

/**
 * Format prices to be consistent across the platform.
 *
 * @category Payment
 * @package  Klarna_Module_XtCommerce
 * @author   MS Dev <ms.modules@klarna.com>
 * @license  http://opensource.org/licenses/BSD-2-Clause BSD2
 * @link     http://integration.klarna.com
 */
class XtcFormatter implements KiTT_Formatter
{
    /**
    * Format the price
    *
    * @param float       $price  the price to format
    * @param KiTT_Locale $locale unused in this implementation
    *
    * @return float formatted price
    */
    public function formatPrice($price, KiTT_Locale $locale = null)
    {
        global $xtPrice;

        $val = $xtPrice->xtcFormat((float) $price, true);

        return KiTT_String::encode(decode_htmlentities($val), null, 'UTF-8');
    }
}
