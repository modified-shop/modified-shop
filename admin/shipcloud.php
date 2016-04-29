<?php
/* -----------------------------------------------------------------------------------------
   $Id: shipcloud.php 2011-11-24 modified-shop $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(configuration.php,v 1.40 2002/12/29); www.oscommerce.com
   (c) 2003   nextcommerce (configuration.php,v 1.16 2003/08/19); www.nextcommerce.org
   (c) 2003 XT-Commerce - community made shopping http://www.xt-commerce.com ($Id: configuration.php 1125 2005-07-28 09:59:44Z novalis $)
   (c) 2008 Gambio OHG (gm_trusted_info.php 2008-08-10 gambio)

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

require('includes/application_top.php');
require (DIR_WS_INCLUDES.'head.php');
?>
  </head>
<body>
  <!-- header //-->
  <?php require(DIR_WS_INCLUDES . 'header.php'); ?>
  <!-- header_eof //-->
  <!-- body //-->
  <table class="tableBody">
    <tr>
      <?php //left_navigation
      if (USE_ADMIN_TOP_MENU == 'false') {
        echo '<td class="columnLeft2">'.PHP_EOL;
        echo '<!-- left_navigation //-->'.PHP_EOL;
        require_once(DIR_WS_INCLUDES . 'column_left.php');
        echo '<!-- left_navigation eof //-->'.PHP_EOL;
        echo '</td>'.PHP_EOL;
      }
      ?>
      <!-- body_text //-->
      <td class="boxCenter">
        <div class="pageHeadingImage"><?php echo xtc_image(DIR_WS_ICONS.'heading/icon_modules.png'); ?></div>
        <div class="pageHeading pdg2">shipcloud</div>
        <div class="main">Modules</div>
        <table class="tableCenter">
          <tr>
            <td valign="middle" class="dataTableHeadingContent" style="width:250px;">
              Send. Track. Analyze.
            </td>
            <td valign="middle" class="dataTableHeadingContent">
              <a href="<?php echo xtc_href_link('module_export.php', 'set=system&module=shipcloud'); ?>"><u>Einstellungen</u></a>
            </td>
          </tr>
          <tr style="background-color: #FFFFFF;">
            <td colspan="2" style="font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 12px; padding: 0px 10px 11px 10px; text-align: justify">
              <br />
              <img src="images/shipcloud/shipcloud_191x38.png" /><br />
              <br />
              <font color="#d52d53"><strong>&bdquo;ahead of the pack&ldquo; - mit shipcloud immer ein Paket voraus</strong></font><br />
              <br />
              Mit shipcloud beginnt die neue Generation des Paketversands: &uuml;ber den cloud-basierten Service k&ouml;nnen Online-H&auml;ndler einfach und unkompliziert mit allen wesentlichen Paketdienstleistern zusammenarbeiten. Unabh&auml;ngig von der Zahl der zu versendenden Pakete k&ouml;nnen sich H&auml;ndler f&uuml;r den jeweils g&uuml;nstigsten Tarif entscheiden. Das gew&auml;hrleistet Unabh&auml;ngigkeit gegen&uuml;ber den einzelnen Versendern, spart Zeit und Geld und erm&ouml;glicht es Ihnen, sich wieder auf Ihr Kerngesch&auml;ft zu fokussieren.<br />
              <br />
              <img src="images/shipcloud/Ideenkurier_Grafik_No1.jpg" />
              <img src="images/shipcloud/Ideenkurier_Grafik_No2.jpg" /><br />
              <br />
              Mit dem Modul &bdquo;shipcloud&ldquo; k&ouml;nnen Sie aus dem Backend Ihrer modified eCommerce Shopsoftware heraus Versandetiketten erzeugen. Es werden alle relevanten Paketdienste unterst&uuml;tzt: DHL, UPS, DPD, Hermes, GLS, ILOXX, FedEx und Liefery. Sie brauchen nur einen shipcloud-Account, und k&ouml;nnen sofort mit dem Paket Ihrer Wahl zu g&uuml;nstigen Konditionen verschicken und verfolgen.<br />
              <br />
              Sollten Sie bereits Vertr&auml;ge mit einem oder mehreren Paketdiensten haben, k&ouml;nnen Sie diese in shipcloud verwenden und mit ihren eigenen Account-Daten Versandlabels erstellen.<br />
              <br />
              <font color="#91c24f">
              <ul>
                <li style="list-style-type: circle !important;"><strong>Weitere Informationen zu shipcloud finden Sie hier: <a href="https://www.shipcloud.io/de/lp/modified/?pc=modified" target="_blank"><font style="font-size:12px; color:#56a5cf;"><u><strong>Klick mich!</strong></u></font></a></strong></li>
              </ul>
              </font>
              <br />
              <font color="#d52d53"><strong>Voraussetzungen / Anforderungen</strong></font>
              <font color="#91c24f">
              <ul>
                <li style="list-style-type: circle !important;"><a href="https://www.shipcloud.io/de/lp/modified/?pc=modified" target="_blank"><font style="font-size:12px; color:#56a5cf;"><u><strong>shipcloud Account</strong></u></font></a></li>
              </ul>
              </font>
              <br />
              <font color="#d52d53"><strong>Features / Funktionalit&auml;ten</strong></font>
              <br /><br />
              <font color="#91c24f">
              <ul>
                <li style="list-style-type: circle !important;">Erstellung von Versandetiketten f&uuml;r die Paketdienste:<br />DHL, UPS, DPD, Hermes, GLS, ILOXX, FedEx und Liefery</li>
                <li style="list-style-type: circle !important;">Es ist nur ein shipcloud-Account notwendig, alle Paketdienste in einem Account!</li>
                <li style="list-style-type: circle !important;">Direkte Erstellung der Labels aus dem Backend der modified eCommerce Shopsoftware heraus, &uuml;ber die shipcloud-API, kein Hantieren mit CSV-Dateien.</li>
                <li style="list-style-type: circle !important;">Automatische Hinterlegung der Trackingcodes in den Bestelldetails.</li>
                <li style="list-style-type: circle !important;">Automatische &Auml;nderung des Bestellstatus nach der Etiketten-Erstellung m&ouml;glich, schicken Sie z.B. eine E-Mail mit entsprechendem Trackingcode an Ihre Kunden.</li>
                <?php /*
                <li style="list-style-type: circle !important;">Automatische Berechnung des Versandgewichts (pauschales Versandgewicht ebenfalls m&ouml;glich).</li>
                <li style="list-style-type: circle !important;">Automatische Berechnung der Packst&uuml;ck-Anzahl.</li>
                <li style="list-style-type: circle !important;">Stapelverarbeitung &ndash; Erstellen Sie beliebig viele Etiketten gleichzeitig.</li>
                */ ?>
                <li style="list-style-type: circle !important;">Sendungsverfolgung direkt aus den Bestelldetails m&ouml;glich.</li>
                <li style="list-style-type: circle !important;">Porto-/ Versandkosten werden in der Etiketten&uuml;bersicht angezeigt.</li>
                <li style="list-style-type: circle !important;">Ihr eigenes Shop-Logo auf den Versandetiketten.</li>
                <li style="list-style-type: circle !important;">Multi- / Subshop-F&auml;higkeit &ndash; Je Shop k&ouml;nnen abweichende Daten hinterlegt werden (z.B. der sichtbarer Shopname auf dem Label).</li>
              </ul>
              </font>
            </td>
          </tr>
        </table>
      </td>
      <!-- body_text_eof //-->
    </tr>
  </table>
  <!-- body_eof //-->
  <!-- footer //-->
  <?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
  <!-- footer_eof //-->
  <br />
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>