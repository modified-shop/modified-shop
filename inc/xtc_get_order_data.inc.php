<?php
/* -----------------------------------------------------------------------------------------
   $Id: xtc_get_order_data.inc.php 899 2005-04-29 02:40:57Z hhgag $   

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2003	 nextcommerce (xtc_get_order_data.inc.php,v 1.1 2003/08/15); www.nextcommerce.org
   
   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

function xtc_get_order_data($order_id) {
  $order_query = xtc_db_query("SELECT *
                                 FROM ".TABLE_ORDERS."
                                WHERE orders_id='".(int)$_GET['oID']."'");
  					
  $order_data= xtc_db_fetch_array($order_query);

  // get order status name	
  $order_status_query=xtc_db_query("SELECT orders_status_name
 				                              FROM ".TABLE_ORDERS_STATUS."
 				                             WHERE orders_status_id='".$order_data['orders_status']."'
 				                               AND language_id='".(int)$_SESSION['languages_id']."'");
  $order_status_data=xtc_db_fetch_array($order_status_query); 			
  $order_data['orders_status'] = $order_status_data['orders_status_name'];
  
  // get language name for payment method
  include(DIR_WS_LANGUAGES.$_SESSION['language'].'/modules/payment/'.$order_data['payment_method'].'.php');
  $order_data['payment_method'] = constant(strtoupper('MODULE_PAYMENT_'.$order_data['payment_method'].'_TEXT_TITLE'));	
  
  return $order_data; 
}
?>