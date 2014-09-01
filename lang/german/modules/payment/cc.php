<?php
/*------------------------------------------------------------------------------
  $Id: cc.php 1136 2005-08-07 13:19:54Z mz $

  XTC-CC - Contribution for XT-Commerce http://www.xt-commerce.com
  modified by http://www.netz-designer.de

  Copyright (c) 2003 netz-designer
  -----------------------------------------------------------------------------
  based on:
  $Id: cc.php 1136 2005-08-07 13:19:54Z mz $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2002 osCommerce

  Released under the GNU General Public License
------------------------------------------------------------------------------*/

define('MODULE_PAYMENT_CC_TEXT_TITLE', 'Kreditkarte');
define('MODULE_PAYMENT_CC_TEXT_DESCRIPTION', 'Kreditkarte Testinfo:<br /><br />CC#: 4111111111111111<br />G&uuml;ltig bis: Jederzeit');
define('MODULE_PAYMENT_CC_TEXT_INFO', 'Bezahlen Sie bequem und Sicher per Kreditkarte.');
define('MODULE_PAYMENT_CC_TEXT_CREDIT_CARD_TYPE', 'Kreditkarten-Typ:');
define('MODULE_PAYMENT_CC_TEXT_CREDIT_CARD_OWNER', 'Kreditkarten-Inhaber:');
define('MODULE_PAYMENT_CC_TEXT_CREDIT_CARD_NUMBER', 'Kreditkarten-Nummer:');
define('MODULE_PAYMENT_CC_TEXT_CREDIT_CARD_START', 'Kreditkarten-Startdatum:');
define('MODULE_PAYMENT_CC_TEXT_CREDIT_CARD_EXPIRES', 'Kreditkarten-G&uuml;ltigkeitsdatum:');
define('MODULE_PAYMENT_CC_TEXT_CREDIT_CARD_ISSUE', 'Kreditkarten-Vorgangsnummer:');
define('MODULE_PAYMENT_CC_TEXT_CREDIT_CARD_CVV', '3-  oder 4-stelliger Sicherheitscode:');
define('MODULE_PAYMENT_CC_TEXT_JS_CC_OWNER', '* Der Name des Karteninhabers muss aus mindestens'.CC_OWNER_MIN_LENGTH.' ZEichen bestehen.\n\n');
define('MODULE_PAYMENT_CC_TEXT_JS_CC_NUMBER', '* Die Kreditkartennummer muss aus mindestens '.CC_NUMBER_MIN_LENGTH.' Ziffern bestehen.\n\n');
define('MODULE_PAYMENT_CC_TEXT_ERROR', 'Kreditkartenfehler!');
define('TEXT_CARD_NOT_ACZEPTED', 'Die Zahlung mit einer <b>%s</b> Karte ist nicht m&ouml;glich, bitte verwenden Sie eine andere Karte!<br />Folgende Karten werden von uns Akzeptiert: ');
define('MODULE_PAYMENT_CC_TEXT_JS_CC_CVV', '* Der CVV Sicherheitscode ist ein Pflichtfeld und muss ausgef&uuml;llt werden.\n Bestellungen k&ouml;nnen ohne diesen Code nicht ausgef&uuml;hrt werden.\n Der CVV Code besteht aus 3 oder 4 (American Express) Ziffern und ist im Unterschriftsfeld\n auf der R&uuml;ckseite Ihrer Karte gedruckt.\n\n');
define('MODULE_PAYMENT_CC_TEXT_CVV_LINK', '<u>[Hilfe?]</u>');
define('HEADING_CVV', 'Sicherheitscode Infoseite');
define('TEXT_CVV', '<table align="center" cellspacing="2" cellpadding="5" width="400"><tr><td><span class="tableHeading"><b>Visa, Mastercard, Discover und Andere mit 3-stelligem CVV-Code</b></span></td></tr><tr><td><span class="boxText">F&uuml;r Ihre eigene Sicherheit ist die Angabe Ihres Sicherheitscodes verpflichtend. Die Sicherheitsnummer ist eine 3stellige Nummer, die im Unterschriftsfeld auf der R&uuml;ckseite Ihrer Kreditkarte gedruckt ist. Sie erscheint nach den letzten vier Stellen Ihrer Kartennummer auf der rechten Seite.</span></td></tr><tr><td align="center"><IMG src="images/cv_card.gif"></td></tr></table><hr /><table align="center" cellspacing="2" cellpadding="5" width="400"><tr><td><span class="main"><b>American Express 4-stelliger CVV-Code</b> </span></td></tr><tr><td><span class="boxText">F&uuml;r Ihre eigene Sicherheit ist die Angabe Ihres Sicherheitscodes verpflichtend. Der American Express Sicherheitscode ist eine 4-stellige Nummer auf Ihrer Kartenvorderseite. Sie erscheint nach den letzten vier Stellen Ihrer Kartennummer auf der rechten Seite.</span></td></tr><tr><td align="center"><IMG src="images/cv_amex_card.gif"></td></tr></table>');
define('TEXT_CLOSE_WINDOW', '<u>Fenster schliessen</u> [x]');
define('MODULE_PAYMENT_CC_ACCEPTED_CARDS', 'Wir akzeptieren folgende Kreditkarten:');
define('MODULE_PAYMENT_CC_STATUS_TITLE', 'Kreditkartenmodul aktivieren');
define('MODULE_PAYMENT_CC_STATUS_DESC', 'M&ouml;chten Sie Zahlungen per Kreditkarte akzeptieren?');
define('MODULE_PAYMENT_CC_ALLOWED_TITLE', 'Erlaubte Zonen');
define('MODULE_PAYMENT_CC_ALLOWED_DESC', 'Geben Sie <b>einzeln</b> die Zonen an, welche f&uuml;r dieses Modul erlaubt sein sollen. (z.B. AT,DE (wenn leer, werden alle Zonen erlaubt))');
define('CC_VAL_TITLE', 'Karten&uuml;berpr&uuml;fung einschalten');
define('CC_VAL_DESC', 'M&ouml;chten Sie die Kreditkartenangaben &uuml;berpr&uuml;fen und Karten identifizieren?');
define('CC_BLACK_TITLE', 'KK-Blackliste aktivieren');
define('CC_BLACK_DESC', 'M&ouml;chten Sie die KK-Blackliste aktivieren, um dort hinterlegte Kreditkarten abzulehnen?');
define('CC_ENC_TITLE', 'Kreditkarteninfo verschl&uuml;sseln');
define('CC_ENC_DESC', 'M&ouml;chten Sie Kreditkarteninfos verschl&uuml;sseln?');
define('MODULE_PAYMENT_CC_SORT_ORDER_TITLE', 'Anzeigereihenfolge');
define('MODULE_PAYMENT_CC_SORT_ORDER_DESC', 'Reihenfolge der Anzeige. Kleinste Ziffer wird zuerst angezeigt.');
define('MODULE_PAYMENT_CC_ZONE_TITLE', 'Zahlungszone');
define('MODULE_PAYMENT_CC_ZONE_DESC', 'Wenn eine Zone ausgew&auml;hlt ist, gilt die Zahlungsmethode nur f&uuml;r diese Zone.');
define('MODULE_PAYMENT_CC_ORDER_STATUS_ID_TITLE', 'Bestellstatus festlegen');
define('MODULE_PAYMENT_CC_ORDER_STATUS_ID_DESC', 'Bestellungen, welche mit diesem Modul gemacht werden, auf diesen Status setzen');
define('USE_CC_CVV_TITLE', 'CVV Nummer hinterlegen');
define('USE_CC_CVV_DESC', 'M&ouml;chten Sie die CVV Nummer aufnehmen?');
define('USE_CC_ISS_TITLE', 'Vorgangsnummer hinterlegen');
define('USE_CC_ISS_DESC', 'M&ouml;chten Sie die Vorgangsnummer aufnehmen?');
define('USE_CC_START_TITLE', 'Startdatum hinterlegen');
define('USE_CC_START_DESC', 'M&ouml;chten Sie das Startdatums aufnehmen?');
define('CC_CVV_MIN_LENGTH_TITLE', 'L&auml;nge der CVV Nummer');
define('CC_CVV_MIN_LENGTH_DESC', 'L&auml;nge der CVV angeben. Der Standard ist 3 und sollte nicht ge&auml;ndert werden, bis ein neuer Industrie-Standard ausgegeben wurde.');
define('MODULE_PAYMENT_CC_EMAIL_TITLE', 'Kartensplit E-Mail Adresse');
define('MODULE_PAYMENT_CC_EMAIL_DESC', 'Wenn eine E-Mailadresse angegeben worden ist, werden die mittleren Ziffern der Kreditkarten Nummer an diese Adresse gesandt. (Die &auml;usseren Ziffern werden in der Datenbank gespeichert, und die Mittleren darin werden zensiert.');
define('TEXT_CCVAL_ERROR_INVALID_DATE', 'Das "G&uuml;ltig bis" Datum ist ung&uuml;ltig. Bitte korrigieren Sie Ihre Angaben.');
define('TEXT_CCVAL_ERROR_INVALID_NUMBER', 'Die "Kreditkarten-Nummer", die Sie angegeben haben, ist ung&uuml;ltig. Bitte korrigieren Sie Ihre Angaben.');
define('TEXT_CCVAL_ERROR_UNKNOWN_CARD', 'Die ersten 4 Ziffern Ihrer Kreditkarte sind: %s Wenn diese Angaben stimmen, wird dieser Kartentyp leider nicht akzeptiert. Bitte korrigieren Sie Ihre Angaben gegebenfalls.');

