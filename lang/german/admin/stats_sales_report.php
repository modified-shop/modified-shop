<?php
/* --------------------------------------------------------------
   $Id: stats_sales_report.php 1311 2005-10-18 12:30:40Z mz $   

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   --------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(stats_sales_report.php,v 1.6 2002/03/30); www.oscommerce.com 
   (c) 2003	 nextcommerce (stats_sales_report.php,v 1.4 2003/08/14); www.nextcommerce.org

   Released under the GNU General Public License 
   --------------------------------------------------------------*/
 

define('REPORT_DATE_FORMAT', 'd. m. Y');

define('HEADING_TITLE', 'Verkaufs Report');

define('REPORT_SALES_STATISTICS', 'Umsatzstatistik Filter');
define('REPORT_TYPE_YEARLY', 'J&auml;hrlich');
define('REPORT_TYPE_MONTHLY', 'Monatlich');
define('REPORT_TYPE_WEEKLY', 'W&ouml;chentlich');
define('REPORT_TYPE_DAILY', 'T&auml;glich');
define('REPORT_START_DATE', 'von Datum');
define('REPORT_END_DATE', 'bis Datum (inklusive)');
define('REPORT_DETAIL', 'Detail');
define('REPORT_MAX', 'zeige Beste');
define('REPORT_ALL', 'Alle');
define('REPORT_SORT', 'Sortierung');
define('REPORT_EXP', 'Export');
define('REPORT_SEND', 'Senden');
define('EXP_NORMAL', 'Normal');
define('EXP_HTML', 'HTML only');
define('EXP_CSV', 'CSV');

define('TABLE_HEADING_DATE', 'Datum');
define('TABLE_HEADING_ORDERS', '# Bestellungen');
define('TABLE_HEADING_ITEMS', '# Artikel');
define('TABLE_HEADING_REVENUE', 'Umsatz');
define('TABLE_HEADING_SHIPPING', 'Versand');
define('TABLE_HEADING_ADDITIONAL', 'Sonstiges');
define('TABLE_HEADING_TOTAL', 'Gesamt');

define('DET_HEAD_ONLY', 'keine Details');
define('DET_DETAIL', 'Details anzeigen');
define('DET_DETAIL_ONLY', 'Details mit Betrag');

define('SORT_VAL0', 'Standard');
define('SORT_VAL1', 'Beschreibung');
define('SORT_VAL2', 'Beschreibung ab');
define('SORT_VAL3', '# Artikel');
define('SORT_VAL4', '# Artikel ab');
define('SORT_VAL5', 'Umsatz');
define('SORT_VAL6', 'Umsatz ab');

define('REPORT_STATUS_FILTER', 'Status');
define('REPORT_PAYMENT_FILTER','Zahlungsweise');

define('SR_SEPARATOR1', ';');
define('SR_SEPARATOR2', ';');
define('SR_NEWLINE', '<br />');
?>