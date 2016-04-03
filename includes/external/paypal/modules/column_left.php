<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  defined( '_VALID_XTC' ) or die( 'Direct Access to this location is not allowed.' );
  
  if ((isset($admin_access['paypal_config']) && $admin_access['paypal_config'] == '1')
      || (isset($admin_access['paypal_profile']) && $admin_access['paypal_profile'] == '1')
      || (isset($admin_access['paypal_webhook']) && $admin_access['paypal_webhook'] == '1')
      || (isset($admin_access['paypal_module']) && $admin_access['paypal_module'] == '1')
      || (isset($admin_access['paypal_payment']) && $admin_access['paypal_payment'] == '1')
      )
  {      
    echo '<li><a href="#" class="menuBoxContentLinkSub"> -PayPal</a><ul>';
    if (isset($admin_access['paypal_config']) && $admin_access['paypal_config'] == '1') echo '<li><a href="' . xtc_href_link('paypal_config.php', '') . '" class="menuBoxContentLink"> -' . TEXT_PAYPAL_TAB_CONFIG . '</a></li>';
    if (isset($admin_access['paypal_profile']) && $admin_access['paypal_profile'] == '1') echo '<li><a href="' . xtc_href_link('paypal_profile.php', '') . '" class="menuBoxContentLink"> -' . TEXT_PAYPAL_TAB_PROFILE . '</a></li>';
    if (isset($admin_access['paypal_webhook']) && $admin_access['paypal_webhook'] == '1') echo '<li><a href="' . xtc_href_link('paypal_webhook.php', '') . '" class="menuBoxContentLink"> -' . TEXT_PAYPAL_TAB_WEBHOOK . '</a></li>';
    if (isset($admin_access['paypal_module']) && $admin_access['paypal_module'] == '1') echo '<li><a href="' . xtc_href_link('paypal_module.php', '') . '" class="menuBoxContentLink"> -' . TEXT_PAYPAL_TAB_MODULE . '</a></li>';
    if (isset($admin_access['paypal_payment']) && $admin_access['paypal_payment'] == '1') echo '<li><a href="' . xtc_href_link('paypal_payment.php', '') . '" class="menuBoxContentLink"> -' . TEXT_PAYPAL_TAB_TRANSACTIONS . '</a></li>';
    echo '  </ul></li>';
  }
?>