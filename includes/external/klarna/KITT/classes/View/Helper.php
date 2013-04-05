<?php
/**
 * KiTT View Helper
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
 * Helper class for KiTT_View
 *
 * @category  Payment
 * @package   KiTT
 * @author    MS Dev <ms.modules@klarna.com>
 * @copyright 2012 Klarna AB (http://klarna.com)
 * @license   http://opensource.org/licenses/BSD-2-Clause BSD-2
 * @link      http://integration.klarna.com/
 */
class KiTT_View_Helper
{

    /**
     * @var KiTT_Config
     */
    public $config;

    /**
     * @var KiTT_Locale
     */
    public $locale;

    /**
     * @var KiTT_Translator
     */
    public $lang;

    /**
     * @var KiTT_TemplateLoader
     */
    protected $templateLoader;

    /**
     * @var string
     */
    public $country;

    /**
     * @var string
     */
    public $type;

    /**
     * Construct helper
     *
     * @param KiTT_Config         $config         site configuration
     * @param KiTT_Locale         $locale         locale
     * @param KiTT_TemplateLoader $templateLoader template loader
     * @param KiTT_Translator     $translator     translations
     */
    public function __construct ($config, $locale, $templateLoader, $translator)
    {
        $this->config = $config;
        $this->locale = $locale;
        $this->lang = $translator;
        $this->templateLoader = $templateLoader;
    }

    /**
     * Render the consent template.
     *
     * @return string
     */
    public function consent()
    {
        return $this->templateLoader->load('consent.mustache')->render($this);
    }
}
