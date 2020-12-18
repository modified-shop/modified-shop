<?php
  /* --------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2014 [www.modified-shop.org]
   --------------------------------------------------------------
   Released under the GNU General Public License
   --------------------------------------------------------------*/

    
  if (TRACKING_PIWIK_ACTIVE == 'true'
      && ((TRACKING_COUNT_ADMIN_ACTIVE == 'true' && $_SESSION['customers_status']['customers_status_id'] == '0')
          || $_SESSION['customers_status']['customers_status_id'] != '0'
          )
      )
  {  
    // include needed functions
    require_once (DIR_FS_INC.'get_order_total.inc.php');

    // include needed classes
    require_once (DIR_WS_CLASSES.'language.php');

    $url = TRACKING_PIWIK_LOCAL_PATH;
    $id = TRACKING_PIWIK_ID;
    $goal = TRACKING_PIWIK_GOAL;

    if (!$url || !$id) {
      return false;
    }

    $piwik_lang = new language(xtc_input_validation(DEFAULT_LANGUAGE, 'lang'));
    $piwik_language_id = $piwik_lang->language['id'];

    $url = str_replace(array('http://', 'https://'), '', $url);
    $url = trim($url, '/');
    $beginCode = '<script>';
    if (defined('MODULE_COOKIE_CONSENT_STATUS') && strtolower(MODULE_COOKIE_CONSENT_STATUS) == 'true' && (in_array(7, $_SESSION['tracking']['allowed']) || defined('COOKIE_CONSENT_NO_TRACKING'))) {
      $beginCode = '<script async data-type="text/javascript" type="as-oil" data-purposes="7" data-managed="as-oil">';
    }
    $beginCode .= '
        var _paq = _paq || [];
          var u="//'.$url.'/";
          _paq.push([\'setSiteId\', '.$id.']);
          _paq.push([\'setTrackerUrl\', u+\'matomo.php\']);
          _paq.push([\'trackPageView\']);
          _paq.push([\'enableLinkTracking\']);'."\n";

    $endCode = '
          (function(){
            var d=document,
            g=d.createElement(\'script\'),
            s=d.getElementsByTagName(\'script\')[0];
            g.type=\'text/javascript\';
            g.defer=true;
            g.async=true;
            g.src=u+\'matomo.js\';
            s.parentNode.insertBefore(g,s);
          })();
      </script>
    ';
  
    $orderCode = null;
    if (basename($PHP_SELF) == FILENAME_DEFAULT && isset($_GET['cPath']) && $_GET['cPath'] != '') {
      $orderCode .= getMatomoCategoryName();
    }
    if (strpos($PHP_SELF, FILENAME_PRODUCT_INFO) != false && isset($_GET['products_id']) && $_GET['products_id'] != '') {
      $orderCode .= getProductsName();
    }
    if (strpos($PHP_SELF, FILENAME_SHOPPING_CART) != false) {
      $orderCode .= getMatomoCartDetails();
    }
    if (strpos($PHP_SELF, FILENAME_CHECKOUT_SUCCESS) != false && !in_array('PW-'.$last_order, $_SESSION['tracking']['order'])) {
      $_SESSION['tracking']['order'][] = 'PW-'.$last_order;
      $orderCode .= getMatomoOrder();
      if ($goal > 0) {
        $orderCode .= getMatomoOrderDetails($goal);
      }
    }
  
    echo $beginCode . $orderCode . $endCode;
  }


  /* get category name */
  function getMatomoCategoryName() {
    global $piwik_language_id;

    $cPath_array = explode('_', $_GET['cPath']);
  
    $categories_id = array_pop($cPath_array);
    $categories_name = get_categories_name($categories_id, $piwik_language_id);

    return "        "."_paq.push(['setEcommerceView', productSku = false, productName = false, category = '".encode_htmlspecialchars($categories_name)."']);\n";
  }

  /* get products name */
  function getProductsName() {
    global $piwik_language_id;

    $products_id = xtc_get_prid($_GET['products_id']);
    $products_name = xtc_get_products_name($products_id, $piwik_language_id);

    $cPath = xtc_get_product_path($products_id);
    $cPath_array = explode('_', $cPath);
  
    $categories_id = array_pop($cPath_array);
    $categories_name = get_categories_name($categories_id, $piwik_language_id);
  
    return "        "."_paq.push(['setEcommerceView', '".$products_id."', '".encode_htmlspecialchars($products_name)."', '".encode_htmlspecialchars($categories_name)."']);\n";
  }

  /* get shopping cart contents */
  function getMatomoCartDetails() {
    global $piwik_language_id;
  
    $products = $_SESSION['cart']->get_products();
    if ($_SESSION['cart']->count_contents() > 0) {
      $return_string = '';
      for ($i=0, $n=sizeof($products); $i<$n; $i++) {
        $cPath = xtc_get_product_path($products[$i]['id']);
        $cPath_array = explode('_', $cPath);
      
        $categories_id = array_pop($cPath_array);
        $categories_name = get_categories_name($categories_id, $piwik_language_id);

        $return_string .= "        "."_paq.push(['addEcommerceItem', '".(int)$products[$i]['id']."', '".encode_htmlspecialchars($products[$i]['name'])."', '".encode_htmlspecialchars($categories_name)."', '".formatMatomoPrice($products[$i]['final_price'])."', '". (int)$products[$i]['quantity']."']);\n";
      }
      $return_string .= "        "."_paq.push(['trackEcommerceCartUpdate', '".formatMatomoPrice($_SESSION['cart']->show_total())."']);\n";
    }
  
    return $return_string;
  }

  /* get order */
  function getMatomoOrder () {
    global $piwik_language_id, $last_order;
  
    $orders_query = xtc_db_query("SELECT orders_id
                                    FROM " . TABLE_ORDERS . "
                                   WHERE orders_id = '" . (int)$last_order . "'
                                ORDER BY date_purchased DESC 
                                   LIMIT 1"
                                );
    if (xtc_db_num_rows($orders_query) == 1) {
      $order = xtc_db_fetch_array($orders_query);
      $total = array();
      $return_string = '';
      $order_total_query = xtc_db_query("SELECT value,
                                                class
                                           FROM " . TABLE_ORDERS_TOTAL . "
                                          WHERE orders_id = '" . (int)$order['orders_id'] . "'"
                                       );
      while ($order_total = xtc_db_fetch_array($order_total_query)) {
        $total[$order_total['class']] = $order_total['value'];
      }
      $order_products_query = xtc_db_query("SELECT op.products_id,
                                                   pd.products_name,
                                                   op.final_price,
                                                   op.products_quantity
                                              FROM " . TABLE_ORDERS_PRODUCTS . " op
                                              JOIN " . TABLE_PRODUCTS_DESCRIPTION . " pd
                                                   ON op.products_id = pd.products_id
                                                      AND pd.language_id = '".$piwik_language_id."'
                                             WHERE op.orders_id = '" . (int)$order['orders_id'] . "'"
                                          );
      while ($order_products = xtc_db_fetch_array($order_products_query)) {
        $cPath = xtc_get_product_path($order_products['products_id']);
        $cPath_array = explode('_', $cPath);
      
        $categories_id = array_pop($cPath_array);
        $categories_name = get_categories_name($categories_id, $piwik_language_id);

        $return_string .= "        "."_paq.push(['addEcommerceItem', '".(int)$order_products['products_id']."', '".encode_htmlspecialchars($order_products['products_name'])."', '".encode_htmlspecialchars($categories_name)."', '".formatMatomoPrice($order_products['final_price'])."', '".(int)$order_products['products_quantity']."']);\n";
      }
      $return_string .= "        "."_paq.push(['trackEcommerceOrder', '".(int)$order['orders_id']."', '".(isset($total['ot_total']) ? formatMatomoPrice($total['ot_total']) : 0)."', '".(isset($total['ot_subtotal']) ? formatMatomoPrice($total['ot_subtotal']) : 0)."', '".(isset($total['ot_tax']) ? formatMatomoPrice($total['ot_tax']) : 0)."', '".(isset($total['ot_shipping']) ? formatMatomoPrice($total['ot_shipping']) : 0)."', '".(isset($total['ot_payment']) ? formatMatomoPrice($total['ot_payment']) : 0)."']);\n";
    }
    return $return_string;
  }

  /* get order details */
  function getMatomoOrderDetails($goal) {
    global $last_order;

    $total = get_order_total($last_order);

    return "        "."_paq.push(['trackGoal', '" . $goal . "', '" . $total . "' ]);\n";
  }

  /* format price */
  function formatMatomoPrice($price) {      
    return number_format($price, 2, '.', '');
  }

  /* get categories_name */
  function get_categories_name($categories_id, $language_id) {
    $category_query = xtc_db_query("SELECT categories_name
                                      FROM " . TABLE_CATEGORIES_DESCRIPTION . "
                                     WHERE categories_id = '".(int)$categories_id."'
                                       AND language_id = '".(int)$language_id."'");
    $category = xtc_db_fetch_array($category_query);
  
    return $category['categories_name'];
  }
?>