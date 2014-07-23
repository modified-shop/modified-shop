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
 * (c) 2011 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

require_once(DIR_MAGNALISTER_MODULES.'magnacompatible/configure.php');

class LaaryConfigure extends MagnaCompatibleConfigure {

	protected function getAuthValuesFromPost() {
		$nUser = trim($_POST['conf'][$this->marketplace.'.username']);
		$nPass = trim($_POST['conf'][$this->marketplace.'.password']);
		$nMPUser = trim($_POST['conf'][$this->marketplace.'.mpusername']);
		$nMPPass = trim($_POST['conf'][$this->marketplace.'.mppassword']);
		$nPass = $this->processPasswordFromPost('password', $nPass);
		$nMPPass = $this->processPasswordFromPost('mppassword', $nMPPass);

		if (empty($nUser)) {
			unset($_POST['conf'][$this->marketplace.'.username']);
		}
		if (empty($nMPUser)) {
			unset($_POST['conf'][$this->marketplace.'.mpusername']);
		}
		if (($nPass === false) || ($nMPPass === false)) {
			unset($_POST['conf'][$this->marketplace.'.password']);
			unset($_POST['conf'][$this->marketplace.'.mppassword']);
			return false;
		}
		return array (
			'USERNAME' => $nUser,
			'PASSWORD' => $nPass,
			'MPUSERNAME' => $nMPUser,
			'MPPASSWORD' => $nMPPass,
		);
	}
	
	protected function getFormFiles() {
		$forms = parent::getFormFiles();
		$forms[] = 'checkinMarketplaceRegion';
		$forms[] = 'prepare/useShopCatsAsOwn';
		//$forms[] = 'ordersimport_pending';
		return $forms;
	}
	
	protected function loadChoiseValues() {
		parent::loadChoiseValues();
		/*
		if (isset($this->form['orders']['fields']['unpaidsatus'])) {
			mlGetOrderStatus($this->form['orders']['fields']['unpaidsatus']);
		}
		*/
	}
	
}