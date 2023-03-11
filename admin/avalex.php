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
        <div class="pageHeading pdg2">avalex Rechtstexte</div>
        <div class="main">Modules</div>         
        <table class="tableCenter">
          <tr>
            <td valign="middle" class="dataTableHeadingContent" style="width:250px;">
              Update-Service                        
            </td>
            <td valign="middle" class="dataTableHeadingContent">
              <a href="<?php echo xtc_href_link('module_export.php', 'set=system&module=avalex'); ?>"><u>Einstellungen</u></a>  
            </td>
          </tr>
          <tr style="background-color: #FFFFFF;">
            <td colspan="2" style="font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 12px; padding: 0px 10px 11px 10px; text-align: justify">
              <br />
              <a href="https://avalex.de" target="_blank"><img src="images/avalex/avalex-banner-modified.png" style="max-width:100%" /></a><br /><br />
              <p><strong>Sch&uuml;tzen Sie Ihren modified Shop mit automatisch aktuellen Rechtstexten vor Abmahnungen. Einmal installiert, dauerhaft gesch&uuml;tzt.</strong></p>
              Im avalex Paket f&uuml;r Onlineshops sind die folgenden Rechtstexte enthalten:
              <ul>
                <li><strong>Impressum</strong></li>
                <li><strong>Datenschutzerkl&auml;rung</strong></li>
                <li><strong>Widerrufsbelehrung</strong></li>
                <li><strong>AGB</strong></li>
              </ul>
              <p>Starten Sie mit einem kostenlosen Scan Ihres modified Shops auf <a href="https://avalex.de" target="_blank">www.avalex.de</a>. Wir &uuml;berpr&uuml;fen den Shop automatisiert auf Abmahnrisiken und zeigen an, welche aktiven Webdienste von uns gefunden wurden. Nach der Bestellung konfigurieren Sie mithilfe unseres Scanners, Fragen zu Ihrem Unternehmen und umfangreichen Hilfetexten selbst individuelle Rechtstexte in Ihrem avalex-Kundenkonto (Keine Sorge, es dauert nur wenige Minuten). Nach der Konfiguration installieren Sie das avalex Modul f&uuml;r modified Shops und entscheiden, auf welchen Seiten Ihres Shops die avalex Rechtstexte ausgespielt werden sollen. Ab sofort halten wir Ihre avalex Rechtstexte w&auml;hrend der Vertragslaufzeit automatisch &uuml;ber das Internet auf dem aktuellen Stand. Falls wir einen Fehler machen und Sie deshalb abgemahnt w&uuml;rden, tragen wir die entstehenden Kosten gem&auml;&szlig; Rechtsanwaltsverg&uuml;tungsgesetz und Gerichtskostengesetz.</p>
              <p>Die wichtigsten avalex Features im &Uuml;berblick:</p>
              <ul>
                <li><strong>Anwaltlich erstellte Rechtstexte f&uuml;r Ihren modified Shop</strong></li>
                <li><strong>Bereitgestellt auf Deutsch und Englisch (ohne Aufpreis)</strong></li>
                <li><strong>Laufende automatisierte Aktualisierung, z.B. bei neuen Urteilen oder Gesetzes&auml;nderungen</strong></li>
                <li><strong>Einfache Einbindung per Modul</strong></li>
                <li><strong>Inklusive Abmahnkostenschutz</strong></li>
                <li><strong>W&ouml;chentliche Scans Ihres Shops + Infomails bei rechtlich relevanten &Auml;nderungen</strong></li>
                <li><strong><a href="https://www.avalex.de/faq#collapse-13" target="_blank"><font style="font-size:12px;"><u><strong>30 Tage Geld-zur&uuml;ck-Garantie</strong></u></font></a></strong></li>
              </ul>
              <p align="left">
                <br />
                <a href="https://avalex.de" target="_blank"><font size="3" color="#893769"><u><strong>&rarr; Jetzt Shop absichern mit avalex Rechtstexten</strong></u></font></a> 
              </p>
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