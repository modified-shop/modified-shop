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
  
  $menu_output = false;
  if (!isset($menu_access)) {
    $menu_access = array();
    $menu_output = true;
  }

  if ((isset($admin_access['paypal_config']) && $admin_access['paypal_config'] == '1')
      || (isset($admin_access['paypal_banner']) && $admin_access['paypal_banner'] == '1')
      || (isset($admin_access['paypal_profile']) && $admin_access['paypal_profile'] == '1')
      || (isset($admin_access['paypal_webhook']) && $admin_access['paypal_webhook'] == '1')
      || (isset($admin_access['paypal_module']) && $admin_access['paypal_module'] == '1')
      )
  {      
    $menu_access[] = '<li><a href="javascript:void(0)" class="menuBoxContentLinkSub"> -PayPal</a><ul>';
    if (isset($admin_access['paypal_info']) && $admin_access['paypal_info'] == '1') $menu_access[] = '<li><a href="' . xtc_href_link('paypal_info.php') . '" class="menuBoxContentLink"> -' . TEXT_PAYPAL_TAB_INFO . '</a></li>';
    if (isset($admin_access['paypal_module']) && $admin_access['paypal_module'] == '1') $menu_access[] = '<li><a href="' . xtc_href_link('paypal_module.php') . '" class="menuBoxContentLink"> -' . TEXT_PAYPAL_TAB_MODULE . '</a></li>';
    if (isset($admin_access['paypal_config']) && $admin_access['paypal_config'] == '1') $menu_access[] = '<li><a href="' . xtc_href_link('paypal_config.php') . '" class="menuBoxContentLink"> -' . TEXT_PAYPAL_TAB_CONFIG . '</a></li>';
    if (isset($admin_access['paypal_banner']) && $admin_access['paypal_banner'] == '1') $menu_access[] = '<li><a href="' . xtc_href_link('paypal_banner.php') . '" class="menuBoxContentLink"> -' . TEXT_PAYPAL_TAB_BANNER . '</a></li>';
    if (isset($admin_access['paypal_profile']) && $admin_access['paypal_profile'] == '1') $menu_access[] = '<li><a href="' . xtc_href_link('paypal_profile.php') . '" class="menuBoxContentLink"> -' . TEXT_PAYPAL_TAB_PROFILE . '</a></li>';
    if (isset($admin_access['paypal_webhook']) && $admin_access['paypal_webhook'] == '1') $menu_access[] = '<li><a href="' . xtc_href_link('paypal_webhook.php') . '" class="menuBoxContentLink"> -' . TEXT_PAYPAL_TAB_WEBHOOK . '</a></li>';
    $menu_access[] = '</ul></li>';
  } elseif (isset($admin_access['paypal_info']) && $admin_access['paypal_info'] == '1') {
    $menu_access[] = '<li><a href="' . xtc_href_link('paypal_info.php', '') . '" class="menuBoxContentLink"> -PayPal</a></li>';
  }

  if ($menu_output === true) {
    echo implode(PHP_EOL, $menu_access);
    unset($menu_access);
  }
