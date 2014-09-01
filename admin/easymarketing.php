<?php
/* -----------------------------------------------------------------------------------------
   $Id: easymarketing.php 6027 2013-11-07 11:48:21Z GTB $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

require('includes/application_top.php');

require (DIR_WS_INCLUDES.'head.php');
?>
  </head>
  <body marginwidth="0" marginheight="0" topmargin="0" bottommargin="0" leftmargin="0" rightmargin="0" bgcolor="#FFFFFF">
    <!-- header //-->
    <?php require(DIR_WS_INCLUDES . 'header.php'); ?>
    <!-- header_eof //-->
    <!-- body //-->
    <table border="0" width="100%" cellspacing="2" cellpadding="2">
      <tr>
        <td class="columnLeft2" width="<?php echo BOX_WIDTH; ?>" valign="top">
          <table border="0" width="<?php echo BOX_WIDTH; ?>" cellspacing="1" cellpadding="1" class="columnLeft">
            <!-- left_navigation //-->
            <?php require(DIR_WS_INCLUDES . 'column_left.php'); ?>
            <!-- left_navigation_eof //-->
          </table>
        </td>
        <!-- body_text //-->
        <td class="boxCenter" width="100%" valign="top">
          <table border="0" width="100%" cellspacing="0" cellpadding="2">
            <tr>
              <td width="100%">
                <table border="0" width="100%" cellspacing="0" cellpadding="0">
                  <tr>
                    <td width="80" rowspan="2"><?php echo xtc_image(DIR_WS_ICONS.'heading_modules.gif'); ?></td>
                    <td class="pageHeading">EASYMARKETING</td>
                  </tr>
                  <tr>
                    <td class="main" valign="top">Modules</td>
                  </tr>
                </table>
              </td>
            </tr>
            <tr>
              <td>
                <table border="0" width="100%" cellspacing="0" cellpadding="0">
                  <tr>
                    <td width="250" valign="middle" class="dataTableHeadingContent">
                      Vollautomatisierte Online-Werbung
                    </td>
                    <td valign="middle" class="dataTableHeadingContent"  style="border-right: 0px;">
                      <a href="<?php echo xtc_href_link('module_export.php', 'set=&module=easymarketing'); ?>"><u>Einstellungen</u></a>  
                    </td>
                  </tr>
                </table>
                <table style="border: 1px solid #dddddd" border="0" cellpadding="0" cellspacing="0" width="100%">
                  <tr style="background-color: #FFFFFF;">
                    <td style="font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 12px; padding: 0px 10px 11px 10px; text-align: justify">
                      <br />
                      <font color="#FF7A00"><strong>Vollautomatisiert und optimiert werben auf Google uvm.</strong></font>
                      <a href="https://easymarketing.de/analysis/new?partner=modified" target="_blank"><img src="images/easymarketing/logo-easymarketing.jpg" align="right" /></a>
                      <br />
                      <br />
                      <font color="#FF7A00"><strong>In drei Schritten durchstarten:</strong></font>
                      <ul>
                        <li style="list-style-type: circle !important;">Auf easymarketing.de die Shop-URL eingeben</li>
                        <li style="list-style-type: circle !important;">Automatische und dauerhafte Optimierung</li>
                        <li style="list-style-type: circle !important;">Registrieren</li>
                      </ul>
                      EASYMARKETING crawlt Ihren Shop, erkennt dabei alle besonders performanten Keywords, erstellt automatisch aus mehreren 100 verschiedenen Keywords &uuml;ber
                      1.000 verschiedene AdWordsAnzeigen, ver&ouml;ffentlicht diese bei Google &amp; optimiert die Ergebnisse permanent. Dank des intelligenten Algorithmus ist
                      EASYMARKETING vielfach effizienter, als wenn der Online-H&auml;ndler die Anzeigenverwaltung manuell vornehmen w&uuml;rde, es wird sehr viel mehr Traffic und
                      somit Umsatz generiert. Sie sparen somit Zeit und auch Geld, weil EASYMARKETING f&uuml;r Sie vollautomatisch arbeitetet und Ihr Budget mit Ber&uuml;cksichtigung
                      Ihrer Konkurrenz auf Google optimal g&uuml;nstig aussteuert. Die CPC-Gebote werden also automatisch berechnet, so dass Sie als Werbetreibender nicht zu
                      viel zahlen.
                      <ul>
                        <li style="list-style-type: circle !important;">Maximale Effizienz &uuml;ber die Werbeaktivit&auml;ten</li>
                        <li style="list-style-type: circle !important;">Automatische und dauerhafte Optimierung</li>
                        <li style="list-style-type: circle !important;">Hohe Zeitersparnis, da Kampagnen automatisch erstellt und gepflegt werden</li>
                      </ul>
                      <br />
                      <a href="https://easymarketing.de/analysis/new?partner=modified" target="_blank"><span style="font-size:12px; color:#FF7A00;"><u><strong>Weitere Infos zu EASYMARKETING finden Sie unter www.easymarketing.de</strong></u></span></a>
                    </td>
                  </tr>
                  <tr style="background-color: #FFFFFF;">
                    <td colspan="2">
                    <iframe style="background-color: transparent; border: 0px none transparent;padding: 0px; overflow: hidden;" seamless="seamless" scrolling="no" frameborder="0" allowtransparency="true" width="300px" height="250px" src="http://api.easymarketing.de/demo_chart?website_url=<?php echo urlencode(HTTP_SERVER.DIR_WS_CATALOG); ?>&partner_id=modified&version=large"></iframe>
                    </td>
                  </tr>
                </table>
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