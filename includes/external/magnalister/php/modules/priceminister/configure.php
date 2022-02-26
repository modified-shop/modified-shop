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
 * (c) 2010 - 2020 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

require_once(DIR_MAGNALISTER_MODULES.'magnacompatible/configure.php');

class PriceministerConfigure extends MagnaCompatibleConfigure {

	protected function getAuthValuesFromPost() {
		$nAPIToken = trim($_POST['conf'][$this->marketplace.'.apitoken']);
        $nAPIToken = $this->processPasswordFromPost('apitoken', $nAPIToken);

		$nMPUser = trim($_POST['conf'][$this->marketplace.'.mpusername']);

		if (empty($nMPUser)) {
			unset($_POST['conf'][$this->marketplace.'.mpusername']);
		}

        if (empty($nAPIToken)) {
            unset($_POST['conf'][$this->marketplace.'.apitoken']);
            return false;
        }
		
		$data = array (
			'TOKEN' => $nAPIToken,
			'USERNAME' => $nMPUser,
		);

		return $data;
	}
	
	protected function getFormFiles() {
		$forms = parent::getFormFiles();
		$forms[] = 'prepareadd';
		$forms[] = 'orderStatus';
		$forms[] = 'product_template_generic';
        $forms[] = 'email_template_generic';

		return $forms;
	}

	protected function loadChoiseValues() {
		parent::loadChoiseValues();
		if ($this->isAuthed) {
		    PriceministerHelper::GetConditionTypesConfig($this->form['prepare']['fields']['condition']);
            PriceministerHelper::GetCarriersConfig($this->form['orderSyncState']['fields']['carrier']);
            PriceministerHelper::GetCountriesConfig($this->form['orders']['fields']['shippingfromcountry']);

            $orderStatuses = array();
			mlGetOrderStatus($orderStatuses);
			$this->form['orderSyncState']['fields']['shippedstatus']['values'] = $orderStatuses['values'];
			$this->form['orderSyncState']['fields']['acceptstatus']['values'] = $orderStatuses['values'];
			$this->form['orderSyncState']['fields']['cancelstatus']['values'] = $orderStatuses['values'];
			$this->form['orderSyncState']['fields']['refusestatus']['values'] = $orderStatuses['values'];

			unset($this->form['checkin']['fields']['leadtimetoship']);
		}
	}
	
	protected function finalizeForm() {
		parent::finalizeForm();
		if (!$this->isAuthed) {
			$this->form = array (
				'login' => $this->form['login']
			);
			return;
		}
	}

	protected function loadChoiseValuesAfterProcessPOST() {
		global $magnaConfig;
		if (!$this->isAuthed) {
			unset($magnaConfig['db'][$this->mpID]['priceminister.secretkey']);
		} elseif (isset($magnaConfig['db'][$this->mpID]['priceminister.orderstatus.autoacceptance'])
			&& $magnaConfig['db'][$this->mpID]['priceminister.orderstatus.autoacceptance']['val'] == false) {
			$this->boxes .= '<p class="noticeBox">' . ML_PRICEMINISTER_ERROR_ORDERSTATUS_AUTOACCEPTANCE . '</p>';
		}
	}
	
}
