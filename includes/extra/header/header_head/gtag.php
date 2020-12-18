<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  if (TRACKING_GOOGLEANALYTICS_ACTIVE == 'true'
      && TRACKING_GOOGLEANALYTICS_GTAG == 'true'
      && ((TRACKING_COUNT_ADMIN_ACTIVE == 'true' && $_SESSION['customers_status']['customers_status_id'] == '0')
          || $_SESSION['customers_status']['customers_status_id'] != '0'
          )
      )
  {
    $beginCode = '<script async src="https://www.googletagmanager.com/gtag/js?id='.TRACKING_GOOGLEANALYTICS_ID.'"></script>
<script>';

    if (defined('MODULE_COOKIE_CONSENT_STATUS') && MODULE_COOKIE_CONSENT_STATUS == 'true' && (in_array(3, $_SESSION['tracking']['allowed']) || defined('COOKIE_CONSENT_NO_TRACKING'))) {
      $beginCode = '<script async data-type="text/javascript" data-src="https://www.googletagmanager.com/gtag/js?id='.TRACKING_GOOGLEANALYTICS_ID.'" type="as-oil" data-purposes="3" data-managed="as-oil"></script>
<script async data-type="text/javascript" type="as-oil" data-purposes="3" data-managed="as-oil">';
    }
    
    $beginCode .= "
  window['ga-disable-".TRACKING_GOOGLEANALYTICS_ID."'] = ".(((TRACKING_COUNT_ADMIN_ACTIVE == 'true' && $_SESSION['customers_status']['customers_status_id'] == '0') || $_SESSION['customers_status']['customers_status_id'] != '0') ? 'false' : 'true').";
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', '".TRACKING_GOOGLEANALYTICS_ID."', {
    'anonymize_ip': true,
    'link_attribution': ".((TRACKING_GOOGLE_LINKID == 'true') ? 'true' : 'false').",
    'allow_google_signals': ".((TRACKING_GOOGLE_DISPLAY == 'true') ? 'true' : 'false')."
  });
";
  
    $endCode = "
</script>
";

    $orderCode = null;
    if (strpos($PHP_SELF, FILENAME_CHECKOUT_SUCCESS) !== false
        && TRACKING_GOOGLE_ECOMMERCE == 'true'
        && !in_array('GA-'.$last_order, $_SESSION['tracking']['order'])
        )
    {
      $_SESSION['tracking']['order'][] = 'GA-'.$last_order;
  
      $orderCode = getOrderDetailsGtag();
    }

    echo $beginCode . $orderCode . $endCode;  
  } 
  
  /*
   * FUNCTIONS
   */
  function getOrderDetailsGtag() {
    global $last_order;
  
    require_once (DIR_FS_INC.'get_order_total.inc.php');
    $total = get_order_total($last_order);
    
    $shipping = 0;
    $ot_shipping_query = xtc_db_query("SELECT value
                                         FROM " . TABLE_ORDERS_TOTAL . "
                                        WHERE orders_id = '" . (int)$last_order . "' 
                                          AND class='ot_shipping'");
    if (xtc_db_num_rows($ot_shipping_query) > 0) {
      $ot_shipping = xtc_db_fetch_array($ot_shipping_query);
      $shipping = $ot_shipping['value'];
    }
    
    $tax = 0;
    $ot_tax_query = xtc_db_query("SELECT value
                                    FROM " . TABLE_ORDERS_TOTAL . "
                                   WHERE orders_id = '" . (int)$last_order . "' 
                                     AND class='ot_tax'");
    if (xtc_db_num_rows($ot_shipping_query) > 0) {
      while ($ot_tax = xtc_db_fetch_array($ot_tax_query)) {
        $tax += $ot_tax['value'];
      }
    }
    
    $currency_query = xtc_db_query("SELECT currency
                                      FROM " . TABLE_ORDERS . "
                                     WHERE orders_id = '" . (int)$last_order . "'");
    $currency = xtc_db_fetch_array($currency_query);

    $item_query = xtc_db_query("SELECT cd.categories_name,
                                       op.products_id,
                                       op.orders_products_id,
                                       op.products_model,
                                       op.products_name,
                                       op.products_price,
                                       op.products_quantity
                                  FROM " . TABLE_ORDERS_PRODUCTS . " op
                                  JOIN " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c
                                       ON op.products_id = p2c.products_id
                                  JOIN " . TABLE_CATEGORIES_DESCRIPTION . " cd
                                       ON p2c.categories_id = cd.categories_id
                                          AND cd.language_id = '" . (int)$_SESSION['languages_id'] . "'
                                 WHERE op.orders_id='" . (int)$last_order . "'
                              GROUP BY op.products_id");
    
    $i = 1;
    $addItem = array();
    while ($item = xtc_db_fetch_array($item_query)) {
      $addItem[] = "
      {
        'id': '".$item['products_id']."',
        'name': '".addslashes($item['products_name'])."',
        'category': '".addslashes($item['categories_name'])."',
        'list_position': ".$i.",
        'quantity': ".$item['products_quantity'].",
        'price': '".number_format($item['products_price'], 2)."'
      }";
      $i ++;
    }


    $orderCode = "
  gtag('event', 'purchase', {
    'transaction_id': '".$last_order."',
    'affiliation': '".addslashes(STORE_NAME)."',
    'currency': '".$currency['currency']."',
    'value': ".number_format($total, 2).",
    'tax': ".number_format($tax, 2).",
    'shipping': ".number_format($shipping, 2).",
    'items': [".implode(',', $addItem)."
    ]
  });";

    return $orderCode;
  }
?>