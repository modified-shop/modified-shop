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

define('MODULE_ORDER_TOTAL_PAYMENT_HELP_LINK', ' <a onclick="window.open(\'popup_help.php?type=order_total&modul=ot_payment&lng=english\', \'HELP\', \'scrollbars=yes,resizable=yes,menubar=yes,width=800,height=600\'); return false" target="_blank" href="popup_help.php?type=order_total&modul=ot_payment&lng=english"><b>[HELP]</b></a>');
define('MODULE_ORDER_TOTAL_PAYMENT_HELP_TEXT', '<h2>Payment type discount &amp; surcharge</h2>
If there are more scale of discounts required (default is <b>3</b>), the value of the variable $num (language files) and $this->num (module file) has to be changed to the desired value.
A later increase requires a deinstallation/re-installation of the module or a manual database correction!
<hr>
<h3>Fields scale of discount</h3>
<p class="red">Notice: the shipping address is crucial for the country codes. If a discount/surcharge should be valid for all countries, the code 00 has to be used or the country code has to be omitted completely (inclusive the &quot;|&quot;)!</p>
<h4>For discounts use the following notation:</h4>
<pre>   <span class="blue">DE</span>|<span class="green">100</span>:<span class="red">4</span>,<span class="green">200</span>:<span class="red">5</span></pre>
<p>Meaning:</p>
<p>For customers from <span class="blue">Germany</span>  with an order volume from <span class="green">100&euro;</span> a discount of <span class="red">4%</span> will be granted, from <span class="green">200&euro;</span> a discount of <span class="red">5%</span> will be granted.</p>
<pre>   <span class="green">100</span>:<span class="red">2</span>,<span class="green">200</span>:<span class="red">3</span></pre>
<p>Meaning:</p>
<p>For customers from all countries with an order volume from <span class="green">100&euro;</span> a discount of <span class="red">2%</span>will be granted, from <span class="green">200&euro;</span> a discount of <span class="red">3%</span> will be granted.</p>
<h4>For surcharges use the following notation:</h4>
<pre>   <span class="blue">DE</span>|<span class="green">100</span>:<span class="red">-3</span></pre>
<p>Meaning:</p>
<p>For customers from <span class="blue">Germany</span> with an order volume from <span class="green">100&euro;</span> a surcharge of <span class="red">3%</span> will be added.</p>
<h4>Example for Paypal</h4>
1. scale of discount
<pre>   <span class="blue">DE</span>|<span class="green">0</span>:<span class="red">-1.9</span>&amp;<span class="lila">-0.35</span></pre>
2. scale of discount
<pre>   <span class="blue">00</span>|<span class="green">0</span>:<span class="red">-3.4</span>&amp;<span class="lila">-0.35</span></pre>
<p>Meaning:</p>
<p>For customers from <span class="blue">Germany</span> with an order volume from <span class="green">0&euro;</span> (i.e. always) a surcharge of <span class="red">1,9%</span> plus <span class="lila">0,35&euro;</span> will be added.</p>
<p>For customers from <span class="blue">all other countries (00=all)</span> with an order volume from <span class="green">0&euro;</span> a surcharge of <span class="red">3,4%</span> plus <span class="lila">0,35&euro;</span> will be added.</p>
<p>Important is the sequence of the entries (all other countries always at last) and that  &quot;Multiple calculation&quot; is set to &quot;false&quot;, otherwise both surcharges will be added.</p>
<h4>Example for fixed amounts</h4>
<pre>   <span class="green">0</span>:<span class="red">0</span>&amp;<span class="lila">-2</span></pre>
<p>Meaning:</p>
<p>For customers from all countries with an order volume from <span class="green">0&euro;</span> (i.e. always) a surcharge of <span class="red">0%</span> (so no percentage surcharge) plus <span class="lila">2,00&euro;</span> (fixed surcharge) will be added.</p>
<hr>
<h3>Fields payment type</h3>
<p>Enter the <b>internal usage code</b> of the payment type, e.g. <b>moneyorder</b> for Check/Money Order or <b>cod</b> for Cash on delivery. More payment types have to be separated by comma.</p>
See also Modules -&gt; Payment systems -&gt; column &quot;Modulname (for internal usage)&quot;.<br/><br/>
<hr>
<h3>Display in checkout payment during order process</h3>
If the appropriate discount should be shown during order process when a payment has to be selected, set the option &quot;Display in payment types&quot; to &quot;true&quot;. <br/><br/>
Weiter kann man mit der Option &quot;Anzeigeart im Bestellprozess bei der Zahlungsauswahl&quot; die Art der Darstellung einstellen:
<p> -- default: percent or amount, dependent on the entries in the scale of discounts</p>
<p> -- price: always show the actual amount</p>'
);

