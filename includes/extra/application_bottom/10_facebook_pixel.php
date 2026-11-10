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
    $fb_consent_given = true;
    if (defined('MODULE_COOKIE_CONSENT_STATUS') && strtolower(MODULE_COOKIE_CONSENT_STATUS) == 'true' && (in_array(6, $_SESSION['tracking']['allowed']) || defined('COOKIE_CONSENT_NO_TRACKING'))) {
      $beginCode = '<script async data-type="text/javascript" type="as-oil" data-purposes="6" data-managed="as-oil">';
      $fb_consent_given = (!defined('COOKIE_CONSENT_NO_TRACKING')
                           && isset($_SESSION['tracking']['allow'][6])
                           && $_SESSION['tracking']['allow'][6] === true
                           );
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
  ';

    $noscriptCode = '
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


    // express payments skip shipping and payment, so the first checkout page of a cart counts
    if (in_array(basename($PHP_SELF), array(FILENAME_CHECKOUT_SHIPPING, FILENAME_CHECKOUT_PAYMENT, FILENAME_CHECKOUT_CONFIRMATION))
        && isset($_SESSION['cart'])
        && is_object($_SESSION['cart'])
        && $_SESSION['cart']->count_contents() > 0
        )
    {
      $fb_contents = $fb_cart = array();
      foreach ($_SESSION['cart']->get_products() as $fb_product) {
        $fb_contents[] = array('id' => (string)$fb_product['model'], 'quantity' => (int)$fb_product['quantity']);
        $fb_cart[$fb_product['id']] = (int)$fb_product['quantity'];
      }
      ksort($fb_cart, SORT_STRING);
      $fb_cart = md5(serialize($fb_cart));

      // every submit of the cart form creates a new cart ID, so products, variants and quantities identify the checkout
      if (!isset($_SESSION['tracking']['fb_checkout']['cart'])
          || $_SESSION['tracking']['fb_checkout']['cart'] != $fb_cart
          )
      {
        $_SESSION['tracking']['fb_checkout'] = array(
          'cart' => $fb_cart,
          'token' => bin2hex(random_bytes(8)),
          'sent' => false,
        );
      }

      if ($_SESSION['tracking']['fb_checkout']['sent'] !== true) {
        if ($fb_consent_given === true) {
          $_SESSION['tracking']['fb_checkout']['sent'] = true;
        }

        $trackingCode .= '
      (function () {
        try {
          if (localStorage.getItem("MODfbCheckout") == "'.$_SESSION['tracking']['fb_checkout']['token'].'") return;
          localStorage.setItem("MODfbCheckout", "'.$_SESSION['tracking']['fb_checkout']['token'].'");
        } catch (e) {}
        fbq(\'track\', \'InitiateCheckout\', {
          contents: '.json_encode($fb_contents, JSON_HEX_TAG | JSON_HEX_AMP | JSON_INVALID_UTF8_SUBSTITUTE).',
          content_type: "product",
          currency: "'.$_SESSION['currency'].'",
          num_items: '.(int)$_SESSION['cart']->count_contents().',
          value: '.round($_SESSION['cart']->show_total(), 2).'
        });
      })();
    ';
      }
    }


    // a checkout after the order gets a new token, even with the same products
    if (strpos($PHP_SELF, FILENAME_CHECKOUT_SUCCESS) !== false) {
      unset($_SESSION['tracking']['fb_checkout']);
    }


    if (strpos($PHP_SELF, FILENAME_CHECKOUT_SUCCESS) !== false
        && !in_array('FB-'.$last_order, $_SESSION['tracking']['order'])
        )
    {
      // include needed functions
      require_once (DIR_FS_INC.'get_order_total.inc.php');

      // OIL may still run a blocked script on this page, so only a known consent marks the order as sent
      if ($fb_consent_given === true) {
        $_SESSION['tracking']['order'][] = 'FB-'.$last_order;
      }

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

      // the browser remembers the last sent orders, Meta does not deduplicate a second Purchase
      $trackingCode .= '
      (function () {
        var order = "'.(int)$last_order.'", sent = [];
        try {
          sent = JSON.parse(localStorage.getItem("MODfbPurchase")) || [];
        } catch (e) {}
        if (!Array.isArray(sent)) sent = [];
        if (sent.indexOf(order) != -1) return;
        sent.push(order);
        try {
          localStorage.setItem("MODfbPurchase", JSON.stringify(sent.slice(-20)));
        } catch (e) {}
        fbq(\'track\', \'Purchase\', {
          contents: ['.implode(',', $items_array).'],
          content_type: "product",
          currency: "'.$orders['currency'].'",
          value: '.$total.'
        });
      })();
    ';

      $noscriptCode .= '<img height="1" width="1" style="display:none" src="https://www.facebook.com/tr?id='.MODULE_FACEBOOK_PIXEL_ID.'&amp;ev=Purchase&amp;cd[value]='.number_format($total, 2, '.', '').'&amp;cd[currency]='.$orders['currency'].'&amp;noscript=1"/>
    ';
    }

    // the consent tool only manages the script, not the noscript images
    if ($fb_consent_given === true) {
      $endCode .= $noscriptCode.'
  </noscript>
  ';
    }
    
    $output = $beginCode . $trackingCode . $endCode;  
    
    if (COMPRESS_JAVASCRIPT == 'true') {
      require_once(DIR_FS_EXTERNAL.'compactor/compactor.php');
      $compactor = new Compactor(array('compress_css' => false, 'compress_scripts' => true));
      $output = $compactor->squeeze($output);
    }
    
    echo $output.PHP_EOL;
  }
