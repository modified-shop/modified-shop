<?php
$prefix = 'MODULE_ORDER_TOTAL_BILLPAY_';

  // config options
  define($prefix.'FEE_TITLE', 'Invoice surcharge (BillPay)');
  define($prefix.'FEE_DESCRIPTION', 'Additional surcharge for using this payment method');

  define($prefix.'FEE_STATUS_TITLE','Enabled?');
  define($prefix.'FEE_STATUS_DESC','');

  define($prefix.'FEE_SORT_ORDER_TITLE','Sort order');
  define($prefix.'FEE_SORT_ORDER_DESC','');

  define($prefix.'FEE_TYPE_TITLE','Fee type');
  define($prefix.'FEE_TYPE_DESC','Fee may be fixed amount (fest) or percentage of an order (prozentual).');

  define($prefix.'FEE_PERCENT_TITLE','Percentage');
  define($prefix.'FEE_PERCENT_DESC','Enter percent rates for each country (i.e. DE:5;CH:7).');

  define($prefix.'FEE_VALUE_TITLE','Fixed amount');
  define($prefix.'FEE_VALUE_DESC','Enter fixed amount for each country (i.e. DE:5;CH:7).');

  define($prefix.'FEE_GRADUATE_TITLE','Staffelung');
  define($prefix.'FEE_GRADUATE_DESC','Geben Sie hier die Geb&uuml;hrenstaffelung in der Form {Rechnungssumme}={Nettogeb&uuml;hr};{Rechnungssumme}={Nettogeb&uuml;hr}; ein. Diese Staffelung wird auf die Rechnungssumme erhoben, falls der Geb&uuml;hrtyp "Staffelung" aktiviert ist.');

  define($prefix.'FEE_TAX_CLASS_TITLE','Tax class');
  define($prefix.'FEE_TAX_CLASS_DESC','Choose a tax class for the fee');

  // display
  defined($prefix.'FEE_FROM_TOTAL') OR define($prefix.'FEE_FROM_TOTAL', 'of order amount');
