<?php
/*
 $Id$

 osCommerce, Open Source E-Commerce Solutions
 http://www.oscommerce.com

 Copyright (c) 2001 - 2003 osCommerce

 Released under the GNU General Public License

 ************************************************************************
 Copyright (C) 2001 - 2003 TheMedia, Dipl.-Ing Thomas Pl�nkers
 http://www.themedia.at & http://www.oscommerce.at

 Copyright (C) 2004 - 2009 iclear GmbH, Mannheim, FRG
 All rights reserved.

 This program is free software licensed under the GNU General Public License (GPL).

 This program is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with this program; if not, write to the Free Software
 Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307
 USA

 *************************************************************************/
global $icCore, $icLang;
if(!class_exists('IclearLanguage')) {
  $icPath = DIR_FS_EXTERNAL.'iclear/class/IclearLanguage.php'; //DokuMan - 2011-09-06 - move iclear to 'external' directory
  //$icPath = './iclear/class/IclearLanguage.php';
  //if(!file_exists($icPath)) {
    //$icPath = '.' . $icPath;
  //}
  require_once $icPath;
}
$icLang = new IclearLanguage('de');

define('MODULE_PAYMENT_ICLEAR_INVOICE_TEXT_TITLE', $icLang->getParam('MODULE_TITLE_INVOICE'));

define('MODULE_PAYMENT_ICLEAR_INVOICE_STATUS_TITLE', $icLang->getParam('STATUS_TITLE_INVOICE'));
define('MODULE_PAYMENT_ICLEAR_INVOICE_STATUS_DESC', $icLang->getParam('STATUS_DESC_INVOICE'));

define('MODULE_PAYMENT_ICLEAR_INVOICE_ALLOWED_TITLE', $icLang->getParam('ALLOWED_TITLE'));
define('MODULE_PAYMENT_ICLEAR_INVOICE_ALLOWED_DESC', $icLang->getParam('ALLOWED_DESC'));

define('MODULE_PAYMENT_ICLEAR_INVOICE_ID_TITLE', $icLang->getParam('ID_TITLE'));
define('MODULE_PAYMENT_ICLEAR_INVOICE_ID_DESC', $icLang->getParam('ID_DESC'));

define('MODULE_PAYMENT_ICLEAR_INVOICE_ZONE_TITLE', $icLang->getParam('ZONE_TITLE'));
define('MODULE_PAYMENT_ICLEAR_INVOICE_ZONE_DESC', $icLang->getParam('ZONE_DESC'));

define('MODULE_PAYMENT_ICLEAR_INVOICE_SORT_ORDER_TITLE', $icLang->getParam('SORT_ORDER_TITLE'));
define('MODULE_PAYMENT_ICLEAR_INVOICE_SORT_ORDER_DESC', $icLang->getParam('SORT_ORDER_DESC'));

define('MODULE_PAYMENT_ICLEAR_INVOICE_ORDER_STATUS_ID_TITLE', $icLang->getParam('ORDER_STATUS_ID_TITLE'));
define('MODULE_PAYMENT_ICLEAR_INVOICE_ORDER_STATUS_ID_DESC', $icLang->getParam('ORDER_STATUS_ID_DESC'));

define('MODULE_PAYMENT_ICLEAR_INVOICE_IFRAME_TITLE', $icLang->getParam('IFRAME_TITLE'));
define('MODULE_PAYMENT_ICLEAR_INVOICE_IFRAME_DESC', $icLang->getParam('IFRAME_DESC'));

// BOF - Hendrik - 2010-08-09 - exlusion config for shipping modules
define('MODULE_PAYMENT_ICLEAR_INVOICE_NEG_SHIPPING_TITLE', 'Ausschlu&szlig; bei Versandmodulen');
define('MODULE_PAYMENT_ICLEAR_INVOICE_NEG_SHIPPING_DESC', 'Dieses Zahlungsmodul deaktivieren wenn Versandmodul gew&auml;hlt (Komma separierte Liste)');
// EOF - Hendrik - 2010-08-09 - exlusion config for shipping modules
?>