<?php
/* -----------------------------------------------------------------------------------------
   $Id: xtc_get_tax_rate_from_desc.inc.php 2843 2012-05-06 14:30:10Z web28 $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(application_top.php,v 1.273 2003/05/19); www.oscommerce.com
   (c) 2003     nextcommerce (application_top.php,v 1.54 2003/08/25); www.nextcommerce.org
   (c) 2006 XT-Commerce (xtc_get_tax_rate_from_desc.inc.php 455 2009-11-01 21); ; www.xt-commerce.com

   Released under the GNU General Public License
   -----------------------------------------------------------------------------------------
   Third Party contribution:

   Credit Class/Gift Vouchers/Discount Coupons (Version 5.10)
   http://www.oscommerce.com/community/contributions,282
   Copyright (c) Strider | Strider@oscworks.com
   Copyright (c  Nick Stanko of UkiDev.com, nick@ukidev.com
   Copyright (c) Andre ambidex@gmx.net
   Copyright (c) 2001,2002 Ian C Wilson http://www.phesis.org


   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

// Get tax rate from tax description
  function xtc_get_tax_rate_from_desc($tax_desc) {
    //search digits in tax_description
    if (preg_match('/\d+\.?\d*/', str_replace(',', '.', $tax_desc), $matches)) {
      return floatval($matches[0]);
    }
    //check for TAX_SHORT_DISPLAY and remove it
    if(defined('TAX_SHORT_DISPLAY') && TAX_SHORT_DISPLAY != '') {
      $tax_desc = trim(str_replace(TAX_SHORT_DISPLAY, '', $tax_desc));
    }
    //remove tax info text
    $tax_desc = trim(str_replace(array(TAX_ADD_TAX,TAX_NO_TAX), '', $tax_desc));
    //get tax_rate from table tax_rates by tax_description
    $tax_query = xtc_db_query("select tax_rate from " . TABLE_TAX_RATES . " where tax_description = '" . $tax_desc . "'");
    if (xtc_db_num_rows($tax_query) > 0) {
      $tax = xtc_db_fetch_array($tax_query);
      return $tax['tax_rate'];
    }
    //no tax_rate find, return 0
    return 0;
  }
?>