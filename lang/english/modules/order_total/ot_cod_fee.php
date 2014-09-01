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

  define('MODULE_ORDER_TOTAL_COD_FEE_FLAT_TITLE','Flat Shippingcosts');
  define('MODULE_ORDER_TOTAL_COD_FEE_FLAT_DESC','&lt;ISO2-Code&gt;:&lt;Price&gt;, ....<br />
  00 as ISO2-Code allows the COD shipping in all countries. If
  00 is used you have to enter it as last argument. If
  no 00:9.99 is entered the COD shipping into foreign countries will not be calculated
  (not possible).');

  define('MODULE_ORDER_TOTAL_COD_FEE_ITEM_TITLE','Shippingcosts each');
  define('MODULE_ORDER_TOTAL_COD_FEE_ITEM_DESC','&lt;ISO2-Code&gt;:&lt;Price&gt;, ....<br />
  00 as ISO2-Code allows the COD shipping in all countries. If
  00 is used you have to enter it as last argument. If
  no 00:9.99 is entered the COD shipping into foreign countries will not be calculated
  (not possible).');

  define('MODULE_ORDER_TOTAL_COD_FEE_TABLE_TITLE','Tabular Shippingcosts');
  define('MODULE_ORDER_TOTAL_COD_FEE_TABLE_DESC','&lt;ISO2-Code&gt;:&lt;Price&gt;, ....<br />
  00 as ISO2-Code allows the COD shipping in all countries. If
  00 is used you have to enter it as last argument. If
  no 00:9.99 is entered the COD shipping into foreign countries will not be calculated
  (not possible).');

  define('MODULE_ORDER_TOTAL_COD_FEE_ZONES_TITLE','Shippingcosts for zones');
  define('MODULE_ORDER_TOTAL_COD_FEE_ZONES_DESC','&lt;ISO2-Code&gt;:&lt;Price&gt;, ....<br />
  00 as ISO2-Code allows the COD shipping in all countries. If
  00 is used you have to enter it as last argument. If
  no 00:9.99 is entered the COD shipping into foreign countries will not be calculated
  (not possible).');

  define('MODULE_ORDER_TOTAL_COD_FEE_AP_TITLE','Austrian Post AG');
  define('MODULE_ORDER_TOTAL_COD_FEE_AP_DESC','&lt;ISO2-Code&gt;:&lt;Price&gt;, ....<br />
  00 as ISO2-Code allows the COD shipping in all countries. If
  00 is used you have to enter it as last argument. If
  no 00:9.99 is entered the COD shipping into foreign countries will not be calculated
  (not possible).');

  define('MODULE_ORDER_TOTAL_COD_FEE_CHP_TITLE','The Swiss Post');
  define('MODULE_ORDER_TOTAL_COD_FEE_CHP_DESC','&lt;ISO2-Code&gt;:&lt;Price&gt;, ....<br />
  00 as ISO2-Code allows the COD shipping in all countries. If
  00 is used you have to enter it as last argument. If
  no 00:9.99 is entered the COD shipping into foreign countries will not be calculated
  (not possible).');

  define('MODULE_ORDER_TOTAL_COD_FEE_CHRONOPOST_TITLE','Chronopost');
  define('MODULE_ORDER_TOTAL_COD_FEE_CHRONOPOST_DESC','&lt;ISO2-Code&gt;:&lt;Price&gt;, ....<br />
  00 as ISO2-Code allows the COD shipping in all countries. If
  00 is used you have to enter it as last argument. If
  no 00:9.99 is entered the COD shipping into foreign countries will not be calculated
  (not possible).');

  define('MODULE_ORDER_TOTAL_COD_FEE_DHL_TITLE','DHL Austria');
  define('MODULE_ORDER_TOTAL_COD_FEE_DHL_DESC','&lt;ISO2-Code&gt;:&lt;Price&gt;, ....<br />
  00 as ISO2-Code allows the COD shipping in all countries. If
  00 is used you have to enter it as last argument. If
  no 00:9.99 is entered the COD shipping into foreign countries will not be calculated
  (not possible).');

  define('MODULE_ORDER_TOTAL_COD_FEE_DP_TITLE','German Post AG');
  define('MODULE_ORDER_TOTAL_COD_FEE_DP_DESC','&lt;ISO2-Code&gt;:&lt;Price&gt;, ....<br />
  00 as ISO2-Code allows the COD shipping in all countries. If
  00 is used you have to enter it as last argument. If
  no 00:9.99 is entered the COD shipping into foreign countries will not be calculated
  (not possible).');
  
  // BOF - vr - 2009-12-04 - missing language definitions for OT-COD-FEE-UPS
  define('MODULE_ORDER_TOTAL_COD_FEE_UPS_TITLE','UPS');
  define('MODULE_ORDER_TOTAL_COD_FEE_UPS_DESC','&lt;ISO2-Code&gt;:&lt;Price&gt;, ....<br />
  00 as ISO2-Code allows the COD shipping in all countries. If
  00 is used you have to enter it as last argument. If
  no 00:9.99 is entered the COD shipping into foreign countries will not be calculated
  (not possible).');
  
  define('MODULE_ORDER_TOTAL_COD_FEE_UPSE_TITLE','UPS Express');
  define('MODULE_ORDER_TOTAL_COD_FEE_UPSE_DESC','&lt;ISO2-Code&gt;:&lt;Price&gt;, ....<br />
  00 as ISO2-Code allows the COD shipping in all countries. If
  00 is used you have to enter it as last argument. If
  no 00:9.99 is entered the COD shipping into foreign countries will not be calculated
  (not possible).');
  // EOF - vr - 2009-12-04 - missing language definitions for OT-COD-FEE-UPS

  define('MODULE_ORDER_TOTAL_COD_FEE_FREE_TITLE','Free Shipping (Order Total Modul Shipping)');
  define('MODULE_ORDER_TOTAL_COD_FEE_FREE_DESC','&lt;ISO2-Code&gt;:&lt;Price&gt;, ....<br />
  00 as ISO2-Code allows the COD shipping in all countries. If
  00 is used you have to enter it as last argument. If
  no 00:9.99 is entered the COD shipping into foreign countries will not be calculated
  (not possible).');
  
  define('MODULE_ORDER_TOTAL_FREEAMOUNT_FREE_TITLE','Free Shipping (Module Free Shipping)');
  define('MODULE_ORDER_TOTAL_FREEAMOUNT_FREE_DESC','&lt;ISO2-Code&gt;:&lt;Price&gt;, ....<br />
  00 as ISO2-Code allows the COD shipping in all countries. If
  00 is used you have to enter it as last argument. If
  no 00:9.99 is entered the COD shipping into foreign countries will not be calculated
  (not possible).');  

  define('MODULE_ORDER_TOTAL_COD_FEE_TAX_CLASS_TITLE','Taxclass');
  define('MODULE_ORDER_TOTAL_COD_FEE_TAX_CLASS_DESC','Choose a taxclass.');
?>