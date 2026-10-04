<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

// texts of the module klarna, the texts shared by all Klarna modules are in klarna_shared.php
$klarna_code = 'KLARNA';
include(DIR_FS_CATALOG.'lang/english/modules/payment/klarna_shared.php');

$lang_array['MODULE_PAYMENT_'.$klarna_code.'_TEXT_TITLE'] = 'Klarna';
$lang_array['MODULE_PAYMENT_'.$klarna_code.'_TEXT_ERROR_MESSAGE'] = 'The payment with Klarna was cancelled.';

foreach ($lang_array as $key => $val) {
  defined($key) or define($key, $val);
}
