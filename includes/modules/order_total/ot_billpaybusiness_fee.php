<?php
/* -----------------------------------------------------------------------------------------
   $Id: ot_billpaybusiness_fee.php 4200 2013-01-10 19:47:11Z Tomcraft1980 $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   Copyright (c) 2010 Billpay GmbH

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

require_once('ot_billpay_fee.php');

class ot_billpaybusiness_fee extends ot_billpay_fee{
	var $_paymentIdentifier = 'BILLPAYBUSINESS';

	function addFee() {
		if ($_SESSION['payment'] == 'billpay' || $_POST['payment'] == 'billpay') {
			if($this->_checkFeeGroup(1)==2)
				return $_SESSION['billpay_preselect'] == 'b2b';
			else
				return $this->_checkFeeGroup(1);
		}
		return false;
	}
}

?>