<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

$sofort_code = 'SOFORT_IDEAL';

define('MODULE_PAYMENT_'.$sofort_code.'_TEXT_TITLE', 'iDEAL <br /><img src="https://images.sofort.com/de/ideal/logo_90x30.png" alt="Logo iDEAL"/>');
define('MODULE_PAYMENT_'.$sofort_code.'_TEXT_DESCRIPTION', '<b>iDEAL</b><br />Sobald der Kunde diese Zahlungsart und seine Bank ausgew&auml;hlt hat, wird er durch die SOFORT GmbH auf seine Bank weitergeleitet. Dort t&auml;tigt er seine Zahlung und wird danach wieder auf das Shopsystem zur&uuml;ckgeleitet. Bei erfolgreicher Zahlungsbest&auml;tigung findet durch die SOFORT GmbH ein sog. Callback auf das Shopsystem statt, der den Zahlungsstatus der Bestellung entsprechend &auml;ndert.<br />Bereitgestellt durch die SOFORT GmbH');
define('MODULE_PAYMENT_'.$sofort_code.'_TEXT_INFO', 'iDEAL.nl - Online-&Uuml;berweisungen f&uuml;r den elektronischen Handel in den Niederlanden. F&uuml;r die Bezahlung mit iDEAL ben&ouml;tigen Sie ein Konto bei einer der genannten Banken. Sie nehmen die &Uuml;berweisung direkt bei Ihrer Bank vor. Dienstleistungen/Waren werden bei Verf&uuml;gbarkeit SOFORT geliefert bzw. versendet!');

// checkout
define('MODULE_PAYMENT_'.$sofort_code.'_TEXT_DESCRIPTION_CHECKOUT_PAYMENT_IMAGE', '
  <table border="0" cellspacing="0" cellpadding="0">
    <tr>
      <td valign="bottom">
	      <a onclick="javascript:window.open(\'http://www.ideal.nl\',\'Information\',\'toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=yes, width=1020, height=900\');" style="float:left; width:auto;">{{image}}</a>
	    </td>
	  </tr>
	  <tr>
	    <td class="main">{{text}}</td>
	  </tr>
	</table>');
define('MODULE_PAYMENT_'.$sofort_code.'_TEXT_DESCRIPTION_CHECKOUT_PAYMENT_IMAGEALT', 'iDeal');
define('MODULE_PAYMENT_'.$sofort_code.'_TEXT_DESCRIPTION_CHECKOUT_PAYMENT_TEXT', '
  <ul>
    <li>Online-&Uuml;berweisungen f&uuml;r den elektronischen Handel in den Niederlanden</li>
    <li>F&uuml;r die Bezahlung mit iDEAL ben&ouml;tigen Sie ein Konto bei einer der genannten Banken</li>
    <li>Sie nehmen die &Uuml;berweisung direkt bei Ihrer Bank vor</li>
    <li>Dienstleistungen/Waren werden bei Verf&uuml;gbarkeit SOFORT geliefert bzw. versendet</li>
  </ul>');

define('MODULE_PAYMENT_'.$sofort_code.'_SELECTBOX', 'Bitte wählen Sie Ihre Bank aus');

// admin
define('MODULE_PAYMENT_'.$sofort_code.'_STATUS_TITLE', 'iDeal Modul aktivieren');
define('MODULE_PAYMENT_'.$sofort_code.'_STATUS_DESC', 'M&ouml;chten Sie Zahlungen per iDeal akzeptieren?');

include(DIR_FS_CATALOG.'lang/german/modules/payment/sofort_payment.php');

?>