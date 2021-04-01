<?php
/* -----------------------------------------------------------------------------------------
   $Id: janolaw.php 2011-11-24 modified-shop $

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
        <div class="pageHeading pdg2">Site Search 360 Produktsuche</div>
        <div class="main">Modules</div>         
        <table class="tableCenter">
          <tr>
            <td valign="middle" class="dataTableHeadingContent" style="width:250px;">
              Eine intelligente On-Site-Suche, die sich an Deine Bed&uuml;rfnisse anpasst
            </td>
            <td valign="middle" class="dataTableHeadingContent">
              <a href="<?php echo xtc_href_link('module_export.php', 'set=system&amp;module=semknox_system'); ?>"><u>Einstellungen</u></a>  
            </td>
          </tr>
          <tr style="background-color: #FFFFFF;">
            <td colspan="2" style="font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 12px; padding: 0px 10px 11px 10px; text-align: justify">
              <br />
              <img src="images/semknox/SS3_logo-gradient.png" /><br /><br />
              <font color="#3d8fff"><strong>Verbessern Sie die Produktsuche in Ihrem Onlineshop - mit der Site Search 360</strong></font>
              <br />
              <br />
              Unsere blitzschnelle, intelligente und einfach anpassbare Suche erweitert die Grenzen der modified eCommerce Standardsuche. Ersetzen Sie die Standardsuche durch ein SaaS Suchmodul und nutzen Sie die Vorteile einer hoch optimierten Produktsuche.
              <ul>
                <li style="list-style-type: circle !important;">Semantische Produktsuche, die jedes Detail Ihrer Produkte mit Hilfe einer <strong>Ontologie</strong> analysiert und versteht.</li>
                <li style="list-style-type: circle !important;"><strong>Fehlertolerante Suche</strong>, die auch die Hinterlegung von passgenauen Ergebnissen &uuml;ber den integrierten Drag&rsquo;n&rsquo;Drop Editor erlaubt.</li>
                <li style="list-style-type: circle !important;"><strong>Kostenloser, 14-t&auml;giger Test</strong> der Suche ohne Eingabe von Zahlungsinformationen, automatische Beendigung des Accounts nach Abschluss der Testphase.</li>
                <li style="list-style-type: circle !important;">In Zusammenarbeit entwickelt und <strong>zertifiziert durch die Macher der modified eCommerce Shopsoftware</strong></li>
              </ul>
              Mit der Installation und Aktivierung des Site Search 360 modified eCommerce Moduls werden Ihre Produktdaten automatisch und kontinuierlich mit den Such-Servern von Site Search 360 synchronisiert. Die integrierte Produktontologie (Knowledge Graph) klassifiziert die Produkte, normalisiert die Produktattribute und reichert diese mit zus&auml;tzlichen Synonymen an.<br /><br />
              &Uuml;ber ein kleines JavaScript Widget wird die Suche in das Template des Shops integriert. Sobald der Benutzer die ersten Buchstaben eintippt, erscheint die Autosuggestion mit Produktvorschl&auml;gen. Ein [enter] im Suchfeld zeigt die volle Ergebnisliste innerhalb von Bruchteilen einer Sekunde an, inklusive facettierter Suche &uuml;ber dargestellten Filter.<br /><br />
              Zur Site Search 360 geh&ouml;rt zus&auml;tzlich ein Control Panel, mit dem die volle Kontrolle &uuml;ber die Suche zur Verf&uuml;gung steht. Es lassen sich Ranking Strategien hinterlegen oder einzelne Suchergebnisse explizit gestalten. Dazu kann der Result Manager verwendet werden, mit dem f&uuml;r einzelne Suchbegriffe das Ergebnis per Drag&rsquo;n&rsquo;Drop umgestaltet werden kann. Auch HTML Teaser k&ouml;nnen eingebunden werden oder f&uuml;r bestimmte Suchbegriffe Weiterleitungen eingerichtet werden.<br /><br />
              Die Site Search 360 ist nicht nur auf Produktdaten beschr&auml;nkt, es lassen sich auch beliebige Content Seiten indizieren, wie z.B. ein Blog, ein Magazin oder Ratgeber, eine FAQ-Seite, PDF oder Word-Dokumente und sogar YouTube Videos. Dadurch l&auml;sst sich die Suche ganzheitlich auf die Bed&uuml;rfnisse des Besuchers einstellen, so dass zu jeder Zeit das optimale Ergebnis pr&auml;sentiert werden kann.<br /><br />
              Testen Sie die Site Search 360 noch heute und starten Sie Ihren kostenlosen, 14-t&auml;gigen Testzeitraum noch heute: <a href="https://app.sitesearch360.com/signup.html" target="_blank" style="font-size:12px;"><u>https://app.sitesearch360.com/signup.html</u></a><br /><br />
              <img src="images/semknox/teaser01.png" style="max-width: 100%" /><br /><br />
              <img src="images/semknox/teaser02.png" style="max-width: 100%" /><br /><br />
              <img src="images/semknox/teaser03.png" style="max-width: 100%" /><br /><br />
              Voraussetzung/Anforderungen:<br /><br />
              Zum Betrieb der Site Search 360 Produktsuche ist ein kostenpflichtiger Account erforderlich, welcher unter <a href="https://app.sitesearch360.com/signup.html" target="_blank" style="font-size:12px;"><u>https://app.sitesearch360.com/signup.html</u></a> angelegt werden kann. Jede Neuanmeldung verbleibt f&uuml;r 14 Tage im kostenfreien Testzeitraum und wird automatisch gel&ouml;scht, sollte keine Anmeldung zu einem der kostenpflichtigen Tarife erfolgen.
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