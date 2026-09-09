<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2026 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  if (defined('MODULE_GUARANTEE_LABELS_STATUS')
      && MODULE_GUARANTEE_LABELS_STATUS == 'true'
      )
  {
    // the label needs duration, model identifier and manufacturer; each query already carries
    // part of that, so only the missing columns are added here. The manufacturer name itself
    // cannot come from here, this extension point adds columns and no joins.

    // listing already selects p.manufacturers_id, the constant carries the model identifier
    $add_select_default[] = 'p.products_garan_duration';

    // search carries the model identifier through the constant as well
    $add_select_search[] = 'p.products_garan_duration';
    $add_select_search[] = 'p.manufacturers_id';

    // cart already selects p.manufacturers_id
    $add_select_cart[] = 'p.products_garan_duration';
    $add_select_cart[] = 'p.products_manufacturers_model';

    // used by new products, cross selling and reverse cross selling
    $add_select_product[] = 'p.products_garan_duration';
    $add_select_product[] = 'p.products_manufacturers_model';
    $add_select_product[] = 'p.manufacturers_id';
  }
