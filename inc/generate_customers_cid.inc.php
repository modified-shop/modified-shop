<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

function generate_customers_cid($create = false) {

  if (!defined('MODULE_CUSTOMERS_CID_STATUS') || MODULE_CUSTOMERS_CID_STATUS == 'false') {
    return '';
  }

  $n = (int)MODULE_CUSTOMERS_CID_NEXT;
  $d = date('d');
  $m = date('m');
  $y = date('Y');

  $cid = MODULE_CUSTOMERS_CID_FORMAT;
  $cid = str_replace('{n}', $n, $cid);
  $cid = str_replace('{d}', $d, $cid);
  $cid = str_replace('{m}', $m, $cid);
  $cid = str_replace('{y}', $y, $cid);
  
  if ($create === true) {
    xtc_db_query("UPDATE ".TABLE_CONFIGURATION." SET configuration_value = '".($n + 1)."' WHERE configuration_key = 'MODULE_CUSTOMERS_CID_NEXT'");
  }
  
  return $cid;
}
?>