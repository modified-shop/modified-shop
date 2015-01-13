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
    $customer_status_query = xtc_db_query("SELECT c.customers_status, 
                                                  c.member_flag, 
                                                  cs.*
                                             FROM " . TABLE_CUSTOMERS . " c 
                                        LEFT JOIN " . TABLE_CUSTOMERS_STATUS . " cs 
                                                  ON customers_status = customers_status_id
                                                     AND cs.language_id = '" . (int)$_SESSION['languages_id'] . "'
                                            WHERE c.customers_id='" . (int)$_SESSION['customer_id'] . "'");
  } else {
    $customer_status_query = xtc_db_query("SELECT cs.*
                                             FROM " . TABLE_CUSTOMERS_STATUS . " cs
                                            WHERE cs.customers_status_id='" . (int) DEFAULT_CUSTOMERS_STATUS_ID_GUEST . "' 
                                              AND cs.language_id = '" . (int)$_SESSION['languages_id'] . "'");
    $customer_status_value['customers_status'] = DEFAULT_CUSTOMERS_STATUS_ID_GUEST;
  }

  $customer_status_value = xtc_db_fetch_array($customer_status_query);
  $_SESSION['customer_status_value'] = $customer_status_value;
  
  return $customer_status_value;
}
?>