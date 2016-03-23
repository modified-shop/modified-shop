<?php
/* -----------------------------------------------------------------------------------------
   $Id: ot_cod_fee.php 1003 2005-07-10 18:58:52Z mz $

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

  define('MODULE_ORDER_TOTAL_COD_FEE_TITLE', 'Nachnahmegeb&uuml;hr');
  define('MODULE_ORDER_TOTAL_COD_FEE_DESCRIPTION', 'Berechnung der Nachnahmegeb&uuml;hr');

  define('MODULE_ORDER_TOTAL_COD_FEE_STATUS_TITLE','Nachnahmegeb&uuml;hr');
  define('MODULE_ORDER_TOTAL_COD_FEE_STATUS_DESC','Berechnung der Nachnahmegeb&uuml;hr');

  define('MODULE_ORDER_TOTAL_COD_FEE_SORT_ORDER_TITLE','Sortierreihenfolge');
  define('MODULE_ORDER_TOTAL_COD_FEE_SORT_ORDER_DESC','Anzeigereihenfolge');

  define('MODULE_ORDER_TOTAL_COD_FEE_TAX_CLASS_TITLE','Steuerklasse');
  define('MODULE_ORDER_TOTAL_COD_FEE_TAX_CLASS_DESC','W&auml;hlen Sie eine Steuerklasse.');

  function define_shipping_titles_cod() {
    $module_keys = str_replace('.php','',MODULE_SHIPPING_INSTALLED);
    $installed_shipping_modules = explode(';',$module_keys);

    if (count($installed_shipping_modules) > 0) {
      foreach($installed_shipping_modules as $shipping_code) {
        $module_type = 'shipping';
        $file = $shipping_code.'.php';
        $shipping_code = strtoupper($shipping_code);
        $title = '';

        if (defined('DIR_FS_LANGUAGES') && file_exists(DIR_FS_LANGUAGES . 'german/modules/' . $module_type . '/' . $file)) {
            include_once(DIR_FS_LANGUAGES . 'german/modules/' . $module_type . '/' . $file);
            $title = constant('MODULE_SHIPPING_'.$shipping_code.'_TEXT_TITLE');
        }
        $shipping_code = ($shipping_code == 'FREEAMOUNT') ? 'FREEAMOUNT_FREE' : 'FEE_' . $shipping_code;

        define('MODULE_ORDER_TOTAL_COD_'.$shipping_code.'_TITLE',$title);
        define('MODULE_ORDER_TOTAL_COD_'.$shipping_code.'_DESC','&lt;ISO2-Code&gt;:&lt;Preis&gt;, ....<br />
        00 als ISO2-Code erm&ouml;glicht den Nachnahmeversand in alle L&auml;nder. Wenn
        00 verwendet wird, muss dieses als letztes Argument eingetragen werden. Wenn
        kein 00:9.99 eingetragen ist, wird der Nachnahmeversand ins Ausland nicht berechnet
        (nicht m&ouml;glich). Um nur ein Land ausschlie&szlig;en, keine Kosten f&uuml;r dieses Land
        eingeben. Beispiel: DE:4.00,CH:,00:9.99<br />-&gt; Erkl&auml;rung: Versand nach DE: 4&euro; /
        Versand nach CH: nicht m&ouml;glich / Versand in den Rest der Welt: 9,99&euro;');
      }
    }
  }
  define_shipping_titles_cod();
?>