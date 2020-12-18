<?php
  /* --------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2014 [www.modified-shop.org]
   --------------------------------------------------------------
   Released under the GNU General Public License
   --------------------------------------------------------------*/

    
  if (TRACKING_FACEBOOK_ACTIVE == 'true'
      && strpos($PHP_SELF, FILENAME_CHECKOUT_SUCCESS) !== false
      && ((TRACKING_COUNT_ADMIN_ACTIVE == 'true' && $_SESSION['customers_status']['customers_status_id'] == '0')
          || $_SESSION['customers_status']['customers_status_id'] != '0'
          )
      )
  {  
    // include needed functions
    require_once (DIR_FS_INC.'get_order_total.inc.php');
  
    $query = xtc_db_query("SELECT currency
                             FROM " . TABLE_ORDERS . "
                            WHERE orders_id = '" . $last_order . "'");
    $orders = xtc_db_fetch_array($query);
  
    $id = TRACKING_FACEBOOK_ID;
  
    if (!in_array('FB-'.$last_order, $_SESSION['tracking']['order'])) {  
      $_SESSION['tracking']['order'][] = 'FB-'.$last_order;
      $total = get_order_total($last_order);
      $beginCode = '<script>';
      if (defined('MODULE_COOKIE_CONSENT_STATUS') && strtolower(MODULE_COOKIE_CONSENT_STATUS) == 'true' && (in_array(6, $_SESSION['tracking']['allowed']) || defined('COOKIE_CONSENT_NO_TRACKING'))) {
        $beginCode = '<script async data-type="text/javascript" type="as-oil" data-purposes="6" data-managed="as-oil">';
      }
      $beginCode .= '
      (function() {
        var _fbq = window._fbq || (window._fbq = []);
        if (!_fbq.loaded) {
          var fbds = document.createElement(\'script\');
          fbds.async = true;
          fbds.src = \'//connect.facebook.net/en_US/fbds.js\';
          var s = document.getElementsByTagName(\'script\')[0];
          s.parentNode.insertBefore(fbds, s);
          _fbq.loaded = true;
        }
      })();
      ';

      $endCode = 'window._fbq = window._fbq || [];
      window._fbq.push([\'track\', \''.$id.'\', {\'value\':\''.$total.'\',\'currency\':\''.$orders['currency'].'\'}]);
    </script>
      ';
    }
  
    echo $beginCode . $endCode;  
  }
?>