<?php
/* -----------------------------------------------------------------------------------------
   $Id: ot_ps_fee.php 2301 2011-10-30 12:12:31Z web28 $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(ot_cod_fee.php,v 1.02 2003/02/24); www.oscommerce.com
   (C) 2001 - 2003 TheMedia, Dipl.-Ing Thomas Plänkers ; http://www.themedia.at & http://www.oscommerce.at
   (c) 2006 XT-Commerce (ot_ps_fee.php 899 2005-04-29 02)

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/


  define('MODULE_ORDER_TOTAL_PS_FEE_TITLE', 'Personal Shipping');
  define('MODULE_ORDER_TOTAL_PS_FEE_DESCRIPTION', 'Calculation of the Personal Shipping charge');

  define('MODULE_ORDER_TOTAL_PS_FEE_STATUS_TITLE','Personal Shipping');
  define('MODULE_ORDER_TOTAL_PS_FEE_STATUS_DESC','Calculation of the Personal Shipping charge');

  define('MODULE_ORDER_TOTAL_PS_SORT_ORDER_TITLE','Sort Order');
  define('MODULE_ORDER_TOTAL_PS_SORT_ORDER_DESC','Sort order of display');

  define('MODULE_ORDER_TOTAL_PS_FEE_FLAT_TITLE','Flat Shippingcosts');
  define('MODULE_ORDER_TOTAL_PS_FEE_FLAT_DESC','&lt;ISO2-Code&gt;:&lt;Price&gt;, ....<br />
  00 as ISO2-Code allows the PS shipping in all countries. If
  00 is used you have to enter it as last argument. If
  no 00:9.99 is entered the PS shipping into foreign countries will not be calculated
  (not possible).');

  define('MODULE_ORDER_TOTAL_PS_FEE_ITEM_TITLE','Shippingcosts each');
  define('MODULE_ORDER_TOTAL_PS_FEE_ITEM_DESC','&lt;ISO2-Code&gt;:&lt;Price&gt;, ....<br />
  00 as ISO2-Code allows the PS shipping in all countries. If
  00 is used you have to enter it as last argument. If
  no 00:9.99 is entered the PS shipping into foreign countries will not be calculated
  (not possible).');

  define('MODULE_ORDER_TOTAL_PS_FEE_TABLE_TITLE','Tabular Shippingcosts');
  define('MODULE_ORDER_TOTAL_PS_FEE_TABLE_DESC','&lt;ISO2-Code&gt;:&lt;Price&gt;, ....<br />
  00 as ISO2-Code allows the PS shipping in all countries. If
  00 is used you have to enter it as last argument. If
  no 00:9.99 is entered the PS shipping into foreign countries will not be calculated
  (not possible).');

  define('MODULE_ORDER_TOTAL_PS_FEE_ZONES_TITLE','Shippingcosts for zones');
  define('MODULE_ORDER_TOTAL_PS_FEE_ZONES_DESC','&lt;ISO2-Code&gt;:&lt;Price&gt;, ....<br />
  00 as ISO2-Code allows the PS shipping in all countries. If
  00 is used you have to enter it as last argument. If
  no 00:9.99 is entered the PS shipping into foreign countries will not be calculated
  (not possible).');

  define('MODULE_ORDER_TOTAL_PS_FEE_AP_TITLE','Austrian Post AG');
  define('MODULE_ORDER_TOTAL_PS_FEE_AP_DESC','&lt;ISO2-Code&gt;:&lt;Price&gt;, ....<br />
  00 as ISO2-Code allows the PS shipping in all countries. If
  00 is used you have to enter it as last argument. If
  no 00:9.99 is entered the PS shipping into foreign countries will not be calculated
  (not possible).');

  define('MODULE_ORDER_TOTAL_PS_FEE_DP_TITLE','German Post AG');
  define('MODULE_ORDER_TOTAL_PS_FEE_DP_DESC','&lt;ISO2-Code&gt;:&lt;Price&gt;, ....<br />
  00 as ISO2-Code allows the PS shipping in all countries. If
  00 is used you have to enter it as last argument. If
  no 00:9.99 is entered the PS shipping into foreign countries will not be calculated
  (not possible).');

  define('MODULE_ORDER_TOTAL_PS_TAX_CLASS_TITLE','Taxclass');
  define('MODULE_ORDER_TOTAL_PS_TAX_CLASS_DESC','Choose a taxclass.');
?>
