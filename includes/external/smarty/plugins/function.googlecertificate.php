<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   index.html {googlecertificate subaccount=GOOGLE_SHOPPING_ID account=GOOGLE_TRUSTED_ID}
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

require_once (DIR_FS_INC.'get_order_total.inc.php');

function smarty_function_googlecertificate($params, &$smarty) {
  global $PHP_SELF;
  
  if (!isset($params['account']) && !isset($params['subaccount'])) {
    return false;
  }
  $account = strtoupper($params['account']);
  $subaccount = strtoupper($params['subaccount']);

  /**
    * gts.push(["id", "GOOGLE_TRUSTED_ID"]);
    * gts.push(["badge_position", "BOTTOM_RIGHT"]);
    * gts.push(["locale", "PAGE_LANGUAGE"]);
    * gts.push(["google_base_offer_id", "ITEM_GOOGLE_SHOPPING_ID"]);
    * gts.push(["google_base_subaccount_id", "ITEM_GOOGLE_SHOPPING_ACCOUNT_ID"]);
    * gts.push(["google_base_country", "ITEM_GOOGLE_SHOPPING_COUNTRY"]);
    * gts.push(["google_base_language", "ITEM_GOOGLE_SHOPPING_LANGUAGE"]);
    */
  $beginCode = '<script type="text/javascript">
    var gts = gts || [];

    gts.push(["id", "'.$account.'"]);
    gts.push(["badge_position", "BOTTOM_RIGHT"]);
    gts.push(["locale", "'.buildPageLanguageCertificate().'"]);
    ';
    
  $detailsCode = null;
  if ((strpos($PHP_SELF, FILENAME_PRODUCT_INFO) !== false)) {
    $detailsCode = 'gts.push(["google_base_offer_id", "'.(int)$_GET['products_id'].'"]);';
  }
        
  $endCode = '
    gts.push(["google_base_subaccount_id", "'.$subaccount.'"]);
    gts.push(["google_base_country", "'.strtoupper($_SESSION['language_code']).'"]);
    gts.push(["google_base_language", "'.strtolower($_SESSION['language_code']).'"]);

    (function() {
      var gts = document.createElement("script");
      gts.type = "text/javascript";
      gts.async = true;
      gts.src = "https://www.googlecommerce.com/trustedstores/api/js";
      var s = document.getElementsByTagName("script")[0];
      s.parentNode.insertBefore(gts, s);
    })();
</script>'.PHP_EOL;

  $orderCode = null;
  if ((strpos($PHP_SELF, FILENAME_CHECKOUT_SUCCESS) !== false)) {
    $orderCode = getOrderDetailsCertificate($subaccount);
  }

  return $beginCode . $detailsCode . $endCode . $orderCode;
}

/**
 * Get the language
 *
 * @return string language in ISO 639-1
 */
