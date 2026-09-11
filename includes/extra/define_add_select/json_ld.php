<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2026 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  // a listing that publishes offers has to know the release date, otherwise an article on
  // pre-order is listed as in stock while its own page says the opposite
  if (defined('MODULE_JSON_LD_STATUS') && MODULE_JSON_LD_STATUS == 'true'
      && defined('MODULE_JSON_LD_LISTING') && MODULE_JSON_LD_LISTING == 'full'
      )
  {
    $add_select_default[] = 'p.products_date_available';
    $add_select_search[] = 'p.products_date_available';
  }
