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
      && ((TRACKING_COUNT_ADMIN_ACTIVE == 'true' && $_SESSION['customers_status']['customers_status_id'] == '0')
          || $_SESSION['customers_status']['customers_status_id'] != '0'
          )
      )
  {            
    $beginCode = '<script>';
    if (defined('MODULE_COOKIE_CONSENT_STATUS') && strtolower(MODULE_COOKIE_CONSENT_STATUS) == 'true' && (in_array(6, $_SESSION['tracking']['allowed']) || defined('COOKIE_CONSENT_NO_TRACKING'))) {
      $beginCode = '<script async data-type="text/javascript" type="as-oil" data-purposes="6" data-managed="as-oil">';
    }
        
    $beginCode .= "
    !function (f, b, e, v, n, t, s) {
      if (f.fbq) return; n = f.fbq = function () {
        n.callMethod ?
          n.callMethod.apply(n, arguments) : n.queue.push(arguments)
      }; if (!f._fbq) f._fbq = n;
      n.push = n; n.loaded = !0; n.version = '2.0'; n.queue = []; t = b.createElement(e); t.async = !0;
      t.src = v; s = b.getElementsByTagName(e)[0]; s.parentNode.insertBefore(t, s)
    }(window,document, 'script', 'https://connect.facebook.net/en_US/fbevents.js');

    fbq('init', '".TRACKING_FACEBOOK_ID."');
    fbq('track', 'PageView');
    ";

    $trackingCode = null;

    $endCode = '
  </script>
  <noscript>
    <img height="1" width="1" style="display:none" src="https://www.facebook.com/tr?id='.TRACKING_FACEBOOK_ID.'&amp;ev=PageView&amp;noscript=1"/>
    ';

    if (strpos($PHP_SELF, FILENAME_CHECKOUT_SUCCESS) !== false
        && !in_array('FB-'.$last_order, $_SESSION['tracking']['order'])
        )
    {
      // include needed functions
      require_once (DIR_FS_INC.'get_order_total.inc.php');
      
      $_SESSION['tracking']['order'][] = 'FB-'.$last_order;

      $query = xtc_db_query("SELECT currency
                             FROM " . TABLE_ORDERS . "
                            WHERE orders_id = '" . $last_order . "'");
      $orders = xtc_db_fetch_array($query);
      $total = get_order_total($last_order);

      $trackingCode = '
      fbq(\'track\', \'Purchase\', {
        currency: "'.$orders['currency'].'", 
        value: '.number_format($total, 2, '.', '').'
      });
      ';

      $endCode .= '<img height="1" width="1" style="display:none" src="https://www.facebook.com/tr?id='.TRACKING_FACEBOOK_ID.'&amp;ev=Purchase&amp;cd[value]='.number_format($total, 2, '.', '').'&amp;cd[currency]='.$orders['currency'].'&amp;noscript=1"/>
      ';
    }
    
    $endCode .= '
  </noscript>
  ';
    
    echo $beginCode . $trackingCode . $endCode;  
  }
?>