<?php
$prefix = 'MODULE_ORDER_TOTAL_BILLPAY_';

  // config options
  define($prefix.'FEE_TITLE', 'Zahlartenzuschlag Rechnung (BillPay)');
  define($prefix.'FEE_DESCRIPTION', 'Berechnung der Geb&uuml;hr f&uuml;r Bestellungen mit der Zahlart Rechnung (BillPay)');

  define($prefix.'FEE_STATUS_TITLE','Zahlartenzuschlag Rechnungskauf');
  define($prefix.'FEE_STATUS_DESC','Berechnung der Rechnungsgeb&uuml;hr');

  define($prefix.'FEE_SORT_ORDER_TITLE','Sortierreihenfolge');
  define($prefix.'FEE_SORT_ORDER_DESC','Anzeigereihenfolge');

  define($prefix.'FEE_TYPE_TITLE','Geb&uuml;hr Typ');
  define($prefix.'FEE_TYPE_DESC','W&auml;hlen Sie die Art der Geb&uuml;hr. Die Geb&uuml;hr kann als fester Betrag, ein Prozentwert auf die Rechnungssumme oder gestaffelter Betrag erhoben werden.');

  define($prefix.'FEE_PERCENT_TITLE','Prozentsatz');
  define($prefix.'FEE_PERCENT_DESC','Geben Sie hier den Prozentwert als ganze Zahl mit dem Land in das versendet wird ein (Beispiel: DE:5;CH:7). Dieser Prozentwert wird auf die Rechnungssumme erhoben, falls der Geb&uuml;hrtyp "prozentual" aktiviert ist.');

  define($prefix.'FEE_VALUE_TITLE','fester Wert');
  define($prefix.'FEE_VALUE_DESC','Geben Sie hier den festen Wert (netto) mit dem Land in das versendet wird ein (Beispiel: DE:5;CH:7). Dieser Wert wird der Rechnungssumme aufaddiert, falls der Geb&uuml;hrtyp "fest" aktiviert ist.');

  define($prefix.'FEE_GRADUATE_TITLE','Staffelung');
  define($prefix.'FEE_GRADUATE_DESC','Geben Sie hier die Geb&uuml;hrenstaffelung in der Form {Rechnungssumme}={Nettogeb&uuml;hr};{Rechnungssumme}={Nettogeb&uuml;hr}; ein. Diese Staffelung wird auf die Rechnungssumme erhoben, falls der Geb&uuml;hrtyp "Staffelung" aktiviert ist.');

  define($prefix.'FEE_TAX_CLASS_TITLE','Steuerklasse');
  define($prefix.'FEE_TAX_CLASS_DESC','W&auml;hlen Sie eine Steuerklasse.');

  // display
  defined($prefix.'FEE_FROM_TOTAL') OR define($prefix.'FEE_FROM_TOTAL', 'vom Rechnungsbetrag');
