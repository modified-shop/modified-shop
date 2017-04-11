<?php
/**
 * Payment widget
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
 * KiTT_Payment_Widget
 *
 * @category  Payment
 * @package   KiTT
 * @author    MS Dev <ms.modules@klarna.com>
 * @copyright 2012 Klarna AB (http://klarna.com)
 * @license   http://opensource.org/licenses/BSD-2-Clause BSD-2
 * @link      http://integration.klarna.com/
 */
class KiTT_Payment_Widget extends KiTT_Widget
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
     * Language code
     * @var string
     */
    public $language;

    /**
     * @var KiTT_Lookup
     */
    protected $lookup;

    /**
     * @var KiTT_Baptizer
     */
    protected $baptizer;

    /**
     * @var KiTT_TemplateFields
     */
    protected $elements;

    /**
     * Construct KiTT_Payment_Widget
     *
     * @param KiTT_Config           $config         site configuration
     * @param KiTT_Locale           $locale         locale
     * @param string                $type           payment code
     * @param KiTT_TemplateLoader   $templateLoader KiTT TemplateLoader object
     * @param KiTT_Translator       $translator     translations
     * @param KiTT_Baptizer         $baptizer       Baptizer implementation
     * @param KiTT_Lookup           $lookup         The lookup table
     * @param KiTT_TemplateFields   $elements       The template fields
     * @param KiTT_PClassCollection $pclasses       pclass collection
     */
    public function __construct(
        KiTT_Config $config,
        KiTT_Locale $locale,
        $type,
        KiTT_TemplateLoader $templateLoader,
        KiTT_Translator $translator,
        KiTT_Baptizer $baptizer,
        KiTT_Lookup $lookup,
        KiTT_TemplateFields $elements,
        KiTT_PClassCollection $pclasses = null,
        $settings = array()
    ) {
        parent::__construct($config, $locale, $templateLoader, $translator);
        $this->type = $type;
        $this->pclasses = $pclasses;
        $this->baptizer = $baptizer;
        $this->template = "{$type}.html";
        $this->lookup = $lookup;
        $this->elements = $elements;

        foreach($settings as $key => $value) {
            $this->$key = $value;
        }

        $this->country = strtolower($this->locale->getCountryCode());
        $this->language = strtolower($this->locale->getLanguageCode());
    }

    /**
     * Display the checkout
     *
     * @return string rendered html
     */
    public function show()
    {
        return parent::show();
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
     * The baptized fieldname for the pclass input
     *
     * @return fieldname
     */
    public function pclassInputName()
    {
        return $this->baptizer->nameField('paymentPlan');
    }

    /**
     * The baptized fieldname for the address key
     *
     * @return fieldname
     */
    public function addressKey()
    {
        return $this->baptizer->nameField('address_key');
    }

    /**
     * Get the default language for the locale country
     *
     * @return string
     */
    public function defaultLanguage()
    {
        return $this->lookup->defaultLanguage($this->country);
    }

    /**
     * Get the available languages for the locale and make sure that English
     * and the current language is included.
     *
     * @return string
     */
    public function languages()
    {
        $languages = $this->lookup->getAllLanguages($this->country);
        $avail = array_merge(
            array('en', $this->language),
            ($languages !== null) ? $languages : array()
        );
        return json_encode(array_values(array_unique($avail)));
    }

    /**
     * Gather fields data for mustache.
     *
     * @return array
     */
    public function fields()
    {
        return $this->_resolveCustomEntries($this->elements->get());
    }

    /**
     * Resolve partials stored in the json, Needs a callable or field on the
     * View Helper used that matches the value of the custom key.
     *
     * @param array $fields an array of field values
     *
     * @return array
     */
    private function _resolveCustomEntries($fields)
    {
        $helper = $this->getHelper();
        foreach ($fields as &$value) {
            if (array_key_exists('custom', $value)) {
                if (method_exists($helper, $value['custom'])) {
                    $value['custom'] = $helper->{$value['custom']}();
                }
            }
        }
        return $fields;
    }

    /**
     * Override the getHelper() function to assign values we need for the
     * template.
     *
     * @return KiTT_View_Helper object
     */
    protected function getHelper()
    {
        $helper = parent::getHelper();
        $helper->type = $this->type;
        $helper->country = strtolower($this->locale->getCountryCode());
        return $helper;
    }
}
