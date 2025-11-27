<?php
/*
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
 * (c) 2010 - 2021 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

require_once(DIR_MAGNALISTER_MODULES.'magnacompatible/crons/MagnaCompatibleImportOrders.php');

class OttoImportOrders extends MagnaCompatibleImportOrders {

    public function __construct($mpID, $marketplace) {
        parent::__construct($mpID, $marketplace);
        $this->gambioPropertiesEnabled = (getDBConfigValue('general.options', '0', 'old') == 'gambioProperties');
    }

    protected function getConfigKeys() {
        $keys = parent::getConfigKeys();
        $keys['OrderStatusOpen'] = array(
            'key' => 'orders.status.processable',
            'default' => '',
        );
        return $keys;
    }

    protected function getPastTimeOffset() {
        return 60 * 60 * 24 * 30;
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
        return trim(
            sprintf(ML_GENERIC_AUTOMATIC_ORDER_MP_SHORT, $this->marketplaceTitle)."\n".
            'OTTO '.ML_LABEL_ORDER_ID.': '.$this->o['orderInfo']['OttoOrderNumber']."\n\n".
            $this->comment
        );
    }

    /**
     * add 'blacklisted-' to customer's e-mail address
     *  if configured so (not recommended)
     *
     * @return array
     */
    protected function insertCustomer() {
        if (getDBConfigValue(array($this->marketplace . '.mailaddress.blacklist', 'val'), $this->mpID, false)) {
            if ($this->verbose) echo __FUNCTION__.": otto.mailaddress.blacklist == true\n";
            $this->o['customer']['customers_email_address'] = 'blacklisted-'.$this->o['customer']['customers_email_address'];
        }

        return parent::insertCustomer();
    }

    /**
     * Remove "blacklisted-" from mail if present and used from customer to ensure to send the promotion mail
     */
    protected function sendPromoMail() {
        if (($this->config['MailSend'] != 'true') || (get_class($this->db) == 'MagnaTestDB')) {
            return;
        }
        // mail addresses for OTTO customers can have a 'blacklisted-' added by us,
        // if the customer configured so. Therefore, if the merchant wishes to send an e-mail, we have to
        // remove this prefix.
        sendSaleConfirmationMail(
            $this->mpID,
            str_replace('blacklisted-', '', $this->o['customer']['customers_email_address']),
            $this->generatePromoMailContent()
        );
    }

    /**
     * add 'blacklisted-' to customer's e-mail address
     *  if configured so (not recommended)
     */
    protected function doBeforeInsertOrder() {
        if (getDBConfigValue(array($this->marketplace . '.mailaddress.blacklist', 'val'), $this->mpID, false)) {
            if ($this->verbose) echo __FUNCTION__.": otto.mailaddress.blacklist == true\n";
            $this->o['order']['customers_email_address'] = 'blacklisted-'.$this->o['order']['customers_email_address'];
        }
    }
}
