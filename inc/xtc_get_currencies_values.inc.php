<?php
/* -----------------------------------------------------------------------------------------
   $Id$   

   xtcModified - community made shopping
   http://www.xtc-modified.org

   Copyright (c) 2009 - 2012 xtcModified
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2003	 nextcommerce (xtc_get_currencies_values.inc.php,v 1.1 2003/08/213); www.nextcommerce.org
   
   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/


function xtc_get_currencies_values($code) {
    $currency_values = xtc_db_query("select * from " . TABLE_CURRENCIES . " where code = '" . $code . "'");
    $currencie_data=xtc_db_fetch_array($currency_values);
    return $currencie_data;
  }

 ?>