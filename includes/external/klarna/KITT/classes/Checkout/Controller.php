<?php
/**
 * Checkout Controller
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
 * KiTT_Checkout_Controller
 *
 * @category  Payment
 * @package   KiTT
 * @author    MS Dev <ms.modules@klarna.com>
 * @copyright 2012 Klarna AB (http://klarna.com)
 * @license   http://opensource.org/licenses/BSD-2-Clause BSD-2
 * @link      http://integration.klarna.com/
 */
class KiTT_Checkout_Controller
{

    /**
     * @var array
     */
    protected $options;

    protected $config;

    protected $locale;

    protected $formatter;

    protected $pclasses;

    protected $translator;

    protected $templateLoader;

    protected $lookup;

    protected $api;

    protected $sum;

    /**
     * Constructor KiTT_Checkout_Controller
     *
     * @param KiTT_Config         $config         site configuration
     * @param KiTT_Locale         $locale         locale
     * @param numeric             $sum            total price
     * @param KiTT_Formatter      $formatter      formatter
     * @param KiTT_Translator     $translator     translator
     * @param KiTT_TemplateLoader $templateLoader template loader
     * @param KiTT_Lookup         $lookup         lookup table
     * @param Klarna              $api            klarna api instance
     */
    public function __construct(
        KiTT_Config $config, KiTT_Locale $locale, $sum,
        KiTT_Formatter $formatter,
        KiTT_Translator $translator,
        KiTT_TemplateLoader $templateLoader,
        KiTT_Lookup $lookup,
        Klarna $api
    ) {
        $this->options = array();
        $this->config = $config;
        $this->locale = $locale;
        $this->sum = $sum;
        $this->formatter = $formatter;
        $this->translator = $translator;
        $this->templateLoader = $templateLoader;
        $this->lookup = $lookup;
        $this->api = $api;
    }

    /**
     * Create input fields
     *
     * @return KiTT_InputValues
     */
    protected function createInputValues()
    {
        return new KiTT_InputValues;
    }

    /**
     * Create a pclass collection
     *
     * @param string $paymentCode payment code
     *
     * @return KiTT_PClassCollection
     */
    protected function createPClassCollection($paymentCode)
    {
        return new KiTT_PClassCollection(
            $this->api, $this->formatter, $this->sum,
            KlarnaFlags::CHECKOUT_PAGE, $paymentCode
        );
    }

    /**
     * Create a payment option
     *
     * @param string $paymentCode payment widget type
     *
     * @return KiTT_Payment_Option
     */
    protected function createOption($paymentCode)
    {
        return new KiTT_Payment_Option(
            $this->config, $paymentCode, $this->locale, $this->formatter,
            $this->createPClassCollection($paymentCode), $this->translator,
            $this->templateLoader, $this->lookup, $this->createInputValues(),
            new KiTT_VFS, $this->sum
        );
    }

    /**
     * Get a payment option
     *
     * @param string $paymentCode payment widget type
     *
     * @return KiTT_Payment_Option
     */
    public function getOption($paymentCode)
    {
        if (!array_key_exists($paymentCode, $this->options)) {
            $this->options[$paymentCode] = $this->createOption($paymentCode);
        }
        return $this->options[$paymentCode];
    }

}
