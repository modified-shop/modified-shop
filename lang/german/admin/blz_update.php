<?php
/* --------------------------------------------------------------
   $Id: blz_update.php 3499 2012-08-23 09:12:40Z dokuman $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------

   Released under the GNU General Public License
   --------------------------------------------------------------*/

define('HEADING_TITLE', 'Bankleitzahlen von der deutschen Bundesbank aktualisieren');
define('BLZ_INFO_TEXT', '<p>Dieses Formular aktualisiert die Bankleitzahlentabelle der modified eCommerce Shopsoftware. Die BLZ-Tabelle wird bei der Bestellung zur &Uuml;berpr&uuml;fung der Bankdaten verwendet.<br/>Die Bundesbank stellt alle 3 Monate aktualisierte Dateien zur Verf&uuml;gung.</p><p><strong>Updatehinweise:</strong></p><p>Zur Aktualisierung bitte die <a href="http://www.bundesbank.de/Redaktion/DE/Standardartikel/Kerngeschaeftsfelder/Unbarer_Zahlungsverkehr/bankleitzahlen_download.html" target="_blank"><strong>BLZ-Downloadseite der Bundesbank</strong></a> in einem weiteren Browser-Tab &ouml;ffnen. Im unteren Drittel der Bundesbank-Webseite steht unter der &Uuml;berschrift "Bankleitzahlendateien ungepackt" auch ein Downloadlink f&uuml;r die aktuelle BLZ-Datei im Textformat (TXT) zur Verf&uuml;gung. Diesen Link kopieren (Mausklick mit rechter Maustaste auf den Link, dann Link-Adresse kopieren) und dann hier ins Editierfeld eintragen.</p><p>Der Button "Aktualisieren" startet den Aktualisierungsvorgang.<br/>Die Aktualisierung dauert einige Sekunden.</p><p><i>Beispiel-Link f&uuml;r den Zeitraum vom 03.09.2012 bis 02.12.2012:</i></p>');
define('BLZ_LINK_NOT_GIVEN_TEXT', '<span class="messageStackError">Es wurde kein Weblink zur BLZ-Datei der deutschen Bundesbank angegeben!</span><br /><br />');
define('BLZ_LINK_INVALID_TEXT', '<span class="messageStackError">Ung&uuml;ltiger Weblink zur BLZ-Datei.<br/><br/>Nur TXT-Dateien von der Homepage der Bundesbank (www.bundesbank.de) sind zul&auml;ssig!</span><br /><br />');
define('BLZ_DOWNLOADED_COUNT_TEXT', 'Anzahl <u>eindeutig</u> erkannter Bankleitzahlen (ohne Dubletten!)');
define('BLZ_PHP_FILE_ERROR_TEXT', '<p><strong><span class="messageStackError">Der PHP-Parameter "allow_url_fopen" ist deaktiviert ("off"). Dieser ist f&uuml;r die PHP-Funktion <i>file( )</i> notwendig. Um die Aktualisierung automatisch auszuf&uuml;hren, m&uuml;ssen Sie den Parameter auf "on" stellen (lassen).</span></strong></p>');
define('BLZ_UPDATE_SUCCESS_TEXT', ' Datens&auml;tze erfolgreich in der Datenbank gespeichert!');
define('BLZ_UPDATE_ERROR_TEXT', 'Es ist ein Fehler aufgetreten!');
define('BLZ_LINK_ERROR_TEXT', '<span class="messageStackError">Der von Ihnen angegebene Link existiert nicht! Bitte &uuml;berpr&uuml;fen Sie die Eingabe im Editierfeld auf der vorherigen Seite.</span>');
define('BLZ_LINES_PROCESSED_TEXT',' Bankleitzahl-Datens&auml;tze eingelesen.');
define('BLZ_SOURCE_TEXT','Quelle: ');
?>