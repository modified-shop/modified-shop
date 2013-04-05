<?php
/**
 * Part payment widget view
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
 * KiTT_ProductPrice
 *
 * @category  Payment
 * @package   KiTT
 * @author    MS Dev <ms.modules@klarna.com>
 * @copyright 2012 Klarna AB (http://klarna.com)
 * @license   http://opensource.org/licenses/BSD-2-Clause BSD-2
 * @link      http://integration.klarna.com/
 */
class KiTT_Installment_Widget extends KiTT_Widget
{
    /**
     * @var KiTT_PClassCollection
     */
    public $pclasses;

    /**
     * Country code
     * @var string
     */
    public $country;

    /**
     * Asterisk character or empty stirng
     * @var string
     */
    public $asterisk;

    protected $template = 'ppbox.mustache';

    /**
     * Create product price widget
     *
     * @param KiTT_Config           $config         KiTT Config object
     * @param KiTT_Locale           $locale         KiTT Locale object
     * @param KiTT_PClassCollection $pclasses       Collection of pclasses
     * @param KiTT_TemplateLoader   $templateLoader KiTT TemplateLoader object
     * @param KiTT_Translator       $translator     KiTT Translator object
     */
    public function __construct ($config, $locale, $pclasses, $templateLoader,
        $translator
    ) {
        parent::__construct($config, $locale, $templateLoader, $translator);
        $this->pclasses = $pclasses;
    }

    /**
     * Get the eid for the locale country
     *
     * @return int
     */
    public function eid()
    {
        $country = strtoupper($this->locale->getCountryCode());
        try {
            return $this->config->get("sales_countries/{$country}/eid");
        } catch (KiTT_MissingConfigurationException $e) {
            return 0;
        }
    }

    /**
     * Display the part payment box
     *
     * @return string rendered html
     */
    public function show()
    {
        if (count($this->pclasses->pclasses) == 0) {
            return '';
        }

        if (array_key_exists('HTTP_USER_AGENT', $_SERVER)
            && (strstr($_SERVER['HTTP_USER_AGENT'], "MSIE 7.0")
            || strstr($_SERVER['HTTP_USER_AGENT'], "MSIE 6.0"))
        ) {
            return '';
        }

        $this->country = strtolower($this->locale->getCountryCode());

        $this->asterisk = '';
        if (KiTT_CountryLogic::needAsterisk($this->country)) {
            $this->asterisk =  '*';
        }


        return parent::show();
    }
}

