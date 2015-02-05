<?php
/* --------------------------------------------------------------
   $Id: shipping_status.php 899 2005-04-29 02:40:57Z hhgag $

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   --------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(orders_status.php,v 1.7 2002/01/30); www.oscommerce.com 
   (c) 2003	 nextcommerce (orders_status.php,v 1.4 2003/08/14); www.nextcommerce.org

   Released under the GNU General Public License 
   --------------------------------------------------------------*/

define('HEADING_TITLE', 'Lieferstatus');

define('TABLE_HEADING_SHIPPING_STATUS_IMAGE', 'Bild');
define('TABLE_HEADING_SHIPPING_STATUS', 'Lieferstatus');
define('TABLE_HEADING_SORT', 'Sortierung');
define('TABLE_HEADING_ACTION', 'Aktion');

define('TEXT_INFO_EDIT_INTRO', 'Bitte f&uuml;hren Sie notwendige &Auml;nderungen durch');
define('TEXT_INFO_SHIPPING_STATUS_NAME', 'Lieferstatus:');
define('TEXT_INFO_INSERT_INTRO', 'Bitte geben Sie den neuen Lieferstatus mit allen relevanten Daten ein');
define('TEXT_INFO_DELETE_INTRO', 'Sind Sie sicher, dass Sie diesen Lieferstatus l&ouml;schen m&ouml;chten?');
define('TEXT_INFO_HEADING_NEW_SHIPPING_STATUS', 'Neuer Lieferstatus');
define('TEXT_INFO_HEADING_EDIT_SHIPPING_STATUS', 'Lieferstatus bearbeiten');
define('TEXT_INFO_SHIPPING_STATUS_IMAGE', 'Bild:');
define('TEXT_INFO_HEADING_DELETE_SHIPPING_STATUS', 'Lieferstatus l&ouml;schen');
define('TEXT_DELETE_IMAGE', 'Bild l&ouml;schen');
define('TEXT_INFO_SHIPPING_STATUS_SORT_ORDER', 'Sortierung:');

define('ERROR_REMOVE_DEFAULT_SHIPPING_STATUS', 'Fehler: Der Standard-Lieferstatus kann nicht gel&ouml;scht werden. Bitte definieren Sie einen neuen Standard-Lieferstatus und wiederholen Sie den Vorgang.');
define('ERROR_STATUS_USED_IN_ORDERS', 'Fehler: Dieser Lieferstatus wird zur Zeit noch f&uuml;r Artikel verwendet.');
define('ERROR_STATUS_USED_IN_HISTORY', 'Fehler: Dieser Lieferstatus wird zur Zeit noch f&uuml;r Artikel verwendet.');
?>