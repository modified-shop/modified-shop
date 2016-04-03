<?php
/* -----------------------------------------------------------------------------------------
   $Id: ot_cod_fee.php 914 2005-04-30 02:54:02Z matthias $

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(ot_cod_fee.php,v 1.02 2003/02/24); www.oscommerce.com
   (C) 2001 - 2003 TheMedia, Dipl.-Ing Thomas Plänkers ; http://www.themedia.at & http://www.oscommerce.at

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  define('MODULE_ORDER_TOTAL_COD_FEE_TITLE', 'COD charge');
  define('MODULE_ORDER_TOTAL_COD_FEE_DESCRIPTION', 'Calculation of the COD charge');

  define('MODULE_ORDER_TOTAL_COD_FEE_STATUS_TITLE','COD charge');
  define('MODULE_ORDER_TOTAL_COD_FEE_STATUS_DESC','Calculation of the COD charge');

  define('MODULE_ORDER_TOTAL_COD_FEE_SORT_ORDER_TITLE','Sort Order');
  define('MODULE_ORDER_TOTAL_COD_FEE_SORT_ORDER_DESC','Sort order of display');

  define('MODULE_ORDER_TOTAL_COD_FEE_TAX_CLASS_TITLE','Taxclass');
  define('MODULE_ORDER_TOTAL_COD_FEE_TAX_CLASS_DESC','Choose a taxclass.');

  function define_shipping_titles_cod() {
    $module_keys = str_replace('.php','',MODULE_SHIPPING_INSTALLED);
    $installed_shipping_modules = explode(';',$module_keys);

    if (count($installed_shipping_modules) > 0) {
      foreach($installed_shipping_modules as $shipping_code) {
        $module_type = 'shipping';
        $file = $shipping_code.'.php';
        $shipping_code = strtoupper($shipping_code);
        $title = '';

        if (defined('DIR_FS_LANGUAGES') && file_exists(DIR_FS_LANGUAGES . 'english/modules/' . $module_type . '/' . $file)) {
            include_once(DIR_FS_LANGUAGES . 'english/modules/' . $module_type . '/' . $file);
            $title = constant('MODULE_SHIPPING_'.$shipping_code.'_TEXT_TITLE');
        }
        $shipping_code = ($shipping_code == 'FREEAMOUNT') ? 'FREEAMOUNT_FREE' : 'FEE_' . $shipping_code;

        define('MODULE_ORDER_TOTAL_COD_'.$shipping_code.'_TITLE',$title);
        define('MODULE_ORDER_TOTAL_COD_'.$shipping_code.'_DESC','&lt;ISO2-Code&gt;:&lt;Price&gt;, ....<br />
        00 as ISO2-Code allows the COD shipping in all countries. If
        00 is used you have to enter it as last argument. If
        no 00:9.99 is entered the COD shipping into foreign countries will not be calculated
        (not possible). To exclude only one country, do not enter costs for this country.
        Example: DE:4.00,CH:,00:9.99<br />-> Explanation: Shipping to DE: 4&euro; / Shipping to CH: not possible
        / Shipping to the rest of the world: 9,99&euro;');
      }
    }
  }
  define_shipping_titles_cod();
?>