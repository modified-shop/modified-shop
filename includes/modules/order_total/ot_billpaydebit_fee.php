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

class ot_billpaydebit_fee extends ot_billpay_fee{
  var $_paymentIdentifier = 'BILLPAYDEBIT';

  function addFee() {
    return ($_SESSION['payment'] == 'billpaydebit' || $_POST['payment'] == 'billpaydebit') &&
      $_SESSION['billpay_customer_group'] != 'b';
  }
}
?>