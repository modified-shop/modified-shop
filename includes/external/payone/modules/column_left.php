<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
 	 based on:
	  (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
	  (c) 2002-2003 osCommerce - www.oscommerce.com
	  (c) 2001-2003 TheMedia, Dipl.-Ing Thomas Plänkers - http://www.themedia.at & http://www.oscommerce.at
	  (c) 2003 XT-Commerce - community made shopping http://www.xt-commerce.com
    (c) 2013 Gambio GmbH - http://www.gambio.de
  
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  defined( '_VALID_XTC' ) or die( 'Direct Access to this location is not allowed.' );

  if ((isset($admin_access['payone_config']) && $admin_access['payone_config'] == '1') || (isset($admin_access['payone_log']) && $admin_access['payone_log'] == '1')) {
    echo '<li><a href="#" class="menuBoxContentLinkSub"> -PAYONE</a><ul>';
      if (isset($admin_access['payone_config']) && $admin_access['payone_config'] == '1') echo '<li><a href="' . xtc_href_link('payone_config.php', '') . '" class="menuBoxContentLink"> -PAYONE Config</a></li>';
      if (isset($admin_access['payone_logs']) && $admin_access['payone_logs'] == '1') echo '<li><a href="' . xtc_href_link('payone_logs.php', '') . '" class="menuBoxContentLink"> -PAYONE Log</a></li>';
    echo '  </ul></li>';
  }
?>