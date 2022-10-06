<?php
/**
 * 888888ba                 dP  .88888.                    dP
 * 88    `8b                88 d8'   `88                   88
 * 88aaaa8P' .d8888b. .d888b88 88        .d8888b. .d8888b. 88  .dP  .d8888b.
 * 88   `8b. 88ooood8 88'  `88 88   YP88 88ooood8 88'  `"" 88888"   88'  `88
 * 88     88 88.  ... 88.  .88 Y8.   .88 88.  ... 88.  ... 88  `8b. 88.  .88
 * dP     dP `88888P' `88888P8  `88888'  `88888P' `88888P' dP   `YP `88888P'
 *
 *                          m a g n a l i s t e r
 *                                      boost your Online-Shop
 *
 * -----------------------------------------------------------------------------
 * (c) 2010 - 2019 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

require_once(DIR_MAGNALISTER_MODULES.'magnacompatible/crons/MagnaCompatibleImportOrders.php');

class MetroImportOrders extends MagnaCompatibleImportOrders {

    public function __construct($mpID, $marketplace) {
        parent::__construct($mpID, $marketplace);
    }

    protected function getConfigKeys() {
        $keys = parent::getConfigKeys();
        $keys['OrderStatusOpen'] = array(
            'key' => 'orderstatus.open',
            'default' => '',
        );
        return $keys;
    }

    protected function getOrdersStatus() {
        return $this->config['OrderStatusOpen'];
    }

    /**
     * Returns the comment for orders.comment (Database).
     * E.g. the comment from the customer or magnalister related information.
     * Use $this->o['order'].
     *
     * @return String
     *    The comment for the order.
     */
    protected function generateOrderComment($blForce = false) {
        if (!$blForce && !getDBConfigValue(array('general.order.information', 'val'), 0, true)) {
        return ''; 
        }
        $comment = sprintf(ML_GENERIC_AUTOMATIC_ORDER_MP_SHORT, $this->marketplaceTitle)."\n".
            'METRO '.ML_LABEL_ORDER_ID.': '.$this->o['orderInfo']['MetroOrderNumber'];
        if (!empty($this->comment)) {
            $comment .= "\n\n".$this->comment;
        }
        if (!empty($this->o['orderStatus']['comments'])) {
            $comment .= "\n\n".$this->o['orderStatus']['comments'];
        }
        return trim($comment); 
    }

    protected function getPaymentMethod() {
        if ($this->config['PaymentMethod'] == 'matching') {
            return $this->getPaymentClassForMetroPaymentMethod($this->o['order']['payment_method']);
        }
        return $this->config['PaymentMethod'];
    }

/* Zahlungsarten METRO:
  Rate-pay
  Credit-card
  open_invoice
  marketplace
  Rate-pay-dd
  credit_card
  paypal
  direct_debit
  sofort

  Für Ratenzahlung finde ich kein Modul
*/
private function getPaymentClassForMetroPaymentMethod($paymentMethod) {
    $PaymentModules = explode(';', MODULE_PAYMENT_INSTALLED);
    $class = 'metro'; // oder marketplace?

    if (('Credit-card' == $paymentMethod)
        || ('credit_card' == $paymentMethod)) {
        # Kreditkarte
        if (in_array('cc.php', $PaymentModules))
            $class = 'cc';
        else if (in_array('heidelpaycc.php', $PaymentModules))
            $class = 'heidelpaycc';
        else if (in_array('moneybookers_cc.php', $PaymentModules))
            $class = 'moneybookers_cc';
        else if (in_array('uos_kreditkarte_modul.php', $PaymentModules))
            $class = 'uos_kreditkarte_modul';

    } else if ('open_invoice' == $paymentMethod) {
        # Auf Rechnung
        if (in_array('invoice.php', $PaymentModules))
            $class = 'invoice';

    } else if ('paypal' == $paymentMethod) {
        # PayPal
        if (in_array('paypal.php', $PaymentModules))
            $class = 'paypal';
        else if (in_array('paypalng.php', $PaymentModules))
            $class = 'paypalng';
        else if (in_array('paypal_ipn.php', $PaymentModules))
            $class = 'paypal_ipn';
        else if (in_array('paypalexpress.php', $PaymentModules))
            $class = 'paypalexpress';
        else if (in_array('paypal3.php', $PaymentModules))
            $class = 'paypal3';
        else if (in_array('paypalclassic.php', $PaymentModules))
            $class = 'paypalclassic';
        else if (in_array('paypalplus.php', $PaymentModules))
            $class = 'paypalplus';
        else if (in_array('paypallink.php', $PaymentModules))
            $class = 'paypallink';
        else if (in_array('paypalpluslink.php', $PaymentModules))
            $class = 'paypalpluslink';

    } else if ('direct_debit' == $paymentMethod) {
        # Lastschrift
        if (in_array('sepa.php', $PaymentModules))
            $class = 'sepa';
        else if (in_array('moneybookers_elv.php', $PaymentModules))
            $class = 'moneybookers_elv';
        else if (in_array('uos_lastschrift_de_modul.php', $PaymentModules))
            $class = 'uos_lastschrift_de_modul';
        else if (in_array('banktransfer.php', $PaymentModules))
            $class = 'banktransfer';

    } else if ('sofort' == $paymentMethod) {
        # Sofortüberweisung
        if (in_array('sofort_sofortueberweisung.php', $PaymentModules))
            $class = 'sofort_sofortueberweisung';
    }

    return $class;
}

}
