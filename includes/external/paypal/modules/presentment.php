<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  if (!defined('POPUP_CONTENT_LINK_PARAMETERS')) {
    define('POPUP_CONTENT_LINK_PARAMETERS', '&KeepThis=true&TB_iframe=true&height=400&width=600');
  }
  if (!defined('POPUP_CONTENT_LINK_CLASS')) {
    define('POPUP_CONTENT_LINK_CLASS', 'thickbox');
  }
  $link_parameters = defined('TPL_POPUP_CONTENT_LINK_PARAMETERS') ? TPL_POPUP_CONTENT_LINK_PARAMETERS : POPUP_CONTENT_LINK_PARAMETERS;
  $link_class = defined('TPL_POPUP_CONTENT_LINK_CLASS') ? TPL_POPUP_CONTENT_LINK_CLASS : POPUP_CONTENT_LINK_CLASS;
  $link = xtc_href_link('callback/paypal/paypalinstallment.php', 'amount='.$total.'&country='.$country['countries_iso_code_2'].$link_parameters, $request_type);

  $store_owner = explode("\n", STORE_NAME_ADDRESS);
  for ($i=0, $n=count($store_owner); $i<$n; $i++) {
    if (trim($store_owner[$i]) == '') {
      unset($store_owner[$i]);
    } else {
      $store_owner[$i] = trim($store_owner[$i]);
    }
  }
  $store_owner = implode(', ', $store_owner);
?>