<?php
/* -----------------------------------------------------------------------------------------
   $Id: xtc_get_next_ibillnr.php

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   hendrik - 2011-05-14 - independent invoice number and date 
   
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/
   
function xtc_get_next_ibillnr(){
  $query = "select 
              configuration_value 
            from " . 
              TABLE_CONFIGURATION . "
            where 
              configuration_key = 'IBN_BILLNR'";
  $result = xtc_db_query($query);
  $data=xtc_db_fetch_array($result);
  
  $n = $data['configuration_value'];
  $year = date('Y');
  $month = date('m');
  $day = date('d');

  $d=IBN_BILLNR_FORMAT;
  $d=str_replace('{n}', $n, $d);
  $d=str_replace('{d}', $day, $d);
  $d=str_replace('{m}', $month, $d);
  $d=str_replace('{y}', $year, $d);
  
  return $d;
}

 ?>