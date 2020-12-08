<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  function get_country_id($iso_code_2) {
    $country_query = xtc_db_query("SELECT countries_id
                                     FROM ".TABLE_COUNTRIES."
                                    WHERE countries_iso_code_2 = '".xtc_db_input($iso_code_2)."'");
    $country = xtc_db_fetch_array($country_query);
    return $country['countries_id'];
  }