<?php
/* -----------------------------------------------------------------------------------------
   $Id: xtc_inc_next_ibillnr.php

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   hendrik - 2011-05-14 - independent invoice number and date 
   
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/
   
function xtc_inc_next_ibillnr(){
  $query = "select 
              configuration_value 
            from " . 
              TABLE_CONFIGURATION . "
            where 
              configuration_key = 'IBN_BILLNR'";
  $result = xtc_db_query($query);
  $data=xtc_db_fetch_array($result);
  
  $data = (int)$data['configuration_value'];
  if( $data==0 ) 
    return 0;

  $data++;
  
  $query = "update " . 
              TABLE_CONFIGURATION . " 
            set 
              configuration_value = '" . $data . "'
            where 
              configuration_key = 'IBN_BILLNR'";
  return xtc_db_query($query);
}

 ?>