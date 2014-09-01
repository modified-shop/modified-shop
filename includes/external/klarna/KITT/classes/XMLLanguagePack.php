<?php
/**
 * Klarna XML Language Pack
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
 * The Klarna Language Pack class. This class fetches translations from a
 * language pack.
 *
 * @category  Payment
 * @package   KiTT
 * @author    MS Dev <ms.modules@klarna.com>
 * @copyright 2012 Klarna AB (http://klarna.com)
 * @license   http://opensource.org/licenses/BSD-2-Clause BSD-2
 * @link      http://integration.klarna.com/
 */
class KiTT_XMLLanguagePack implements KiTT_LanguagePack
{
    /**
     * XML tree with translations
     * @var SimpleXMLElement
     */
    private $_xml;

    /**
     * @var KiTT_Config
     */
    private $_config;

    /**
     * Create language pack
     *
     * @param KiTT_Config $config configuration
     * @param KiTT_VFS    $vfs    vfs object
     */
    public function __construct(KiTT_Config $config, KiTT_VFS $vfs)
    {
        $this->_config = $config;
        $path = $this->_config->get('paths/lang');
        $this->_xml = simplexml_load_string(
            utf8_encode($vfs->file_get_contents($path))
        );
    }

    /**
     * Get a translated text from the language pack
     *
     * @param string     $text     the string to be translated
     * @param string|int $language target language, iso code or KlarnaLanguage
     *
     * @return string  the translated text
     */
    public function fetch ($text, $language)
    {
        if (is_numeric($language)) {
            $language = KlarnaLanguage::getCode($language);
        } else {
            $language = strtolower($language);
        }

        // XPath query to get translation
        $xpath = "//string[@id='$text']/$language";
        $aResult = (array) @$this->_xml->xpath($xpath);
        if (count($aResult) > 0) {
            return strval($aResult[0]);
        }

        // Fallback to the english text
        if ($language != 'en') {
            return $this->fetch($text, 'en');
        }

        // Or failing that, the placeholder
        return $text;
    }

}
