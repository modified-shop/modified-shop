<?php
/**
 * View base class
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
 * KiTT_View
 *
 * @category  Payment
 * @package   KiTT
 * @author    MS Dev <ms.modules@klarna.com>
 * @copyright 2012 Klarna AB (http://klarna.com)
 * @license   http://opensource.org/licenses/BSD-2-Clause BSD-2
 * @link      http://integration.klarna.com/
 */
abstract class KiTT_Widget
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
     * Name of template used to render this view
     * @var string
     */
    protected $template;

    /**
     * @var KiTT_TemplateLoader
     */
    protected $templateLoader;

    /**
     * Construct view
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
     * Display the view
     *
     * @return string the template rendered with the view as data
     */
    public function show()
    {
        $template = $this->templateLoader->load($this->template);
        return $template->render($this);
    }

    /**
     * Retrieve a View_Helper
     *
     * @return KiTT_View_Helper
     */
    protected function getHelper()
    {
        return new KiTT_View_Helper(
            $this->config, $this->locale, $this->templateLoader, $this->lang
        );
    }
}
