<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

  function cron_customers_ip_maintenance() {
    $customers_ip_date = date('Y-m-d 00:00:00', strtotime('-1 year'));
    
    $customers_query = xtc_db_query("SELECT *, 
                                            MAX(customers_ip_id) as max_customers_ip_id 
                                       FROM ".TABLE_CUSTOMERS_IP." 
                                      WHERE customers_ip_date > '".$customers_ip_date."' 
                                   GROUP BY customers_id");
    while ($customers = xtc_db_fetch_array($customers_query)) {
      xtc_db_query("DELETE FROM ".TABLE_CUSTOMERS_IP." 
                     WHERE customers_ip_date < '".$customers_ip_date."'
                       AND customers_id = '".(int)$customers['customers_id']."'
                       AND customers_ip_id != '".(int)$customers['max_customers_ip_id']."'");
    }
    
    return true;
  }