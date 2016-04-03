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
 * (c) 2012 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

require_once(DIR_MAGNALISTER_MODULES.'magnacompatible/configure.php');

class LafeoConfigure extends MagnaCompatibleConfigure {

	protected function getAuthValuesFromPost() {
		$nUser = trim($_POST['conf'][$this->marketplace.'.username']);
		$nPass = trim($_POST['conf'][$this->marketplace.'.password']);
		$nPass = $this->processPasswordFromPost('password', $nPass);

		if (empty($nUser)) {
			unset($_POST['conf'][$this->marketplace.'.username']);
		}

		if ($nPass === false) {
			unset($_POST['conf'][$this->marketplace.'.password']);
			return false;
		}
		return array (
			'USERNAME' => $nUser,
			'PASSWORD' => $nPass,
		);
	}
	
	protected function getFormFiles() {
		$forms = parent::getFormFiles();
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