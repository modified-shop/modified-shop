<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  if (defined('MODULE_GOOGLE_ANALYTICS_STATUS')
      && MODULE_GOOGLE_ANALYTICS_STATUS == 'true'
      && defined('MODULE_GOOGLE_ANALYTICS_TAG_ID')
      && MODULE_GOOGLE_ANALYTICS_TAG_ID != ''
      && ((defined('MODULE_GOOGLE_ANALYTICS_COUNT_ADMIN') && MODULE_GOOGLE_ANALYTICS_COUNT_ADMIN == 'true' && $_SESSION['customers_status']['customers_status_id'] == '0')
          || $_SESSION['customers_status']['customers_status_id'] != '0'
          )
      )
  {
    $beginCode = '<script async src="https://www.googletagmanager.com/gtag/js?id='.MODULE_GOOGLE_ANALYTICS_TAG_ID.'"></script>
<script>';

    if (defined('MODULE_COOKIE_CONSENT_STATUS') && MODULE_COOKIE_CONSENT_STATUS == 'true' && (in_array(3, $_SESSION['tracking']['allowed']) || defined('COOKIE_CONSENT_NO_TRACKING'))) {
      $beginCode = '<script async data-type="text/javascript" data-src="https://www.googletagmanager.com/gtag/js?id='.MODULE_GOOGLE_ANALYTICS_TAG_ID.'" type="as-oil" data-purposes="3" data-managed="as-oil"></script>
<script async data-type="text/javascript" type="as-oil" data-purposes="3" data-managed="as-oil">';
    }

    $beginCode .= "
  window['ga-disable-".MODULE_GOOGLE_ANALYTICS_TAG_ID."'] = ".(((MODULE_GOOGLE_ANALYTICS_COUNT_ADMIN == 'true' && $_SESSION['customers_status']['customers_status_id'] == '0') || $_SESSION['customers_status']['customers_status_id'] != '0') ? 'false' : 'true').";
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', '".MODULE_GOOGLE_ANALYTICS_TAG_ID."', {
    anonymize_ip: true,
    link_attribution: ".((MODULE_GOOGLE_ANALYTICS_LINKID == 'true') ? 'true' : 'false').",
    allow_google_signals: ".((MODULE_GOOGLE_ANALYTICS_DISPLAY == 'true') ? 'true' : 'false')."
  });
";
    if (MODULE_GOOGLE_ANALYTICS_ADS_ID != '') {
      $beginCode .= "
  gtag('config', '".MODULE_GOOGLE_ANALYTICS_ADS_ID."', {
    anonymize_ip: true,
    allow_enhanced_conversions: true
  });
";
    }
    
    $endCode = "
