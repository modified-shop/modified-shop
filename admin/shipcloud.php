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
              <font color="#d52d53"><strong>„ahead of the pack“ - mit shipcloud immer ein Paket vorab</strong></font><br />
              <br />
              Mit shipcloud beginnt die neue Generation des Paketversands: über den cloud-basierten Service können Online-Händler einfach und unkompliziert mit allen wesentlichen Paketdienstleistern zusammenarbeiten. Unabhängig von der Zahl der zu versendenden Pakete können sich Händler für den jeweils günstigsten Tarif entscheiden. Das gewährleistet Unabhängigkeit gegenüber den einzelnen Versendern, spart Zeit und Geld und ermöglicht es Ihnen, sich wieder auf Ihr Kerngeschäft zu fokussieren.<br />
              <br />
              <img src="images/shipcloud/Ideenkurier_Grafik_No1.jpg" />
              <img src="images/shipcloud/Ideenkurier_Grafik_No2.jpg" /><br />
              <br />
              Mit dem modified-PlugIn „shipcloud" können Sie aus dem modified-Backend heraus Versandetiketten erzeugen. Es werden alle relevanten Paketdienste unterstützt: DHL, UPS, DPD, Hermes, GLS, ILOXX, FedEx und Liefery. Sie brauchen nur einen shipcloud-Account, und können sofort mit dem Paket Ihrer Wahl zu günstigen Konditionen verschicken und verfolgen.<br />
              <br />
              Sollten Sie bereits Verträge mit einem oder mehreren Paketdiensten haben, können Sie diese in shipcloud verwenden und mit ihren eigenen Account-Daten Versandlabels erstellen.<br />
              <br />
              <font color="#91c24f">
              <ul>
                <li style="list-style-type: circle !important;"><strong>Weitere Informationen zu shipcloud finden Sie hier: <a href="http://..." target="_blank"><font style="font-size:12px; color:#56a5cf;"><u><strong>Klick mich!</strong></u></font></a></strong></li>
                <li style="list-style-type: circle !important;"><strong>Informationen zum PlugIn finden Sie hier: <a href="http://..." target="_blank"><font style="font-size:12px; color:#56a5cf;"><u><strong>Klick mich!</strong></u></font></a></strong></li>
              </ul>
              </font>
              <br />
              <font color="#d52d53"><strong>Voraussetzungen / Anforderungen</strong></font>
              <font color="#91c24f">
              <ul>
                <li style="list-style-type: circle !important;">modified eCommerce Shopsoftware, Version 2.00</li>
                <li style="list-style-type: circle !important;">shipcloud Account</li>
              </ul>
              </font>
              <br />
              <font color="#d52d53"><strong>Features / Funktionalitäten</strong></font>
              <br /><br />
              <font color="#91c24f">
              <ul>
                <li style="list-style-type: circle !important;">Erstellung von Versandetiketten für die Paketdienste:<br />DHL, UPS, DPD, Hermes, GLS, ILOXX, FedEx und Liefery</li>
                <li style="list-style-type: circle !important;">Es ist nur ein shipcloud-Account notwendig, alle Paketdienste in einem Account!</li>
                <li style="list-style-type: circle !important;">Direkte Erstellung der Labels aus dem modified-Backend heraus, über die shipcloud-API, kein Hantieren mit CSV-Dateien.</li>
                <li style="list-style-type: circle !important;">Automatische Hinterlegung der Trackingcodes in den Bestelldetails.</li>
                <li style="list-style-type: circle !important;">Automatische Änderung des Bestellstatus nach der Etiketten-Erstellung möglich, schicken Sie z.B. eine E-Mail mit entsprechendem Trackingcode an Ihre Kunden.</li>
                <li style="list-style-type: circle !important;">Automatische Berechnung des Versandgewichts (pauschales Versandgewicht ebenfalls möglich).</li>
                <li style="list-style-type: circle !important;">Automatische Berechnung der Packstück-Anzahl.</li>
                <li style="list-style-type: circle !important;">Stapelverarbeitung – Erstellen Sie beliebig viele Etiketten gleichzeitig.</li>
                <li style="list-style-type: circle !important;">Sendungsverfolgung direkt aus den Bestelldetails möglich.</li>
                <li style="list-style-type: circle !important;">Porto-/ Versandkosten werden in der Etikettenübersicht angezeigt.</li>
                <li style="list-style-type: circle !important;">Ihr eigenes Shop-Logo auf den Versandetiketten.</li>
                <li style="list-style-type: circle !important;">Multi- / Subshop-Fähigkeit – Je Shop können abweichende Daten hinterlegt werden (z.B. der sichtbarer Shopname auf dem Label).</li>
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