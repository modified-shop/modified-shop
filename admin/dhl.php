<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(configuration.php,v 1.40 2002/12/29); www.oscommerce.com
   (c) 2003   nextcommerce (configuration.php,v 1.16 2003/08/19); www.nextcommerce.org
   (c) 2003 XT-Commerce - community made shopping http://www.xt-commerce.com ($Id$)
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
        <div class="pageHeading pdg2">DHL Versand &amp; Label-Erstellung</div>
        <div class="main">Modules</div>         
        <table class="tableCenter">
          <tr>
            <td valign="middle" class="dataTableHeadingContent" style="width:250px;">
              DHL Anbindung
            </td>
            <td valign="middle" class="dataTableHeadingContent">
              <a href="<?php echo xtc_href_link('module_export.php', 'set=system&module=dhl'); ?>"><u>Einstellungen</u></a>  
            </td>
          </tr>
          <tr style="background-color: #FFFFFF;">
            <td colspan="2" style="font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 12px; padding: 0px 10px 11px 10px; text-align: justify">
              <br />
              <img src="images/dhl/dhl_logo.png" />
              <br />
              <br />
              <strong>DHL ist der Partner f&uuml;r Ihren nationalen und internationalen Versand</strong><br />
              <br />
              F&uuml;r Gesch&auml;ftskunden und Gewerbetreibende jeder Gr&ouml;&szlig;enordnung und Branche ist DHL der Partner f&uuml;r alle Versandaufgaben. Unsere 81.000 Zusteller liefern &uuml;ber 1,6 Mrd. Pakete pro Jahr an Kunden in ganz Deutschland aus.<br />
              <br />
              <strong>ZUVERL&Auml;SSIG LIEFERN</strong><br />
              <br />
              &Uuml;ber 80 Prozent aller DHL Pakete werden am Folgetag zugestellt. F&uuml;r ein wachsendes E-Commerce-Business bauen wir unsere Kapazit&auml;ten kontinuierlich aus.<br />
              <br />
              <strong>NACHHALTIG VERSENDEN</strong><br />
              <br />
              Rund 15.000 StreetScooter mit Elektroantrieb sind in der Zustellung im Einsatz. Der Service GoGreen erm&ouml;glicht erm&ouml;glicht DHL Kunden einen klimaneutralen Versand durch Investitionen in Klimaschutzprojekte.<br />
              <br />
              <strong>FL&Auml;CHENDECKEND ERREICHBAR</strong><br />
              <br />
              Deutschlandweit betreibt DHL &uuml;ber 7.000 Packstationen. Hinzu kommen 30.000 Paketabgabe- und -annahmestellen in der N&auml;he Ihrer Kunden. Flexible Empfangsoptionen und Retourenl&ouml;sungen stehen f&uuml;r maximale Kundenorientierung.<br />
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