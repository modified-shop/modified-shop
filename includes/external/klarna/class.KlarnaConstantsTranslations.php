<?php
/**
 * Abstraction of defining constants for xtCommerce 3.0.4s language system.
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

/**
 * Handle translations of the admin page for the Klarna modules
 *
 * @category Payment
 * @package  Klarna_Module_XtCommerce
 * @author   MS Dev <ms.modules@klarna.com>
 * @license  http://opensource.org/licenses/BSD-2-Clause BSD2
 * @link     http://integration.klarna.com
 */
class KlarnaConstantsTranslations
{
    /**
     * @var string
     */
    private $_code;

    /**
     * @var array
     */
    private $_paymentModuleMap = array(
        'STATUS' => 'KLARNA_ADMIN_STATUS',
        'ZONE' => 'KLARNA_ADMIN_ZONE',
        'ALLOWED' => 'KLARNA_ADMIN_ACTIVATED',
        'LIVEMODE' => 'KLARNA_ADMIN_SERVER',
        'LATESTVERSION' => 'KLARNA_ADMIN_LATEST_VERSION',
        'ORDER_STATUS_ID' => 'KLARNA_ADMIN_ORDER_STATUS',
        'ORDER_STATUS_PENDING_ID' => 'KLARNA_ADMIN_ORDER_STATUS_PENDING',
        'ARTNO' => 'KLARNA_ADMIN_ARTNO',
        'SORT_ORDER' => 'KLARNA_ADMIN_SORT_ORDER'
    );

    /**
     * @var array
     */
    private $_invocefeeModuleMap = array(
        'STATUS' => 'KLARNA_FEE_STATUS',
        'SORT_ORDER' => 'KLARNA_ADMIN_SORT_ORDER',
        'MODE' => 'KLARNA_FEE_MODE',
        'TAX_CLASS' => 'KLARNA_ADMIN_TAX_CLASS'
    );

    /**
     * Create the instance of this payment method translator
     *
     * @param string $lang language iso code
     */
    public function __construct($lang)
    {
        if ($lang === null) {
            $lang = 'en';
        }
        KlarnaUtils::configureKITT();
        $this->_translator = KiTT::translator(KiTT::locale(null, $lang));
    }

    /**
     * Define the constants for the modules admin fields.
     *
     * @param string $paymentCode KiTT payment code constant, or 'INVOICE_FEE'
     *
     * @return void
     */
    public function translatePaymentModule($paymentCode)
    {
        $this->_setCode($paymentCode);

        foreach ($this->_paymentModuleMap as $key => $value) {
            define(
                "{$this->_code}{$key}_TITLE",
                $this->_translator->translate("{$value}_TITLE")
            );
            define(
                "{$this->_code}{$key}_DESC",
                $this->_translator->translate("{$value}_DESC")
            );
        }

        $title = '';
        switch ($paymentCode) {
        case KiTT::SPEC:
            $title = 'MODULE_SPEC_TEXT_TITLE';
            break;
        case KiTT::PART:
            $title = 'MODULE_PARTPAY_TEXT_TITLE';
            break;
        case KiTT::INVOICE:
            $title = 'MODULE_INVOICE_TEXT_TITLE';
            break;
        }

        if ($title !== '') {
            define(
                "{$this->_code}TEXT_TITLE",
                $this->_translator->translate($title)
            );
        }

        $this->_countryFields();
    }

    /**
     * Invoice Fee Module fields
     *
     * @return void
     */
    public function translateInvoiceFeeModule()
    {
        $this->_code = 'MODULE_ORDER_TOTAL_KLARNA_FEE_';
        foreach ($this->_invocefeeModuleMap as $key => $value) {
            define(
                "{$this->_code}{$key}_TITLE",
                $this->_translator->translate("{$value}_TITLE")
            );
            define(
                "{$this->_code}{$key}_DESC",
                $this->_translator->translate("{$value}_DESC")
            );
        }

        $this->_countryInvoiceFeeFields();
    }

    /**
     * Country specific invoice fee fields
     *
     * @return void
     */
    private function _countryInvoiceFeeFields()
    {
        foreach (KiTT_CountryLogic::supportedCountries() as $country) {
            $flag = "<span class='klarna_flag_" .
                strtolower($country) . "'></span> ";
            define(
                "{$this->_code}FIXED_{$country}_TITLE",
                "{$flag}{$country} " . $this->_translator->translate(
                    'KLARNA_FEE_FIXED_TITLE'
                )
            );
            define(
                "{$this->_code}FIXED_{$country}_DESC",
                $this->_translator->translate('KLARNA_FEE_FIXED_DESC')
            );
            define(
                "{$this->_code}TABLE_{$country}_TITLE",
                "{$flag}{$country} " . $this->_translator->translate(
                    'KLARNA_FEE_TABLE_TITLE'
                )
            );
            define(
                "{$this->_code}TABLE_{$country}_DESC",
                $this->_translator->translate('KLARNA_FEE_TABLE_DESC')
            );
        }
    }

    /**
     * Country specific fields that don't fit in the array above
     *
     * @return void
     */
    private function _countryFields()
    {
        foreach (KiTT_CountryLogic::supportedCountries() as $country) {
            $flag = "<span class='klarna_flag_" .
                strtolower($country) . "'></span> ";
            define(
                "{$this->_code}EID_{$country}_TITLE",
                "{$flag}{$country} " . $this->_translator->translate(
                    'KLARNA_ADMIN_MERCHANT_ID_TITLE'
                )
            );
            define(
                "{$this->_code}EID_{$country}_DESC",
                $this->_translator->translate('KLARNA_ADMIN_MERCHANT_ID_DESC')
            );
            define(
                "{$this->_code}SECRET_{$country}_TITLE",
                "{$flag}{$country} " . $this->_translator->translate(
                    'KLARNA_ADMIN_SECRET_TITLE'
                )
            );
            define(
                "{$this->_code}SECRET_{$country}_DESC",
                $this->_translator->translate('KLARNA_ADMIN_SECRET_DESC')
            );
            if (KiTT_CountryLogic::needAGB($country)) {
                define(
                    "{$this->_code}AGB_LINK_{$country}_TITLE",
                    "{$flag}{$country} " . $this->_translator->translate(
                        'KLARNA_ADMIN_TAC_TITLE'
                    )
                );
                define(
                    "{$this->_code}AGB_LINK_{$country}_DESC",
                    $this->_translator->translate('KLARNA_ADMIN_TAC_DESC')
                );
            }
        }
    }

    /**
     * Set the module code to use for the given paymentCode
     *
     * @param string $paymentCode KiTT contstant
     *
     * @return void
     */
    private function _setCode($paymentCode)
    {
        switch ($paymentCode) {
        case KiTT::PART:
            $this->_code = 'MODULE_PAYMENT_KLARNA_PARTPAYMENT_';
            break;
        case KiTT::SPEC:
            $this->_code = 'MODULE_PAYMENT_KLARNA_SPECCAMP_';
            break;
        case KiTT::INVOICE:
            $this->_code = 'MODULE_PAYMENT_KLARNA_INVOICE_';
            break;
        default:
            $this->_code = '';
        }
    }
}
