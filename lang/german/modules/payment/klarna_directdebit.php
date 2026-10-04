<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

$klarna_code = 'KLARNA_DIRECTDEBIT';
include(DIR_FS_CATALOG.'lang/german/modules/payment/klarna_shared.php');

$lang_array['MODULE_PAYMENT_'.$klarna_code.'_TEXT_TITLE'] = 'Klarna Lastschrift';
$lang_array['MODULE_PAYMENT_'.$klarna_code.'_TEXT_ADMIN_TITLE'] = 'Klarna Lastschrift<span style="background:#dd2400;color: #fff;font-weight: bold;padding: 2px 5px;border-radius: 4px;margin: 0 0 0 5px;">ALT</span>';
$lang_array['MODULE_PAYMENT_'.$klarna_code.'_TEXT_ERROR_MESSAGE'] = 'Die Zahlung per Lastschrift wurde abgebrochen.';

foreach ($lang_array as $key => $val) {
  defined($key) or define($key, $val);
}