define('MODULE_ORDER_TOTAL_PAYMENT_TITLE', 'Payment type discount &amp; surcharge');
define('MODULE_ORDER_TOTAL_PAYMENT_DESCRIPTION', 'Payment type discount &amp; surcharge'.MODULE_ORDER_TOTAL_PAYMENT_HELP_LINK);

define('MODULE_ORDER_TOTAL_PAYMENT_STATUS_TITLE', 'Display discount');
define('MODULE_ORDER_TOTAL_PAYMENT_STATUS_DESC', 'Do you want to enable the order discount');

define('MODULE_ORDER_TOTAL_PAYMENT_SORT_ORDER_TITLE', '<hr>Sort order');
define('MODULE_ORDER_TOTAL_PAYMENT_SORT_ORDER_DESC', 'Sort order of display');

for ($j=1; $j<=MODULE_ORDER_TOTAL_PAYMENT_NUMBER; $j++) {
  define('MODULE_ORDER_TOTAL_PAYMENT_PERCENTAGE' . $j . '_TITLE', $j . '. Discount percentage');
  define('MODULE_ORDER_TOTAL_PAYMENT_PERCENTAGE' . $j . '_DESC', 'Amount of discount(countries|value:percentage&fee)');
  define('MODULE_ORDER_TOTAL_PAYMENT_TYPE' . $j . '_TITLE', $j . '. Payment type');
  define('MODULE_ORDER_TOTAL_PAYMENT_TYPE' . $j . '_DESC', 'Payment type to get discount');
}

define('MODULE_ORDER_TOTAL_PAYMENT_INC_SHIPPING_TITLE', '<hr>Include shipping');
define('MODULE_ORDER_TOTAL_PAYMENT_INC_SHIPPING_DESC', 'Include shipping in calculation');

define('MODULE_ORDER_TOTAL_PAYMENT_INC_TAX_TITLE', '<hr>Include tax');
define('MODULE_ORDER_TOTAL_PAYMENT_INC_TAX_DESC', 'Include tax in calculation');

define('MODULE_ORDER_TOTAL_PAYMENT_CALC_TAX_TITLE', '<hr>Calculate tax');
define('MODULE_ORDER_TOTAL_PAYMENT_CALC_TAX_DESC', 'Re-calculate tax on discounted amount');

define('MODULE_ORDER_TOTAL_PAYMENT_ALLOWED_TITLE', '<hr>Allowed zones');
define('MODULE_ORDER_TOTAL_PAYMENT_ALLOWED_DESC' , 'Please enter the zones <b>separately</b> which should be allowed to use this modul (e. g. AT,DE (leave empty if you want to allow all zones))');

define('MODULE_ORDER_TOTAL_PAYMENT_DISCOUNT', 'Discount');
define('MODULE_ORDER_TOTAL_PAYMENT_FEE', 'Fee');

define('MODULE_ORDER_TOTAL_PAYMENT_TAX_CLASS_TITLE','<hr>Tax class');
define('MODULE_ORDER_TOTAL_PAYMENT_TAX_CLASS_DESC','Use the following tax class on the low order fee.');

define('MODULE_ORDER_TOTAL_PAYMENT_BREAK_TITLE','<hr>Multiple calculation');
define('MODULE_ORDER_TOTAL_PAYMENT_BREAK_DESC','Should multiple calculation be possible? If false calculation will be stopped after the first fitting setting.');
define('MODULE_ORDER_TOTAL_PAYMENT_SHOW_IN_CHECKOUT_PAYMENT_TITLE', '<hr>Display in payment types');
define('MODULE_ORDER_TOTAL_PAYMENT_SHOW_IN_CHECKOUT_PAYMENT_DESC', 'Display during the checkout process at the checkout');

define('MODULE_ORDER_TOTAL_PAYMENT_SHOW_TYPE_TITLE', '<hr>Display mode of payment types');
define('MODULE_ORDER_TOTAL_PAYMENT_SHOW_TYPE_DESC', 'Display in the ordering process at the checkout <br /> - default: percent or amount, depending on the inputs at discount <br /> - price: it will always show the actual amount');

define('MODULE_ORDER_TOTAL_PAYMENT_NUMBER_TITLE', 'Number Paymenttype');
define('MODULE_ORDER_TOTAL_PAYMENT_NUMBER_DESC', 'Number of Payment type discount &amp; surcharge');
?>