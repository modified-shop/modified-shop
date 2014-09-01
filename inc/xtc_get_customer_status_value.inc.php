<?php
/* -----------------------------------------------------------------------------------------
   $Id: xtc_get_customer_status_value.inc.php 1031 2005-07-15 10:30:28Z gwinger $   

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   -----------------------------------------------------------------------------------------
   based on Third Party contribution:
   Customers Status v3.x  (c) 2002-2003 Copyright Elari elari@free.fr | www.unlockgsm.com/dload-osc/ | CVS : http://cvs.sourceforge.net/cgi-bin/viewcvs.cgi/elari/?sortby=date#dirlist

   Released under the GNU General Public License
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(general.php,v 1.225 2003/05/29); www.oscommerce.com 
   (c) 2003	 nextcommerce (xtc_get_customer_status_value.inc.php,v 1.4 2003/08/13); www.nextcommerce.org

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/
   
// Return all status info values for a customer_id in catalog, need to check session registered customer or will return dafault guest customer status value !
function xtc_get_customer_status_value($customer_id) {

  if (isset($_SESSION['customer_id'])) {
    $customer_status_query = xtc_db_query("select c.customers_status, c.member_flag, cs.customers_status_id, cs.customers_status_name, cs.customers_status_public, cs.customers_status_show_price, cs_customers_status_min_order, cs.customers_status_max_order, cs.customers_status_show_price_tax, cs.customers_status_image, cs.customers_status_discount, cs.customers_status_ot_discount_flag, cs.customers_status_ot_discount, cs.customers_status_graduated_prices, cs.customers_status_cod_permission, cs.customers_status_cc_permission, cs.customers_status_bt_permission  FROM " . TABLE_CUSTOMERS . " as c left join " . TABLE_CUSTOMERS_STATUS . " as cs on customers_status = customers_status_id where c.customers_id='" . $_SESSION['customer_id'] . "' and cs.language_id = '" . $_SESSION['languages_id'] . "'");
  } else {
    $customer_status_query = xtc_db_query("select                                    cs.customers_status_id, cs.customers_status_name, cs.customers_status_public, cs.customers_status_show_price, cs_customers_status_min_order, cs.customers_status_max_order, cs.customers_status_show_price_tax, cs.customers_status_image, cs.customers_status_discount, cs.customers_status_ot_discount_flag, cs.customers_status_ot_discount, cs.customers_status_graduated_prices  FROM "                                        . TABLE_CUSTOMERS_STATUS . " as cs                                           where cs.customers_status_id='" . DEFAULT_CUSTOMERS_STATUS_ID_GUEST . "' and cs.language_id = '" . $_SESSION['languages_id'] . "'");
    $customer_status_value['customers_status'] = DEFAULT_CUSTOMERS_STATUS_ID_GUEST;
  }

  $customer_status_value = xtc_db_fetch_array($customer_status_query);
  $_SESSION['customer_status_value'] = $customer_status_value; // Hetfield - 2009-08-19 - removed deprecated function session_register to be ready for PHP >= 5.3
  
  return $customer_status_value;
}
?>