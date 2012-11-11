<?php
/**
 * @version SOFORT Gateway 5.2.0 - $Date: 2012-09-06 14:27:56 +0200 (Thu, 06 Sep 2012) $
 * @author SOFORT AG (integration@sofort.com)
 * @link http://www.sofort.com/
 *
 * Copyright (c) 2012 SOFORT AG
 *
 * $Id$
 */

$num = 3;

define('MODULE_ORDER_TOTAL_SOFORT_TITLE', 'sofort.de Rabattmodul');
define('MODULE_ORDER_TOTAL_SOFORT_DESCRIPTION', 'Rabatt für Zahlungsarten von sofort.com');

define('MODULE_ORDER_TOTAL_SOFORT_STATUS_TITLE', 'Rabatt anzeigen');
define('MODULE_ORDER_TOTAL_SOFORT_STATUS_DESC', 'Wollen Sie den Zahlungsartenrabatt einschalten?');

define('MODULE_ORDER_TOTAL_SOFORT_SORT_ORDER_TITLE', 'Anzeigereihenfolge');
define('MODULE_ORDER_TOTAL_SOFORT_SORT_ORDER_DESC', 'Reihenfolge der Anzeige. Kleinste Ziffer wird zuerst angezeigt.');

define('MODULE_ORDER_TOTAL_SOFORT_PERCENTAGE_SU_TITLE', 'Rabattstaffel für SOFORT Überweisung');
define('MODULE_ORDER_TOTAL_SOFORT_PERCENTAGE_SU_DESC', 'Rabattierung (Mindestwert:Prozent&Festbetrag)');

define('MODULE_ORDER_TOTAL_SOFORT_PERCENTAGE_SL_TITLE', 'Rabattstaffel für SOFORT Lastschrift');
define('MODULE_ORDER_TOTAL_SOFORT_PERCENTAGE_SL_DESC', 'Rabattierung (Mindestwert:Prozent&Festbetrag)');

define('MODULE_ORDER_TOTAL_SOFORT_PERCENTAGE_SR_TITLE', 'Rabattstaffel für Rechnung by SOFORT');
define('MODULE_ORDER_TOTAL_SOFORT_PERCENTAGE_SR_DESC', 'Rabattierung (Mindestwert:Prozent&Festbetrag)');

define('MODULE_ORDER_TOTAL_SOFORT_PERCENTAGE_SV_TITLE', 'Rabattstaffel für Vorkasse by SOFORT');
define('MODULE_ORDER_TOTAL_SOFORT_PERCENTAGE_SV_DESC', 'Rabattierung (Mindestwert:Prozent&Festbetrag)');

define('MODULE_ORDER_TOTAL_SOFORT_PERCENTAGE_LS_TITLE', 'Rabattstaffel für Lastschrift by SOFORT');
define('MODULE_ORDER_TOTAL_SOFORT_PERCENTAGE_LS_DESC', 'Rabattierung (Mindestwert:Prozent&Festbetrag)');

define('MODULE_ORDER_TOTAL_SOFORT_INC_SHIPPING_TITLE', 'Inklusive Versandkosten');
define('MODULE_ORDER_TOTAL_SOFORT_INC_SHIPPING_DESC', 'Versandkosten werden mit Rabattiert');

define('MODULE_ORDER_TOTAL_SOFORT_INC_TAX_TITLE', 'Inklusive Ust');
define('MODULE_ORDER_TOTAL_SOFORT_INC_TAX_DESC', 'Ust wird mit Rabattiert');

define('MODULE_ORDER_TOTAL_SOFORT_CALC_TAX_TITLE', 'Ust Berechnung');
define('MODULE_ORDER_TOTAL_SOFORT_CALC_TAX_DESC', 'erneutes berechnen der Ust Summe');

define('MODULE_ORDER_TOTAL_SOFORT_ALLOWED_TITLE', 'Erlaubte Zonen');
define('MODULE_ORDER_TOTAL_SOFORT_ALLOWED_DESC' , 'Geben Sie <b>einzeln</b> die Zonen an, welche für dieses Modul erlaubt sein sollen. (z.B. AT,DE (wenn leer, werden alle Zonen erlaubt))');

define('MODULE_ORDER_TOTAL_SOFORT_DISCOUNT', 'Rabatt');
define('MODULE_ORDER_TOTAL_SOFORT_FEE', 'Zuschlag');

define('MODULE_ORDER_TOTAL_SOFORT_TAX_CLASS_TITLE','Steuerklasse');
define('MODULE_ORDER_TOTAL_SOFORT_TAX_CLASS_DESC','Die Steuerklasse spielt keine Rolle und dient nur der Vermeidung einer Fehlermeldung.');

define('MODULE_ORDER_TOTAL_SOFORT_BREAK_TITLE','Mehrfachberechnung');
define('MODULE_ORDER_TOTAL_SOFORT_BREAK_DESC','Sollten Mehrfachberechnungen möglich sein? Wenn nein, wird nach dem ersten passenden Rabatt abgebrochen.');
?>