define('MODULE_PAYMENT_CC_ACCEPT_DINERSCLUB_TITLE', 'DINERS CLUB akzeptieren');
define('MODULE_PAYMENT_CC_ACCEPT_DINERSCLUB_DESC', 'DINERS CLUB akzeptieren');
define('MODULE_PAYMENT_CC_ACCEPT_AMERICANEXPRESS_TITLE', 'AMERICAN EXPRESS akzeptieren');
define('MODULE_PAYMENT_CC_ACCEPT_AMERICANEXPRESS_DESC', 'AMERICAN EXPRESS akzeptieren');
define('MODULE_PAYMENT_CC_ACCEPT_CARTEBLANCHE_TITLE', 'CARTE BLANCHE akzeptieren');
define('MODULE_PAYMENT_CC_ACCEPT_CARTEBLANCHE_DESC', 'CARTE BLANCHE akzeptieren');
define('MODULE_PAYMENT_CC_ACCEPT_OZBANKCARD_TITLE', 'AUSTRALIAN BANKCARD akzeptieren');
define('MODULE_PAYMENT_CC_ACCEPT_OZBANKCARD_DESC', 'AUSTRALIAN BANKCARD akzeptieren');
define('MODULE_PAYMENT_CC_ACCEPT_DISCOVERNOVUS_TITLE', 'DISCOVER/NOVUS akzeptieren');
define('MODULE_PAYMENT_CC_ACCEPT_DISCOVERNOVUS_DESC', 'DISCOVER/NOVUS akzeptieren');
define('MODULE_PAYMENT_CC_ACCEPT_DELTA_TITLE', 'DELTA akzeptieren');
define('MODULE_PAYMENT_CC_ACCEPT_DELTA_DESC', 'DELTA akzeptieren');
define('MODULE_PAYMENT_CC_ACCEPT_ELECTRON_TITLE', 'ELECTRON akzeptieren');
define('MODULE_PAYMENT_CC_ACCEPT_ELECTRON_DESC', 'ELECTRON akzeptieren');
define('MODULE_PAYMENT_CC_ACCEPT_MASTERCARD_TITLE', 'MASTERCARD akzeptieren');
define('MODULE_PAYMENT_CC_ACCEPT_MASTERCARD_DESC', 'MASTERCARD akzeptieren');
define('MODULE_PAYMENT_CC_ACCEPT_SWITCH_TITLE', 'SWITCH akzeptieren');
define('MODULE_PAYMENT_CC_ACCEPT_SWITCH_DESC', 'SWITCH akzeptieren');
define('MODULE_PAYMENT_CC_ACCEPT_SOLO_TITLE', 'SOLO akzeptieren');
define('MODULE_PAYMENT_CC_ACCEPT_SOLO_DESC', 'SOLO akzeptieren');
define('MODULE_PAYMENT_CC_ACCEPT_JCB_TITLE', 'JCB akzeptieren');
define('MODULE_PAYMENT_CC_ACCEPT_JCB_DESC', 'JCB akzeptieren');
define('MODULE_PAYMENT_CC_ACCEPT_MAESTRO_TITLE', 'MAESTRO akzeptieren');
define('MODULE_PAYMENT_CC_ACCEPT_MAESTRO_DESC', 'MAESTRO akzeptieren');
define('MODULE_PAYMENT_CC_ACCEPT_VISA_TITLE', 'VISA akzeptieren');
define('MODULE_PAYMENT_CC_ACCEPT_VISA_DESC', 'VISA akzeptieren');
?>