<?php
  /* --------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2014 [www.modified-shop.org]
   --------------------------------------------------------------
   Released under the GNU General Public License
   --------------------------------------------------------------*/

    
  if (defined('MODULE_FACEBOOK_PIXEL_STATUS')
      && MODULE_FACEBOOK_PIXEL_STATUS == 'true'
      && defined('MODULE_FACEBOOK_PIXEL_ID')
      && MODULE_FACEBOOK_PIXEL_ID != ''
      && ((defined('MODULE_FACEBOOK_PIXEL_COUNT_ADMIN') && MODULE_FACEBOOK_PIXEL_COUNT_ADMIN == 'true' && $_SESSION['customers_status']['customers_status_id'] == '0')
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

    fbq('init', '".MODULE_FACEBOOK_PIXEL_ID."');
    fbq('track', 'PageView');
    ";

    $trackingCode = null;

    $endCode = '
  </script>
  <noscript>
    <img height="1" width="1" style="display:none" src="https://www.facebook.com/tr?id='.MODULE_FACEBOOK_PIXEL_ID.'&amp;ev=PageView&amp;noscript=1"/>
    ';

    if (isset($_SESSION['new_products_id_in_cart'])) {
      $products_query = xtc_db_query("SELECT p.products_id,
                                             p.products_model,
                                             p.products_tax_class_id,
                                             pd.products_name
                                        FROM ".TABLE_PRODUCTS." p
                                        JOIN ".TABLE_PRODUCTS_DESCRIPTION." pd
                                             ON p.products_id = pd.products_id
                                                AND pd.language_id = '".(int)$_SESSION['languages_id']."'
                                       WHERE p.products_id = '".(int)$_SESSION['new_products_id_in_cart']."'");
      $products = xtc_db_fetch_array($products_query);
      $trackingCode .= '
      fbq(\'track\', \'AddToCart\', {
        content_name: "'.$products['products_name'].'", 
        content_ids: ["'.$products['products_model'].'"], 
        content_type: "product",
        currency: "'.$_SESSION['currency'].'", 
        value: '.$xtPrice->xtcGetPrice($products['products_id'], false, 1, $products['products_tax_class_id']).'
      });
    ';
    }


    if (strpos($PHP_SELF, FILENAME_PRODUCT_INFO) !== false
        && is_object($product)
        && $product->isProduct() !== false
        )
    {
      $trackingCode .= '
      fbq(\'track\', \'ViewContent\', {
        content_name: "'.$product->data['products_name'].'", 
        content_ids: ["'.$product->data['products_model'].'"], 
        content_type: "product",
        currency: "'.$_SESSION['currency'].'", 
        value: '.$xtPrice->xtcGetPrice($product->data['products_id'], false, 1, $product->data['products_tax_class_id']).'
      });
    ';
    }


    if (strpos($PHP_SELF, FILENAME_CHECKOUT_SUCCESS) !== false
        && !in_array('FB-'.$last_order, $_SESSION['tracking']['order'])
        )
    {
      // include needed functions
      require_once (DIR_FS_INC.'get_order_total.inc.php');

      $_SESSION['tracking']['order'][] = 'FB-'.$last_order;

      $orders_query = xtc_db_query("SELECT currency
                               FROM " . TABLE_ORDERS . "
                              WHERE orders_id = '" . $last_order . "'");
      $orders = xtc_db_fetch_array($orders_query);
      $total = get_order_total($last_order);
    
      $items_array = array();
      $item_query = xtc_db_query("SELECT products_id,
                                         products_model,
                                         products_quantity
                                    FROM " . TABLE_ORDERS_PRODUCTS . "
                                   WHERE orders_id = '" . (int)$last_order . "'
                                GROUP BY products_id");
      while ($item = xtc_db_fetch_array($item_query)) {
        $items_array[] = '{id: "'.$item['products_model'].'", quantity: '.$item['products_quantity'].'}';
      }

      $trackingCode .= '
      fbq(\'track\', \'Purchase\', {
        content: ['.implode(',', $items_array).'], 
        content_type: "product",
        currency: "'.$orders['currency'].'", 
        value: '.$total.'
      });
    ';

      $endCode .= '<img height="1" width="1" style="display:none" src="https://www.facebook.com/tr?id='.MODULE_FACEBOOK_PIXEL_ID.'&amp;ev=Purchase&amp;cd[value]='.number_format($total, 2, '.', '').'&amp;cd[currency]='.$orders['currency'].'&amp;noscript=1"/>
    ';
    }
    
    $endCode .= '
  </noscript>
  ';
    
    $output = $beginCode . $trackingCode . $endCode;  
    
    if (COMPRESS_JAVASCRIPT == 'true') {
      require_once(DIR_FS_EXTERNAL.'compactor/compactor.php');
      $compactor = new Compactor(array('strip_php_comments' => false, 'compress_css' => false, 'compress_scripts' => true));
      $output = $compactor->squeeze($output);
    }
    
    echo $output.PHP_EOL;
  }
