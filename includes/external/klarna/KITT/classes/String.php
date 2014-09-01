<?php
/**
 * String utilities
 *
 * PHP Version 5.3
 *
 * @category  Payment
 * @package   Klarna_KiTT
 * @author    MS Dev <ms.modules@klarna.com>
 * @copyright 2012 Klarna AB (http://klarna.com)
 * @license   http://opensource.org/licenses/BSD-2-Clause BSD-2
 * @link      http://integration.klarna.com/
 */

/**
 * KiTT_String
 *
 * Does encoding between platform string encoding and Klarna's encoding
 *
 * @category  Payment
 * @package   Klarna_KiTT
 * @author    MS Dev <ms.modules@klarna.com>
 * @copyright 2012 Klarna AB (http://klarna.com)
 * @license   http://opensource.org/licenses/BSD-2-Clause BSD-2
 * @link      http://integration.klarna.com/
 */
class KiTT_String
{

    /**
     * The encoding used by the platform
     *
     * @var string
     */
    public static $platformEncoding = 'UTF-8';

    /**
     * The encoding expected by Klarna
     *
     * @var string
     */
    public static $klarnaEncoding = 'ISO-8859-1';

    /**
     * Encode the string to the klarnaEncoding
     *
     * @param string $str  string to encode
     * @param string $from from encoding
     * @param string $to   target encoding
     *
     * @return string
     */
    public static function encode($str, $from = null, $to = null)
    {
        if ($from === null) {
            $from = self::$platformEncoding;
        }
        if ($to === null) {
            $to = self::$klarnaEncoding;
        }
        return iconv($from, $to, $str);
    }

    /**
     * Decode the string to the platformEncoding
     *
     * @param string $str  string to decode
     * @param string $from from encoding
     * @param string $to   target encoding
     *
     * @return string
     */
    public static function decode($str, $from = null, $to = null)
    {
        if ($from === null) {
            $from = self::$klarnaEncoding;
        }
        if ($to === null) {
            $to = self::$platformEncoding;
        }
        return iconv($from, $to, $str);
    }

    /**
     * inject uri into string. Any occurance of a substring surrounded by
     * _ (underscore) will be converted to a link to $uri.
     *
     * For example:
     *      KiTT_String::injectLink(
     *          "Click to _comment_ this.", 'index.php/addComment'
     *      );
     * will return:
     *      "Click to <a href='index.php/addComment'>comment</a> this."
     *
     * @param string $string the string to parse
     * @param string $uri    link to inject
     *
     * @return a string containing HTML.
     */
    public static function injectLink($string, $uri)
    {
        return preg_replace(
            "/\_([^_]+)\_/",
            "<a href='{$uri}'>\\1</a>",
            $string
        );
    }
}
