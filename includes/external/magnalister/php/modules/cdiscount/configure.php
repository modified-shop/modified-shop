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

require_once(DIR_MAGNALISTER_MODULES.'magnacompatible/configure.php');

class CdiscountConfigure extends MagnaCompatibleConfigure {

	protected function getAuthValuesFromPost() {
		
		$nMPUser = trim($_POST['conf'][$this->marketplace.'.mpusername']);
		$nMPPass = trim($_POST['conf'][$this->marketplace.'.mppassword']);
		$nMPPass = $this->processPasswordFromPost('mppassword', $nMPPass);
		
		if (empty($nMPUser)) {
			unset($_POST['conf'][$this->marketplace.'.mpusername']);
		}
		
		if ($nMPPass === false) {
			unset($_POST['conf'][$this->marketplace.'.mppassword']);
			return false;
		}
		
		$data = array (
			'MPUSERNAME' => $nMPUser,
			'MPPASSWORD' => $nMPPass,
		);
		#echo print_m($data);
		return $data;
	}
	
	protected function getFormFiles() {
		$forms = parent::getFormFiles();
		$forms[] = 'prepareadd';
		$forms[] = 'orderStatus';
		$forms[] = 'email_template_generic';
	
		return $forms;
	}
	
	protected function loadChoiseValues() {
		parent::loadChoiseValues();
		if ($this->isAuthed) {
			CdiscountHelper::GetConditionTypesConfig($this->form['prepare']['fields']['condition']);
			mlGetOrderStatus($this->form['orderSyncState']['fields']['cancelstatus']);
			mlGetOrderStatus($this->form['orderSyncState']['fields']['shippedstatus']);

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
		if (!$this->isAuthed) {
			global $magnaConfig;

			unset($magnaConfig['db'][$this->mpID]['cdiscount.secretkey']);
			unset($magnaConfig['db'][$this->mpID]['cdiscount.mppassword']);
		}
	}
	
}
