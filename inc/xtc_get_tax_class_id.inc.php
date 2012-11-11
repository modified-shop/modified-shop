<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   xtcModified - community made shopping
   http://www.xtc-modified.org

   Copyright (c) 2009 - 2012 xtcModified
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  function xtc_get_tax_class_id($products_id) {


    $tax_query = xtc_db_query("SELECT
                               products_tax_class_id
                               FROM ".TABLE_PRODUCTS."
                               where products_id='".$products_id."'");
    $tax_query_data=xtc_db_fetch_array($tax_query);

    return $tax_query_data['products_tax_class_id'];
  }
 ?>