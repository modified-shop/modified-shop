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

  define('MODULE_ORDER_TOTAL_COD_FEE_FLAT_TITLE','Pauschale Versandkosten');
  define('MODULE_ORDER_TOTAL_COD_FEE_FLAT_DESC','&lt;ISO2-Code&gt;:&lt;Preis&gt;, ....<br />
  00 als ISO2-Code erm&ouml;glicht den Nachnahmeversand in alle L&auml;nder. Wenn 
  00 verwendet wird, muss dieses als letztes Argument eingetragen werden. Wenn 
  kein 00:9.99 eingetragen ist, wird der Nachnahmeversand ins Ausland nicht berechnet 
  (nicht m&ouml;glich).');

  define('MODULE_ORDER_TOTAL_COD_FEE_ITEM_TITLE','Versandkosten pro St&uuml;ck');
  define('MODULE_ORDER_TOTAL_COD_FEE_ITEM_DESC','&lt;ISO2-Code&gt;:&lt;Preis&gt;, ....<br />
  00 als ISO2-Code erm&ouml;glicht den Nachnahmeversand in alle L&auml;nder. Wenn 
  00 verwendet wird, muss dieses als letztes Argument eingetragen werden. Wenn 
  kein 00:9.99 eingetragen ist, wird der Nachnahmeversand ins Ausland nicht berechnet 
  (nicht m&ouml;glich).');

  define('MODULE_ORDER_TOTAL_COD_FEE_TABLE_TITLE','Tabellarische Versandkosten');
  define('MODULE_ORDER_TOTAL_COD_FEE_TABLE_DESC','&lt;ISO2-Code&gt;:&lt;Preis&gt;, ....<br />
  00 als ISO2-Code erm&ouml;glicht den Nachnahmeversand in alle L&auml;nder. Wenn 
  00 verwendet wird, muss dieses als letztes Argument eingetragen werden. Wenn 
  kein 00:9.99 eingetragen ist, wird der Nachnahmeversand ins Ausland nicht berechnet 
  (nicht m&ouml;glich).');

  define('MODULE_ORDER_TOTAL_COD_FEE_ZONES_TITLE','Versandkosten nach Zonen');
  define('MODULE_ORDER_TOTAL_COD_FEE_ZONES_DESC','&lt;ISO2-Code&gt;:&lt;Preis&gt;, ....<br />
  00 als ISO2-Code erm&ouml;glicht den Nachnahmeversand in alle L&auml;nder. Wenn 
  00 verwendet wird, muss dieses als letztes Argument eingetragen werden. Wenn 
  kein 00:9.99 eingetragen ist, wird der Nachnahmeversand ins Ausland nicht berechnet 
  (nicht m&ouml;glich).');

  define('MODULE_ORDER_TOTAL_COD_FEE_AP_TITLE','&Ouml;sterreichische Post AG');
  define('MODULE_ORDER_TOTAL_COD_FEE_AP_DESC','&lt;ISO2-Code&gt;:&lt;Preis&gt;, ....<br />
  00 als ISO2-Code erm&ouml;glicht den Nachnahmeversand in alle L&auml;nder. Wenn 
  00 verwendet wird, muss dieses als letztes Argument eingetragen werden. Wenn 
  kein 00:9.99 eingetragen ist, wird der Nachnahmeversand ins Ausland nicht berechnet 
  (nicht m&ouml;glich).');

  define('MODULE_ORDER_TOTAL_COD_FEE_CHP_TITLE','Schweizerische Post');
  define('MODULE_ORDER_TOTAL_COD_FEE_CHP_DESC','&lt;ISO2-Code&gt;:&lt;Preis&gt;, ....<br />
  00 als ISO2-Code erm&ouml;glicht den Nachnahmeversand in alle L&auml;nder. Wenn
  00 verwendet wird, muss dieses als letztes Argument eingetragen werden. Wenn
  kein 00:9.99 eingetragen ist, wird der Nachnahmeversand ins Ausland nicht berechnet
  (nicht m&ouml;glich).');

  define('MODULE_ORDER_TOTAL_COD_FEE_CHRONOPOST_TITLE','Chronopost Zone Rates');
  define('MODULE_ORDER_TOTAL_COD_FEE_CHRONOPOST_DESC','&lt;ISO2-Code&gt;:&lt;Preis&gt;, ....<br />
  00 als ISO2-Code erm&ouml;glicht den Nachnahmeversand in alle L&auml;nder. Wenn
  00 verwendet wird, muss dieses als letztes Argument eingetragen werden. Wenn
  kein 00:9.99 eingetragen ist, wird der Nachnahmeversand ins Ausland nicht berechnet
  (nicht m&ouml;glich).');

  define('MODULE_ORDER_TOTAL_COD_FEE_DHL_TITLE','DHL &Ouml;sterreich');
  define('MODULE_ORDER_TOTAL_COD_FEE_DHL_DESC','&lt;ISO2-Code&gt;:&lt;Preis&gt;, ....<br />
  00 als ISO2-Code erm&ouml;glicht den Nachnahmeversand in alle L&auml;nder. Wenn
  00 verwendet wird, muss dieses als letztes Argument eingetragen werden. Wenn
  kein 00:9.99 eingetragen ist, wird der Nachnahmeversand ins Ausland nicht berechnet
  (nicht m&ouml;glich).');

  define('MODULE_ORDER_TOTAL_COD_FEE_DP_TITLE','Deutsche Post AG');
  define('MODULE_ORDER_TOTAL_COD_FEE_DP_DESC','&lt;ISO2-Code&gt;:&lt;Preis&gt;, ....<br />
  00 als ISO2-Code erm&ouml;glicht den Nachnahmeversand in alle L&auml;nder. Wenn
  00 verwendet wird, muss dieses als letztes Argument eingetragen werden. Wenn
  kein 00:9.99 eingetragen ist, wird der Nachnahmeversand ins Ausland nicht berechnet
  (nicht m&ouml;glich).');
  
  // BOF - vr - 2009-12-04 - missing language definitions for OT-COD-FEE-UPS
  define('MODULE_ORDER_TOTAL_COD_FEE_UPS_TITLE','UPS');
  define('MODULE_ORDER_TOTAL_COD_FEE_UPS_DESC','&lt;ISO2-Code&gt;:&lt;Preis&gt;, ....<br />
  00 als ISO2-Code erm&ouml;glicht den Nachnahmeversand in alle L&auml;nder. Wenn
  00 verwendet wird, muss dieses als letztes Argument eingetragen werden. Wenn
  kein 00:9.99 eingetragen ist, wird der Nachnahmeversand ins Ausland nicht berechnet
  (nicht m&ouml;glich).');
  
  define('MODULE_ORDER_TOTAL_COD_FEE_UPSE_TITLE','UPS Express');
  define('MODULE_ORDER_TOTAL_COD_FEE_UPSE_DESC','&lt;ISO2-Code&gt;:&lt;Preis&gt;, ....<br />
  00 als ISO2-Code erm&ouml;glicht den Nachnahmeversand in alle L&auml;nder. Wenn
  00 verwendet wird, muss dieses als letztes Argument eingetragen werden. Wenn
  kein 00:9.99 eingetragen ist, wird der Nachnahmeversand ins Ausland nicht berechnet
  (nicht m&ouml;glich).');
  // EOF - vr - 2009-12-04 - missing language definitions for OT-COD-FEE-UPS

  define('MODULE_ORDER_TOTAL_COD_FEE_FREE_TITLE','Versandkostenfrei (Modul Versandkosten in Zusammenfassung)');
  define('MODULE_ORDER_TOTAL_COD_FEE_FREE_DESC','&lt;ISO2-Code&gt;:&lt;Preis&gt;, ....<br />
  00 als ISO2-Code erm&ouml;glicht den Nachnahmeversand in alle L&auml;nder. Wenn
  00 verwendet wird, muss dieses als letztes Argument eingetragen werden. Wenn
  kein 00:9.99 eingetragen ist, wird der Nachnahmeversand ins Ausland nicht berechnet
  (nicht m&ouml;glich).');
  
  define('MODULE_ORDER_TOTAL_FREEAMOUNT_FREE_TITLE','Versandkostenfrei (Modul Versankosten in Versandkosten)');
  define('MODULE_ORDER_TOTAL_FREEAMOUNT_FREE_DESC','&lt;ISO2-Code&gt;:&lt;Preis&gt;, ....<br />
  00 als ISO2-Code erm&ouml;glicht den Nachnahmeversand in alle L&auml;nder. Wenn
  00 verwendet wird, muss dieses als letztes Argument eingetragen werden. Wenn
  kein 00:9.99 eingetragen ist, wird der Nachnahmeversand ins Ausland nicht berechnet
  (nicht m&ouml;glich).');  

  define('MODULE_ORDER_TOTAL_COD_FEE_TAX_CLASS_TITLE','Steuerklasse');
  define('MODULE_ORDER_TOTAL_COD_FEE_TAX_CLASS_DESC','W&auml;hlen Sie eine Steuerklasse.');
?>