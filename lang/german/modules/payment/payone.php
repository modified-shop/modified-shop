<?php
/* --------------------------------------------------------------
	payone.php 2013-08-02 mabr
	Gambio GmbH
	http://www.gambio.de
	Copyright (c) 2013 Gambio GmbH
	Released under the GNU General Public License (Version 2)
	[http://www.gnu.org/licenses/gpl-2.0.html]
	--------------------------------------------------------------


	based on:
	(c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
	(c) 2002-2003 osCommerce(ot_cod_fee.php,v 1.02 2003/02/24); www.oscommerce.com
	(C) 2001 - 2003 TheMedia, Dipl.-Ing Thomas Plänkers ; http://www.themedia.at & http://www.oscommerce.at
	(c) 2003 XT-Commerce - community made shopping http://www.xt-commerce.com ($Id: ot_cod_fee.php 1003 2005-07-10 18:58:52Z mz $)

	Released under the GNU General Public License
	---------------------------------------------------------------------------------------*/

//define('MODULE_PAYMENT_PAYONE_TEXT_TITLE', 'PayOne');
//define('MODULE_PAYMENT_PAYONE_TEXT_DESCRIPTION', 'PayOne lorem ipsum');
//define('MODULE_PAYMENT_PAYONE_TEXT_INFO', 'PayOne ...');
define('MODULE_PAYMENT_PAYONE_STATUS_TITLE', 'Modul aktivieren');
define('MODULE_PAYMENT_PAYONE_STATUS_DESC', 'M&ouml;chten Sie Zahlungen &uuml;ber dieses Modul akzeptieren?');
define('MODULE_PAYMENT_PAYONE_ALLOWED_TITLE', 'Erlaubte Zonen');
define('MODULE_PAYMENT_PAYONE_ALLOWED_DESC', 'Geben Sie <b>einzeln</b> die Zonen an, welche f&uuml;r dieses Modul erlaubt sein sollen. (z.B. AT,DE (wenn leer, werden alle Zonen erlaubt))');
define('MODULE_PAYMENT_PAYONE_ZONE_TITLE', 'Zahlungszone');
define('MODULE_PAYMENT_PAYONE_ZONE_DESC', 'Wenn eine Zone ausgew&auml;hlt ist, gilt die Zahlungsmethode nur f&uuml;r diese Zone.');
define('MODULE_PAYMENT_PAYONE_TMPORDER_STATUS_ID_TITLE', 'Tempor&auml;ren Bestellstatus festlegen');
define('MODULE_PAYMENT_PAYONE_TMPORDER_STATUS_ID_DESC', 'Bestellungen, welche mit diesem Modul gemacht werden, auf diesen Status setzen (w&auml;hrend des laufenden Zahlungsvorgangs)');
define('MODULE_PAYMENT_PAYONE_ORDER_STATUS_ID_TITLE', 'Bestellstatus festlegen');
define('MODULE_PAYMENT_PAYONE_ORDER_STATUS_ID_DESC', 'Bestellungen, welche mit diesem Modul gemacht werden, auf diesen Status setzen');
define('MODULE_PAYMENT_PAYONE_SORT_ORDER_TITLE', 'Anzeigereihenfolge');
define('MODULE_PAYMENT_PAYONE_SORT_ORDER_DESC', 'Reihenfolge der Anzeige. Kleinste Ziffer wird zuerst angezeigt.');
define('MODULE_PAYMENT_PAYONE_LP', '<br/><br/><a target="_blank" href="https://www.payone.com/de/plattform-integration/extensions/modified-shop/"><strong>Jetzt PAYONE Konto hier erstellen.</strong></a>');