function buildPageLanguageCertificate() {
  $language_query = xtDBquery("SELECT countries_iso_code_2 
                                 FROM ".TABLE_COUNTRIES." 
                                WHERE countries_id = '".((isset($_SESSION['customer_country_id'])) ? $_SESSION['customer_country_id'] : STORE_COUNTRY)."'");
  $language = xtc_db_fetch_array($language_query, true);
  
  return strtolower($_SESSION['language_code']) . '_' . strtoupper($language['countries_iso_code_2']);
}

/**
 * Get the details of the order
 *
 * @global <type> $last_order
 * @return string Code for the eCommerce tracking
 */
function getOrderDetailsCertificate($subaccount) {
  global $last_order, $request_type; // from checkout_success.php

  $total = get_order_total($last_order);

  $query = xtc_db_query("-- function.googlecertificate.php
    SELECT value
      FROM " . TABLE_ORDERS_TOTAL . "
     WHERE orders_id = '" . $last_order . "' 
       AND class='ot_shipping'");
  $orders_total_shipping = xtc_db_fetch_array($query);

  $query = xtc_db_query("-- function.googlecertificate.php
    SELECT value
      FROM " . TABLE_ORDERS_TOTAL . "
     WHERE orders_id = '" . $last_order . "' 
       AND class='ot_tax'");
  $orders_total_tax = xtc_db_fetch_array($query);
  
  $discount = 0;
  $query = xtc_db_query("-- function.googlecertificate.php
    SELECT value
      FROM " . TABLE_ORDERS_TOTAL . "
     WHERE orders_id = '" . $last_order . "' 
       AND class IN ('ot_discount', 'ot_coupon', 'ot_payment')");
  while ($orders_total_discount = xtc_db_fetch_array($query)) {
    $discount += $orders_total_discount['value'];
  }

  $query = xtc_db_query("-- function.googlecertificate.php
    SELECT delivery_country_iso_code_2, 
           currency, 
           customers_email_address
      FROM " . TABLE_ORDERS . "
     WHERE orders_id = '" . $last_order . "'");
  $orders = xtc_db_fetch_array($query);

  $time = strtotime("next weekday");
  $opendiv = '<div id="gts-order" style="display:none;" translate="no">'.PHP_EOL;
  $closediv = '</div>'.PHP_EOL;

  /**
   * <span id="gts-o-id">MERCHANT_ORDER_ID</span>
   * <span id="gts-o-domain">MERCHANT_ORDER_DOMAIN</span>
   * <span id="gts-o-email">CUSTOMER_EMAIL</span>
   * <span id="gts-o-country">CUSTOMER_COUNTRY</span>
   * <span id="gts-o-currency">CURRENCY</span>
   * <span id="gts-o-total">ORDER_TOTAL</span>
   * <span id="gts-o-discounts">ORDER_DISCOUNTS</span>
   * <span id="gts-o-shipping-total">ORDER_SHIPPING</span>
   * <span id="gts-o-tax-total">ORDER_TAX</span>
   * <span id="gts-o-est-ship-date">ORDER_EST_SHIP_DATE</span>
   * <span id="gts-o-est-delivery-date">ORDER_EST_DELIVERY_DATE</span>
   * <span id="gts-o-has-preorder">HAS_BACKORDER_PREORDER</span>
   * <span id="gts-o-has-digital">HAS_DIGITAL_GOODS</span>
   */
  $addGTS = '<span id="gts-o-id">'.$last_order.'</span>
    <span id="gts-o-domain">'.$_SERVER['HTTP_HOST'].'</span>
    <span id="gts-o-email">'.$orders['customers_email_address'].'</span>
    <span id="gts-o-country">'.strtoupper($orders['delivery_country_iso_code_2']).'</span>
    <span id="gts-o-currency">'.$orders['currency'].'</span>
    <span id="gts-o-total">'.number_format($total, 2).'</span>
    <span id="gts-o-discounts">'.number_format($discount, 2).'</span>
    <span id="gts-o-shipping-total">'.number_format($orders_total_shipping['value'], 2).'</span>
    <span id="gts-o-tax-total">'.number_format($orders_total_tax["value"], 2).'</span>
    <span id="gts-o-est-ship-date">'.date('Y-m-d', $time).'</span>
    <span id="gts-o-est-delivery-date">'.date('Y-m-d', strtotime("+3 day", $time)).'</span>
    <span id="gts-o-has-preorder">N</span>
    <span id="gts-o-has-digital">N</span>';

  $query = xtc_db_query("-- function.googlecertificate.php
    SELECT products_id,
           products_name,
           products_price,
           products_quantity
      FROM " . TABLE_ORDERS_PRODUCTS . "
     WHERE orders_id = '" . $last_order . "'
  GROUP BY products_id");

  $addGTSItem = array();
  while ($order = xtc_db_fetch_array($query)) {
    /**
     * <span class="gts-item">
     *  <span class="gts-i-name">ITEM_NAME</span>
     *   <span class="gts-i-price">ITEM_PRICE</span>
     *   <span class="gts-i-quantity">ITEM_QUANTITY</span>
     *   <span class="gts-i-prodsearch-id">ITEM_GOOGLE_SHOPPING_ID</span>
     *   <span class="gts-i-prodsearch-store-id">ITEM_GOOGLE_SHOPPING_ACCOUNT_ID</span>
     *   <span class="gts-i-prodsearch-country">ITEM_GOOGLE_SHOPPING_COUNTRY</span>
     *   <span class="gts-i-prodsearch-language">ITEM_GOOGLE_SHOPPING_LANGUAGE</span>
     * </span>
     */
    $addGTSItem[] = '<span class="gts-item">
      <span class="gts-i-name">'.$order['products_name'].'</span>
      <span class="gts-i-price">'.number_format($order['products_price'], 2).'</span>
      <span class="gts-i-quantity">'.$order['products_quantity'].'</span>
      <span class="gts-i-prodsearch-id">'.$order['products_id'].'</span>
      <span class="gts-i-prodsearch-store-id">'.$subaccount.'</span>
      <span class="gts-i-prodsearch-country">'.strtoupper($_SESSION['language_code']).'</span>
      <span class="gts-i-prodsearch-language">'.strtolower($_SESSION['language_code']).'</span>
    </span>'.PHP_EOL;
  }
  
  return $opendiv . $addGTS . implode('', $addGTSItem) . $closediv;
}