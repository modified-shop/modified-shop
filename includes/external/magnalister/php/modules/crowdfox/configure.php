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
 * $Id$
 *
 * (c) 2010 - 2014 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

require_once(DIR_MAGNALISTER_MODULES . 'magnacompatible/configure.php');

class CrowdfoxConfigure extends MagnaCompatibleConfigure {

    protected function getAuthValuesFromPost() {
        $nMPUser = trim($_POST['conf'][$this->marketplace . '.mpusername']);
        $companyName = trim($_POST['conf'][$this->marketplace . '.companyname']);

        $nMPPass = trim($_POST['conf'][$this->marketplace . '.mppassword']);
        $nMPPass = $this->processPasswordFromPost('mppassword', $nMPPass);

        if (empty($nMPUser)) {
            unset($_POST['conf'][$this->marketplace . '.mpusername']);
            return false;
        }

        if (empty($companyName)) {
            unset($_POST['conf'][$this->marketplace . '.companyname']);
            return false;
        }

        if ($nMPPass === false) {
            unset($_POST['conf'][$this->marketplace . '.mppassword']);
            return false;
        }

        $data = array(
            'USERNAME' => $nMPUser,
            'PASSWORD' => $nMPPass,
            'COMPANYNAME' => $companyName,
        );

        return $data;
    }

    protected function getFormFiles() {
        $forms = parent::getFormFiles();
        $forms[] = 'prepareadd';
        $forms[] = 'orderStatus';

        return $forms;
    }

    protected function loadChoiseValues() {
        parent::loadChoiseValues();
        if ($this->isAuthed) {
            CrowdfoxHelper::GetShippingMethodsConfig($this->form['prepare']['fields']['shippingmethod']);

            $orderStatuses = array();
            mlGetOrderStatus($orderStatuses);
            $this->form['orderSyncState']['fields']['shippedstatus']['values'] = $orderStatuses['values'];

            unset($this->form['checkin']['fields']['leadtimetoship']);
        }
    }

    protected function finalizeForm() {
        parent::finalizeForm();
        if (!$this->isAuthed) {
            $this->form = array(
                'login' => $this->form['login'],
            );

            return;
        }
    }

    protected function loadChoiseValuesAfterProcessPOST() {
        if (!$this->isAuthed) {
            global $magnaConfig;

            unset($magnaConfig['db'][$this->mpID]['crowdfox.companyname']);
            unset($magnaConfig['db'][$this->mpID]['crowdfox.mppassword']);
        }
    }

    protected function loadErrorMessage() {
        $nMPUser = trim($_POST['conf'][$this->marketplace . '.mpusername']);
        $companyName = trim($_POST['conf'][$this->marketplace . '.companyname']);

        $nMPPass = trim($_POST['conf'][$this->marketplace . '.mppassword']);
        $nMPPass = $this->processPasswordFromPost('mppassword', $nMPPass);

        $errorMessage = '';

        if (empty($nMPUser)) {
            $errorMessage .= ML_ERROR_CROWDFOX_USERNAME;
        }

        if (empty($companyName)) {
            $errorMessage .= ' ' . ML_ERROR_CROWDFOX_COMPANYNAME;
        }

        if ($nMPPass === false) {
            $errorMessage .= ' ' . ML_ERROR_INVALID_PASSWORD;
        }

        return $errorMessage;
    }

}
