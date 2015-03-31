<?php
/*------------------------------------------------------------------------------
   v 1.0 
   XTC-DPD Shipping Module - Contribution for XT-Commerce http://xt-commerce.com
   modified by http://www.hwangelshop.de

   Copyrigt (c) 2004 cigamth
  ------------------------------------------------------------------------------
   $Id: dpd.php 2751 2012-04-12 13:28:06Z Tomcraft1980 $

   XTC-GLS Shipping Module - Contribution for XT-Commerce http://www.xt-commerce.com
   modified by http://www.hhgag.com

   Copyright (c) 2004 H.H.G.
   -----------------------------------------------------------------------------
   based on:
   (c) 2003 Deutsche Post Module
   Original written by Marcel Bossert-Schwab (webmaster@wernich.de), Version 1.2b
   Addon Released under GLSL V2.0 by Gunter Sammet (Gunter@SammySolutions.com)

   Contribution based on:

   osCommerce, Open Source E-Commerce Solutions
   http://www.oscommerce.com

   Copyright (c) 2002 - 2003 osCommerce

   Released under the GNU General Public License

   ---------------------------------------------------------------------------*/
define('MODULE_SHIPPING_DPD_TEXT_TITLE', 'DPD Dynamic Parcel Distribution');
define('MODULE_SHIPPING_DPD_TEXT_DESCRIPTION', 'DPD Dynamic Parcel Distribution - Weltweites Versandmodul');
define('MODULE_SHIPPING_DPD_TEXT_WAY', 'Versand nach');
define('MODULE_SHIPPING_DPD_TEXT_UNITS', 'kg');
define('MODULE_SHIPPING_DPD_INVALID_ZONE', 'Es ist leider kein Versand in dieses Land m&ouml;glich');
define('MODULE_SHIPPING_DPD_UNDEFINED_RATE', 'Die Versandkosten k&ouml;nnen im Moment nicht errechnet werden');
define('MODULE_SHIPPING_DPD_FREE_SHIPPING', 'Wir &uuml;bernehmen die Versandkosten');
define('MODULE_SHIPPING_DPD_SUBSIDIZED_SHIPPING', 'Einen Teil der Versandkosten &uuml;bernehmen wir.');

define('MODULE_SHIPPING_DPD_STATUS_TITLE', 'DPD Dynamic Parcel Distribution');
define('MODULE_SHIPPING_DPD_STATUS_DESC', 'Wollen Sie den Versand &uuml;ber DPD Dynamic Parcel Distribution anbieten?');
define('MODULE_SHIPPING_DPD_HANDLING_TITLE', 'Bearbeitungsgeb&uuml;hr');
define('MODULE_SHIPPING_DPD_HANDLING_DESC', 'Bearbeitungsgeb&uuml;hr f&uuml;r diese Versandart in Euro');
define('MODULE_SHIPPING_DPD_ALLOWED_TITLE' , 'Erlaubte Versandzonen');
define('MODULE_SHIPPING_DPD_ALLOWED_DESC' , 'Geben Sie <b>einzeln</b> die Zonen an, in welche ein Versand m&ouml;glich sein soll. (z.B. AT,DE (lassen Sie dieses Feld leer, wenn Sie alle Zonen erlauben wollen))');
define('MODULE_SHIPPING_DPD_SORT_ORDER_TITLE', 'Reihenfolge der Anzeige');
define('MODULE_SHIPPING_DPD_SORT_ORDER_DESC', 'Niedrigste wird zuerst angezeigt.');
define('MODULE_SHIPPING_DPD_TAX_CLASS_TITLE', 'Steuersatz');
define('MODULE_SHIPPING_DPD_TAX_CLASS_DESC', 'W&auml;hlen Sie den MwSt.-Satz f&uuml;r diese Versandart aus.');
define('MODULE_SHIPPING_DPD_ZONE_TITLE', 'Versand Zone');
define('MODULE_SHIPPING_DPD_ZONE_DESC', 'Wenn Sie eine Zone ausw&auml;hlen, wird diese Versandart nur in dieser Zone angeboten.');

?>