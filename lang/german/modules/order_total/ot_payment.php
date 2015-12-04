<?php
/* -----------------------------------------------------------------------------------------
   $Id: ot_payment.php 3481 2012-08-22 07:07:50Z dokuman $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (C) 2007 Estelco - Ebusiness & more - http://www.estelco.de
   (C) 2004 IT eSolutions Andreas Zimmermann - http://www.it-esolutions.de

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

define('MODULE_ORDER_TOTAL_PAYMENT_HELP_LINK', ' <a onclick="window.open(\'popup_help.php?type=order_total&modul=ot_payment&lng=german\', \'Hilfe\', \'scrollbars=yes,resizable=yes,menubar=yes,width=800,height=600\'); return false" target="_blank" href="popup_help.php?type=order_total&modul=ot_payment&lng=german"><b>[HILFE]</b></a>');
define('MODULE_ORDER_TOTAL_PAYMENT_HELP_TEXT', '<h2>Rabatt und Zuschlag auf Zahlungsarten</h2>
Sollen mehr Rabattstaffeln m&ouml;glich sein (Standard sind <b>3</b>), muss vor der Installation in allen Dateien der Wert der Variable $num (Sprachdateien) bzw. $this-&gt;num (Moduldatei) auf den gew&uuml;nschten Wert ge&auml;ndert werden.
<hr>
<h3>Felder Rabattstaffel</h3>
<p class="red">Hinweis: entscheidend f&uuml;r die L&auml;ndercodes ist die Lieferanschrift. Soll ein Rabatt/Zuschlag f&uuml;r alle L&auml;nder gelten, so ist entweder 00 als Code zu verwenden oder die L&auml;ndercodeangabe ganz zu unterlassen (inklusive dem &quot;|&quot;)!</p>
<h4>F&uuml;r Rabatte folgende Notation verwenden:</h4>
<pre>   <span class="blue">DE</span>|<span class="green">100</span>:<span class="red">4</span>,<span class="green">200</span>:<span class="red">5</span></pre>
<p>Bedeutung:</p>
<p>F&uuml;r Kunden aus <span class="blue">Deutschland</span> wird ab <span class="green">100&euro;</span> ein Rabatt von <span class="red">4%</span>, ab <span class="green">200&euro;</span> ein Rabatt von <span class="red">5%</span> gew&auml;hrt.</p>
<pre>   <span class="green">100</span>:<span class="red">2</span>,<span class="green">200</span>:<span class="red">3</span></pre>
<p>Bedeutung:</p>
<p>F&uuml;r Kunden aus allen L&auml;ndern wird ab <span class="green">100&euro;</span> ein Rabatt von <span class="red">2%</span>, ab <span class="green">200&euro;</span> ein Rabatt von <span class="red">3%</span> gew&auml;hrt.</p>
<h4>F&uuml;r Zuschl&auml;ge folgende Notation verwenden:</h4>
<pre>   <span class="blue">DE</span>|<span class="green">100</span>:<span class="red">-3</span></pre>
<p>Bedeutung:</p>
<p>F&uuml;r Kunden aus <span class="blue">Deutschland</span> wird ab <span class="green">100&euro;</span> ein Aufschlag von <span class="red">3%</span> berechnet.</p>
<h4>Beispiel f&uuml;r Paypal</h4>
1. Rabattstaffel
<pre>   <span class="blue">DE</span>|<span class="green">0</span>:<span class="red">-1.9</span>&amp;<span class="lila">-0.35</span></pre>
2. Rabattstaffel
<pre>   <span class="blue">00</span>|<span class="green">0</span>:<span class="red">-3.4</span>&amp;<span class="lila">-0.35</span></pre>
<p>Bedeutung:</p>
<p>F&uuml;r Kunden aus <span class="blue">Deutschland</span> wird ab <span class="green">0&euro;</span> (also immer) ein Aufschlag von <span class="red">1,9%</span> zuz&uuml;glich <span class="lila">0,35&euro;</span> berechnet.</p>
<p>F&uuml;r Kunden aus <span class="blue">allen restlichen L&auml;ndern (00=alle)</span> wird ab <span class="green">0&euro;</span> ein Aufschlag von <span class="red">3,4%</span> zuz&uuml;glich <span class="lila">0,35&euro;</span> berechnet.</p>
<p>Wichtig ist hier die Reihenfolge der Eintr&auml;ge (alle restlichen L&auml;nder immer als letztes) und das &quot;Mehrfachberechnung&quot; auf &quot;false&quot; steht, sonst werden beide Zuschl&auml;ge berechnet.</p>
<h4>Beispiel f&uuml;r Festbetr&auml;ge</h4>
<pre>   <span class="green">0</span>:<span class="red">0</span>&amp;<span class="lila">-2</span></pre>
<p>Bedeutung:</p>
<p>F&uuml;r Kunden aus allen L&auml;ndern wird ab <span class="green">0&euro;</span> (also immer) ein Aufschlag von <span class="red">0%</span> (also kein prozentualer Aufschlag) zuz&uuml;glich <span class="lila">2,00&euro;</span> (der feste Aufschlag) berechnet.</p>
<hr>
<h3>Felder Zahlungsart</h3>
<p>In die Felder den <b>internen Code</b> der Zahlungsart eintragen, z.B. <b>moneyorder</b> f&uuml;r Vorkasse oder <b>cod</b> f&uuml;r Nachnahme. Mehrere Zahlungsarten mit Komma trennen</p>Siehe hierzu Module -&gt; Zahlungsoptionen -&gt; Spalte &quot;Modulname (f&uuml;r internen Gebrauch)&quot;.<br/><br/>
<hr>
<h3>Anzeige bei Zahlungsart im Bestellprozess</h3>
Wenn schon im Bestellprozess bei der Zahlungsauswahl der entsprechende Rabatt angezeigt werden soll, die Option &quot;Anzeige bei den Zahlungsarten&quot; auf &quot;true&quot; stellen. <br/><br/>
Weiter kann man mit der Option &quot;Anzeigeart im Bestellprozess bei der Zahlungsauswahl&quot; die Art der Darstellung einstellen:
<p> -- default: Prozent oder Betrag, abh&auml;ngig von den Eingaben bei Rabattstaffel</p>
<p> -- price: Es wird immer der tats&auml;chliche Betrag angezeigt</p>'
);

define('MODULE_ORDER_TOTAL_PAYMENT_TITLE', 'Rabatt &amp; Zuschlag auf Zahlungsarten');
define('MODULE_ORDER_TOTAL_PAYMENT_DESCRIPTION', 'Rabatt und Zuschlag auf Zahlungsarten'.MODULE_ORDER_TOTAL_PAYMENT_HELP_LINK);

define('MODULE_ORDER_TOTAL_PAYMENT_STATUS_TITLE', 'Rabatt anzeigen');
define('MODULE_ORDER_TOTAL_PAYMENT_STATUS_DESC', 'Wollen Sie den Zahlungsartenrabatt einschalten?');

define('MODULE_ORDER_TOTAL_PAYMENT_SORT_ORDER_TITLE', '<hr>Sortierreihenfolge');
define('MODULE_ORDER_TOTAL_PAYMENT_SORT_ORDER_DESC', 'Anzeigereihenfolge');

for ($j=1; $j<=MODULE_ORDER_TOTAL_PAYMENT_NUMBER; $j++) {
  define('MODULE_ORDER_TOTAL_PAYMENT_PERCENTAGE' . $j . '_TITLE', '<hr>'.$j . '. Rabattstaffel');
  define('MODULE_ORDER_TOTAL_PAYMENT_PERCENTAGE' . $j . '_DESC', 'Rabattierung (Mindestwert:Prozent)');
  define('MODULE_ORDER_TOTAL_PAYMENT_TYPE' . $j . '_TITLE', $j . '. Zahlungsart');
  define('MODULE_ORDER_TOTAL_PAYMENT_TYPE' . $j . '_DESC', 'Zahlungsarten, auf die Rabatt gegeben werden soll');
}

define('MODULE_ORDER_TOTAL_PAYMENT_INC_SHIPPING_TITLE', '<hr>Inklusive Versandkosten');
define('MODULE_ORDER_TOTAL_PAYMENT_INC_SHIPPING_DESC', 'Versandkosten werden mit Rabattiert');

define('MODULE_ORDER_TOTAL_PAYMENT_INC_TAX_TITLE', '<hr>Inklusive Ust');
define('MODULE_ORDER_TOTAL_PAYMENT_INC_TAX_DESC', 'Ust wird mit Rabattiert');

define('MODULE_ORDER_TOTAL_PAYMENT_CALC_TAX_TITLE', '<hr>Ust Berechnung');
define('MODULE_ORDER_TOTAL_PAYMENT_CALC_TAX_DESC', 'erneutes berechnen der Ust Summe');

define('MODULE_ORDER_TOTAL_PAYMENT_ALLOWED_TITLE', '<hr>Erlaubte Zonen');
define('MODULE_ORDER_TOTAL_PAYMENT_ALLOWED_DESC' , 'Geben Sie <b>einzeln</b> die Zonen an, welche f&uuml;r dieses Modul erlaubt sein sollen. (z.B. AT,DE (wenn leer, werden alle Zonen erlaubt))');

define('MODULE_ORDER_TOTAL_PAYMENT_DISCOUNT', 'Rabatt');
define('MODULE_ORDER_TOTAL_PAYMENT_FEE', 'Zuschlag');

define('MODULE_ORDER_TOTAL_PAYMENT_TAX_CLASS_TITLE','<hr>Steuerklasse');
define('MODULE_ORDER_TOTAL_PAYMENT_TAX_CLASS_DESC','Die Steuerklasse spielt keine Rolle und dient nur der Vermeidung einer Fehlermeldung.');

define('MODULE_ORDER_TOTAL_PAYMENT_BREAK_TITLE','<hr>Mehrfachberechnung');
define('MODULE_ORDER_TOTAL_PAYMENT_BREAK_DESC','Sollten Mehrfachberechnungen m&ouml;glich sein? Wenn nein, wird nach dem ersten passenden Rabatt abgebrochen.');

define('MODULE_ORDER_TOTAL_PAYMENT_SHOW_IN_CHECKOUT_PAYMENT_TITLE', '<hr>Anzeige bei den Zahlungsarten');
define('MODULE_ORDER_TOTAL_PAYMENT_SHOW_IN_CHECKOUT_PAYMENT_DESC', 'Anzeige im Bestellprozess bei der Zahlungsauswahl');

define('MODULE_ORDER_TOTAL_PAYMENT_SHOW_TYPE_TITLE', '<hr>Anzeigeart bei den Zahlungsarten');
define('MODULE_ORDER_TOTAL_PAYMENT_SHOW_TYPE_DESC', 'Anzeigeart im Bestellprozess bei der Zahlungsauswahl <br />-- default: Prozent oder Betrag, abh&auml;ngig von den Eingaben bei Rabattstaffel<br />-- price: es wird immer der tats&auml;chliche Betrag angezeigt');

define('MODULE_ORDER_TOTAL_PAYMENT_NUMBER_TITLE', 'Anzahl Zahlarten');
define('MODULE_ORDER_TOTAL_PAYMENT_NUMBER_DESC', 'Anzahl an Rabatt &amp; Zuschlag auf Zahlungsarten');
?>