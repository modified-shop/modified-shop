<?php
/**
 * Extension of KlarnaBase for Invoices
 *
 * PHP Version 5.2
 *
 * @category Payment
 * @package  Klarna_Module_XtCommerce
 * @author   MS Dev <ms.modules@klarna.com>
 * @license  http://opensource.org/licenses/BSD-2-Clause BSD2
 * @link     http://integration.klarna.com
 */

require_once DIR_FS_DOCUMENT_ROOT . 'includes/external/klarna/class.KlarnaCore.php';
require_once DIR_KLARNA . 'class.KlarnaBase.php';

/**
 * Klarna Invoice Module
 *
 * @category Payment
 * @package  Klarna_Module_XtCommerce
 * @author   MS Dev <ms.modules@klarna.com>
 * @license  http://opensource.org/licenses/BSD-2-Clause BSD2
 * @link     http://integration.klarna.com
 */
class klarna_invoice extends KlarnaBase
{

    /**
     * The constructor
     *
     * @return void
     */
    public function __construct()
    {
        $this->code = 'klarna_invoice';
        $this->sort_order = MODULE_PAYMENT_KLARNA_INVOICE_SORT_ORDER;

        parent::__construct(KiTT::INVOICE);
    }

    /**
     * Update Status
     *
     * @return void
     */
    public function update_status()
    {
        parent::updateStatus();
    }

    /**
     * No Javascript Validation
     *
     * @return false
     */
    public function javascript_validation()
    {
        return false;
    }

    /**
     * This function outputs the payment method title/text and if required, the
     * input fields.
     *
     * @return array Data to present in page
     */
    public function selection()
    {
        return parent::selection();
    }

    /**
     * This function implements any checks of any conditions after payment
     * method has been selected.
     *
     * @return void
     */
    public function pre_confirmation_check()
    {
        parent::preConfirmationCheck();
    }

    /**
     * Implements any checks or processing on the order information before
     * proceeding to payment confirmation.
     *
     * @return array
     */
    public function confirmation()
    {
        return parent::confirmation();
    }

    /**
     * Outputs the html form hidden elements sent as POST data to the payment
     * gateway.
     *
     * @return string
     */
    public function process_button()
    {
        return parent::processButton();
    }

    /**
     * Build the cart and do the actual call to Klarna Online
     *
     * @return void
     */
    public function before_process()
    {
        parent::beforeProcess();
    }

    /**
     * Update order comments
     *
     * @return false
     */
    public function after_process()
    {
        return parent::afterProcess();
    }

    /**
     * get error message.
     *
     * @return array     error title and error message
     */
    public function get_error()
    {
        return parent::getError();
    }

    /**
     * Check if module is enabled
     *
     * @return int   1 if enabled
     */
    public function check()
    {
        return parent::check();
    }

    /**
     * Install script
     *
     * @return void
     */
    public function install()
    {
        parent::install();
    }

    /**
     * Uninstall script
     *
     * @return void
     */
    public function remove()
    {
        parent::remove();
    }

    /**
     * Return constants defined by setup
     *
     * @return array of constant identifiers
     */
    public function keys()
    {
        $keys = array(
            'MODULE_PAYMENT_KLARNA_INVOICE_STATUS',
            'MODULE_PAYMENT_KLARNA_INVOICE_LATESTVERSION',
            'MODULE_PAYMENT_KLARNA_INVOICE_LIVEMODE'
        );
        foreach (KiTT_CountryLogic::supportedCountries() as $country) {
            $keys[] = "MODULE_PAYMENT_KLARNA_INVOICE_EID_{$country}";
            $keys[] = "MODULE_PAYMENT_KLARNA_INVOICE_SECRET_{$country}";
            if (KiTT_CountryLogic::needAGB($country)) {
                $keys[] = "MODULE_PAYMENT_KLARNA_INVOICE_AGB_LINK_{$country}";
            }
        }
        $keys = array_merge(
            $keys,
            array(
                'MODULE_PAYMENT_KLARNA_INVOICE_ALLOWED',
                'MODULE_PAYMENT_KLARNA_INVOICE_ORDER_STATUS_ID',
                'MODULE_PAYMENT_KLARNA_INVOICE_ORDER_STATUS_PENDING_ID',
                'MODULE_PAYMENT_KLARNA_INVOICE_SORT_ORDER',
                'MODULE_PAYMENT_KLARNA_INVOICE_ZONE',
                'MODULE_PAYMENT_KLARNA_INVOICE_ARTNO'
            )
        );
        return $keys;
    }
}
