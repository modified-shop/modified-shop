<?php
/* -----------------------------------------------------------------------------------------
   $Id: xtc_set_ibillnr.php

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   hendrik - 2011-05-14 - independent invoice number and date 

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/
                                                               
function xtc_set_ibillnr($orders_id, $ibn_billnr){
  $query = "update " . 
              TABLE_ORDERS . " 
            set 
              ibn_billnr= '" . $ibn_billnr . "', 
              ibn_billdate= now()
            where 
              orders_id = '" . $orders_id . "'"; 
  return xtc_db_query($query);
}

 ?>