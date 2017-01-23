<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

define('MODULE_EXCLUDE_PAYMENT_TEXT_TITLE', 'Zahlarten abh&auml;ngig von der Versandart');
define('MODULE_EXCLUDE_PAYMENT_TEXT_DESCRIPTION', '');
define('MODULE_EXCLUDE_PAYMENT_STATUS_TITLE' , 'Status');
define('MODULE_EXCLUDE_PAYMENT_STATUS_DESC' , 'Modul aktivieren?');
define('MODULE_EXCLUDE_PAYMENT_NUMBER_TITLE' , 'Anzahl der Versandarten');
define('MODULE_EXCLUDE_PAYMENT_NUMBER_DESC' , 'Anzahl der Versandarten die konfiguriert werden sollen.');

for ($module_exclude_payment_i = 1; $module_exclude_payment_i <= MODULE_EXCLUDE_PAYMENT_NUMBER; $module_exclude_payment_i ++) {
  define('MODULE_EXCLUDE_PAYMENT_SHIPPING_'.$module_exclude_payment_i.'_TITLE' , '<hr noshade>'.$module_exclude_payment_i.'. Versandart');
  define('MODULE_EXCLUDE_PAYMENT_SHIPPING_'.$module_exclude_payment_i.'_DESC' , 'W&auml;hlen Sie die Versandart bei der Sie eine Zahlart ausschliessen wollen.');
  define('MODULE_EXCLUDE_PAYMENT_PAYMENT_'.$module_exclude_payment_i.'_TITLE' , $module_exclude_payment_i.'. ausgeschlossene Zahlart');
  define('MODULE_EXCLUDE_PAYMENT_PAYMENT_'.$module_exclude_payment_i.'_DESC' , 'W&auml;hlen Sie die Zahlart die Sie ausschliessen wollen.');
}

?>