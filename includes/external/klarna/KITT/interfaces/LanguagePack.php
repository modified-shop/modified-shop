<?php
/**
 * Klarna Language Pack interface
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
 * The Klarna Language Pack interface
 *
 * @category  Payment
 * @package   KiTT
 * @author    MS Dev <ms.modules@klarna.com>
 * @copyright 2012 Klarna AB (http://klarna.com)
 * @license   http://opensource.org/licenses/BSD-2-Clause BSD-2
 * @link      http://integration.klarna.com/
 */
interface KiTT_LanguagePack
{

    /**
     * Get a translated text from the language pack
     *
     * @param string     $text     the string to be translated
     * @param string|int $language target language, iso code or KlarnaLanguage
     *
     * @return string  the translated text
     */
    public function fetch($text, $language);
}
