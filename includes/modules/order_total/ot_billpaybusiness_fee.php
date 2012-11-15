<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   xtcModified - community made shopping
   http://www.xtc-modified.org

   Copyright (c) 2011 xtcModified
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