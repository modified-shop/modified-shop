<?php
/* -----------------------------------------------------------------------------------------
   $Id$   

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
  require_once (DIR_WS_CLASSES . 'payment.php');
  $payment_modules = new payment($order_data['payment_method']);
  $order_data['payment_method'] = $payment_modules::payment_title($order_data['payment_method'],(int)$_GET['oID']);
  
  return $order_data; 
}
?>