</script>
";

    $addCode = null;
    if (isset($site_error)) {
      $addCode = getErrorGoogleAnalytics($site_error);
    } else {
      switch (basename($PHP_SELF)) {
        case FILENAME_CHECKOUT_SHIPPING:
          $addCode = getCheckoutGoogleAnalytics();
          break;
        case FILENAME_CHECKOUT_PAYMENT:
          $addCode = getShippingGoogleAnalytics();
          break;
        case FILENAME_CHECKOUT_CONFIRMATION:
          $addCode = getPaymentGoogleAnalytics();
          break;
        case FILENAME_CHECKOUT_SUCCESS:
          if (MODULE_GOOGLE_ANALYTICS_ECOMMERCE == 'true'
              && !in_array('GTAG-'.$last_order, $_SESSION['tracking']['order'])
              )
          {
            $_SESSION['tracking']['order'][] = 'GTAG-'.$last_order;
            $addCode = getOrderDetailsGoogleAnalytics();
            
            if (MODULE_GOOGLE_ANALYTICS_ADS_CONVERSION_ID != '') {
              $addCode .= getConversionGoogleAnalytics();
            }
          }
          break;
        case FILENAME_SHOPPING_CART:
          $addCode = getCartDetailsGoogleAnalytics();
          break;
        case FILENAME_PRODUCT_INFO:
          $addCode = getProductDetailsGoogleAnalytics();
          break;
        case FILENAME_DEFAULT:
          if ((isset($_GET['cPath']) && $_GET['cPath'] != '')
              || (isset($_GET['manufacturers_id']) && $_GET['manufacturers_id'] != '')
              )
          {
            $addCode = getListingDetailsGoogleAnalytics();
          } else {
            $addCode = getStartpageGoogleAnalytics();
          }
          break;
        case FILENAME_SPECIALS:
        case FILENAME_PRODUCTS_NEW:
        case FILENAME_ADVANCED_SEARCH_RESULT:
          $addCode = getListingDetailsGoogleAnalytics();
          break;
      }
    }

    echo $beginCode . $addCode . $endCode;
  }

  /*
   * FUNCTIONS
   */
  function getStartpageGoogleAnalytics() {
    $addCode = "
  gtag('event', 'view_home');";

    return $addCode;
  }


  function getErrorGoogleAnalytics($site_error) {
    $addCode = "
  gtag('event', 'view_error', {
    error: '".$site_error."'
  });";

    return $addCode;
  }


  function getProductDetailsGoogleAnalytics() {
    global $product, $xtPrice;

    $addCode = "
  gtag('event', 'view_item', {
    currency: '".$_SESSION['currency']."',
    value: ".numberFormatGoogleAnalytics($xtPrice->xtcGetPrice($product->data['products_id'], false, 1, $product->data['products_tax_class_id'])).",
    items: [".getItemDetailsGoogleAnalytics($product->data)."
    ]
  });";

    return $addCode;
  }


  function getListingDetailsGoogleAnalytics() {
    $split_obj = array('listing_split', 'specials_split', 'products_new_split');
    foreach ($split_obj as $object) {
      global ${$object};
      if (isset(${$object}) && is_object(${$object})) {
        break;
      }
    }

    if (isset(${$object}) && is_object(${$object})) {
      $products_array = array();
      $listing_query = xtDBquery(${$object}->sql_query);
      while ($listing = xtc_db_fetch_array($listing_query, true)) {
        $products_array[] = getItemDetailsGoogleAnalytics($listing);
      }

      $addCode = "
  gtag('event', 'view_item_list', {
    items: [".implode(',', $products_array)."
    ]
  });";

    } else {

      $addCode = "
  gtag('event', 'view_category');";

    }

    return $addCode;
  }


  function getCartDetailsGoogleAnalytics() {
    if ($_SESSION['cart']->count_contents() > 0) {
      $products_array = array();
      $products = $_SESSION['cart']->get_products();
      for ($i = 0, $n = sizeof($products); $i < $n; $i ++) {
        $products_array[] = getItemDetailsGoogleAnalytics($products[$i]);
      }

      $addCode = "
  gtag('event', 'view_cart', {
    currency: '".$_SESSION['currency']."',
    value: ".numberFormatGoogleAnalytics($_SESSION['cart']->show_total()).",
    items: [".implode(',', $products_array)."
    ]
  });";

      return $addCode;
    }
  }


  function getCheckoutGoogleAnalytics() {
    global $order;

    if (isset($order->products)
        && is_array($order->products)
        && count($order->products) > 0
        )
    {
      $products_array = array();
      $products = $order->products;
      for ($i = 0, $n = sizeof($products); $i < $n; $i ++) {
        $products_array[] = getItemDetailsGoogleAnalytics($products[$i]);
      }

      $addCode = "
  gtag('event', 'begin_checkout', {
    currency: '".$_SESSION['currency']."',
    value: ".numberFormatGoogleAnalytics($_SESSION['cart']->show_total()).",
    items: [".implode(',', $products_array)."
    ]
  });";

      return $addCode;
    }
  }


  function getShippingGoogleAnalytics() {
    global $order;

    if (isset($order->products)
        && is_array($order->products)
        && count($order->products) > 0
        )
    {
      $products_array = array();
      $products = $order->products;
      for ($i = 0, $n = sizeof($products); $i < $n; $i ++) {
        $products_array[] = getItemDetailsGoogleAnalytics($products[$i]);
      }

      $addCode = "
  gtag('event', 'add_shipping_info', {
    currency: '".$order->info['currency']."',
    value: ".numberFormatGoogleAnalytics($order->info['total']).",
    shipping_tier: '".$order->info['shipping_class']."',
    items: [".implode(',', $products_array)."
    ]
  });";

      return $addCode;
    }
  }


  function getPaymentGoogleAnalytics() {
    global $order;

    if (isset($order->products)
        && is_array($order->products)
        && count($order->products) > 0
        )
    {
      $products_array = array();
      $products = $order->products;
      for ($i = 0, $n = sizeof($products); $i < $n; $i ++) {
        $products_array[] = getItemDetailsGoogleAnalytics($products[$i]);
      }

      $addCode = "
  gtag('event', 'add_payment_info', {
    currency: '".$order->info['currency']."',
    value: ".numberFormatGoogleAnalytics($order->info['total']).",
    payment_type: '".$order->info['payment_class']."',
    items: [".implode(',', $products_array)."
    ]
  });";

      return $addCode;
    }
  }


  function getOrderDetailsGoogleAnalytics() {
    global $last_order;

    require_once (DIR_WS_CLASSES . 'order.php');
    $order = new order($last_order);

    if (isset($order->products)
        && is_array($order->products)
        && count($order->products) > 0
        )
    {
      $addItem = array();
      $products = $order->products;
      for ($i = 0, $n = sizeof($products); $i < $n; $i ++) {
        $addItem[] = getItemDetailsGoogleAnalytics($products[$i]);
      }

      $addCode = "
  gtag('event', 'purchase', {
    transaction_id: '".$order->info['orders_id']."',
    currency: '".$order->info['currency']."',
    value: ".numberFormatGoogleAnalytics($order->info['pp_total']).",
    tax: ".numberFormatGoogleAnalytics($order->info['pp_tax']).",
    shipping: ".numberFormatGoogleAnalytics($order->info['pp_shipping']).",
    items: [".implode(',', $addItem)."
    ]
  });";

      return $addCode;
    }
  }


  function getConversionGoogleAnalytics() {
    global $last_order;

    require_once (DIR_WS_CLASSES . 'order.php');
    $order = new order($last_order);

    $addCode = "
  gtag('event', 'conversion', {
    send_to: '".MODULE_GOOGLE_ANALYTICS_ADS_CONVERSION_ID."',
    transaction_id: '".$order->info['orders_id']."',
    currency: '".$order->info['currency']."',
    value: ".numberFormatGoogleAnalytics($order->info['pp_total'])."
  });";
  
    return $addCode;
  }


  function getItemDetailsGoogleAnalytics($data) {
    global $xtPrice, $PHP_SELF;

    $item = array();
    foreach ($data as $k => $v) {
      $k = str_replace('products_', '', $k);
      $item[$k] = $v;
    }

    if (strpos($PHP_SELF, 'checkout') === false
        && strpos($PHP_SELF, 'shopping_cart') === false
        )
    {
      $item['price'] = $xtPrice->xtcGetPrice($item['id'], false, 1, $item['tax_class_id']);
    }

    $item_data = "
      {
        item_id: '".addslashes($item['model'])."',
        item_name: '".addslashes($item['name'])."',
        price: ".numberFormatGoogleAnalytics($item['price']).",
        quantity: ".((isset($item['quantity']) && $item['quantity'] > 0) ? $item['quantity'] : 1)."
      }";

    return $item_data;
  }


  function numberFormatGoogleAnalytics($price) {
    return number_format($price, 2, '.', '');
  }
