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
  <style type="text/css">
    .sx-container{
      line-height: 1.15;
      -webkit-text-size-adjust: 100%;
      color: #4a4f62;
      font-family: Maven Pro,sans-serif;
      -webkit-font-smoothing: antialiased;
      font-stretch: 100%;
      font-style: normal;
      box-sizing: inherit;
      letter-spacing: inherit;
      margin: 100px auto 20px;
      max-width: 1280px;
      padding: 0 16px;
    }
    .sx-grid{
      line-height: 1.15;
      -webkit-text-size-adjust: 100%;
      color: #4a4f62;
      font-family: Maven Pro,sans-serif;
      -webkit-font-smoothing: antialiased;
      font-stretch: 100%;
      font-style: normal;
      box-sizing: inherit;
      letter-spacing: inherit;
      display: grid;
      grid-template-columns: repeat(3,1fr);
    }
    .sx-grid-item{
      line-height: 1.15;
      -webkit-text-size-adjust: 100%;
      color: #4a4f62;
      font-family: Maven Pro,sans-serif;
      -webkit-font-smoothing: antialiased;
      font-stretch: 100%;
      font-style: normal;
      box-sizing: inherit;
      letter-spacing: inherit;
      margin-right: 16px;
      margin-bottom: 80px;
      margin-left: 16px;
    }
    .sx-grid-item article{
      line-height: 1.15;
      -webkit-text-size-adjust: 100%;
      color: #fff;
      font-family: Maven Pro,sans-serif;
      -webkit-font-smoothing: antialiased;
      font-stretch: 100%;
      font-style: normal;
      box-sizing: inherit;
      letter-spacing: inherit;
      align-items: center;
      background-color: #3d8fff;
      border-radius: 3px;
      box-shadow: rgba(0,0,0,.12) 2px 4px 4px;
      display: flex;
      flex-direction: column;
      height: 100%;
      margin-left: auto;
      margin-right: auto;
      max-width: 600px;
      padding: 0 40px 24px;
      text-align: center;
    }
    .icon-card-wrapper{
      line-height: 1.15;
      -webkit-text-size-adjust: 100%;
      color: #4a4f62;
      font-family: Maven Pro,sans-serif;
      -webkit-font-smoothing: antialiased;
      font-stretch: 100%;
      font-style: normal;
      text-align: center;
      box-sizing: inherit;
      letter-spacing: inherit;
      margin-bottom: 16px;
    }
    .icon-card-wrapper img{
      line-height: 1.15;
      -webkit-text-size-adjust: 100%;
      color: #4a4f62;
      font-family: Maven Pro,sans-serif;
      -webkit-font-smoothing: antialiased;
      font-stretch: 100%;
      font-style: normal;
      text-align: center;
      width: 80px;
      aspect-ratio: auto 80 / 80;
      box-sizing: inherit;
      letter-spacing: inherit;
      border-style: none;
      height: auto;
      max-width: 100%;
      margin-top: -40px;
    }
    .sx-grid-item article h3{
      -webkit-text-size-adjust: 100%;
      color: #fff;
      font-stretch: 100%;
      font-style: normal;
      text-align: center;
      box-sizing: inherit;
      letter-spacing: inherit;
      margin-top: 0;
      max-width: 100%;
      font-size: 28px;
      font-weight: initial;
      line-height: 30px;
      margin-bottom: 16px;
    }
    .sx-grid-item article .icon-card-content{
      -webkit-text-size-adjust: 100%;
      color: #fff;
      font-stretch: 100%;
      font-style: normal;
      text-align: center;
      box-sizing: inherit;
      margin-bottom: 1rem;
      margin-top: 0;
      padding-bottom: 0;
      padding-top: 0;
      max-width: 100%;
      font-size: 16px;
      letter-spacing: -.5px;
      line-height: 23px;
    }
  </style>
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
                <li style="list-style-type: circle !important;"><strong>Kostenloser, 14-t&auml;giger Test</strong> der Suche ohne Eingabe von Zahlungsinformationen, automatische L&ouml;schung des Accounts nach Abschluss der Testphase.</li>
                <li style="list-style-type: circle !important;">In Zusammenarbeit entwickelt und <strong>zertifiziert durch die Macher der modified eCommerce Shopsoftware</strong></li>
              </ul>
              Mit der Installation und Aktivierung des Site Search 360 modified eCommerce Moduls werden Ihre Produktdaten automatisch und kontinuierlich mit den Such-Servern von Site Search 360 synchronisiert. Die integrierte Produktontologie (Knowledge Graph) klassifiziert die Produkte, normalisiert die Produktattribute und reichert diese mit zus&auml;tzlichen Synonymen an.<br /><br />
              &Uuml;ber ein kleines JavaScript Widget wird die Suche in das Template des Shops integriert. Sobald der Benutzer die ersten Buchstaben eintippt, erscheint die Autosuggestion mit Produktvorschl&auml;gen. Ein [enter] im Suchfeld zeigt die volle Ergebnisliste innerhalb von Bruchteilen einer Sekunde an, inklusive facettierter Suche &uuml;ber dargestellten Filter.<br /><br />
              Zur Site Search 360 geh&ouml;rt zus&auml;tzlich ein Control Panel, mit dem die volle Kontrolle &uuml;ber die Suche zur Verf&uuml;gung steht. Es lassen sich Ranking Strategien hinterlegen oder einzelne Suchergebnisse explizit gestalten. Dazu kann der Result Manager verwendet werden, mit dem f&uuml;r einzelne Suchbegriffe das Ergebnis per Drag&rsquo;n&rsquo;Drop umgestaltet werden kann. Auch HTML Teaser k&ouml;nnen eingebunden werden oder f&uuml;r bestimmte Suchbegriffe Weiterleitungen eingerichtet werden.<br /><br />
              Die Site Search 360 ist nicht nur auf Produktdaten beschr&auml;nkt, es lassen sich auch beliebige Content Seiten indizieren, wie z.B. ein Blog, ein Magazin oder Ratgeber, eine FAQ-Seite, PDF oder Word-Dokumente und sogar YouTube Videos. Dadurch l&auml;sst sich die Suche ganzheitlich auf die Bed&uuml;rfnisse des Besuchers einstellen, so dass zu jeder Zeit das optimale Ergebnis pr&auml;sentiert werden kann.<br /><br />
              Testen Sie die Site Search 360 und starten Sie Ihren kostenlosen, 14-t&auml;gigen Testzeitraum noch heute: <a href="https://app.sitesearch360.com/signup.html?kind=ecom&amp;ref=modified-info-page" target="_blank" style="font-size:12px;"><u>https://app.sitesearch360.com/signup.html</u></a><br /><br />

              <div class="sx-container">
                <div class="sx-grid">
                  <div class="sx-grid-item">
                    <article>
                      <div class="icon-card-wrapper">
                        <img src="images/semknox/sx-document-check.svg"
                         width="80" height="80" alt="Document Check">
                      </div>
                      <h3>Schnelle Indexierung mit<br><strong>automatischen</strong> Updates</h3>
                      <p class="icon-card-content">Machen Sie sich keine Sorgen um die Aktualisierung Ihrer Produktdaten im Suchindex.
                        Site Search 360's modified eCommerce Modul sorgt daf&uuml;r, dass Ihre Produktdaten st&auml;ndig aktualisiert werden.</p>
                    </article>
                  </div>
                  <div class="sx-grid-item">
                    <article>
                      <div class="icon-card-wrapper">
                        <img src="images/semknox/sx-speed.svg"
                         width="80" height="80" alt="Speed">
                      </div>
                      <h3><strong>Schnelle Ergebnisse</strong><br>und Vorschl&auml;ge</h3>
                      <p class="icon-card-content">Lassen Sie Ihre Kunden nicht h&auml;ngen. Unsere Produktsuche spielt Vorschl&auml;ge und
                        Ergebnisse schneller aus, als Sie "Google mal" sagen k&ouml;nnen.</p>
                    </article>
                  </div>
                  <div class="sx-grid-item">
                    <article>
                      <div class="icon-card-wrapper">
                        <img src="images/semknox/sx-two-cogs.svg"
                         width="80" height="80" alt="Two Cogs">
                      </div>
                      <h3><strong>Akkurate<br></strong>Vorschlags-Engine</h3>
                      <p class="icon-card-content">Unsere starke Autosuggestion macht das Suchen unglaublich einfach. Vorschl&auml;ge k&ouml;nnen
                        mit spezifischen Produktinformationen wie Preis und Verf&uuml;gbarkeit angereichert werden.</p>
                    </article>
                  </div>
                  <div class="sx-grid-item">
                    <article>
                      <div class="icon-card-wrapper">
                        <img src="images/semknox/sx-three-directions.svg"
                         width="80" height="80" alt="Three Directions">
                      </div>
                      <h3>Indizierung von Produktdaten<br>und <strong>Content</strong></h3>
                      <p class="icon-card-content">Site Search 360 kann neben den Produktdaten auch beliebigen Content Ihres Webshops
                        indizieren, wie z.B. einen Blog, Ratgeber oder Magazin. So k&ouml;nnen sowohl Produkt- als auch Content-Ergebnisse
                        angezeigt werden.</p>
                    </article>
                  </div>
                  <div class="sx-grid-item">
                    <article>
                      <div class="icon-card-wrapper">
                        <img src="images/semknox/sx-search-loop.svg"
                         width="80" height="80" alt="Search Loop">
                      </div>
                      <h3>Leicht konfigurierbare<br><strong>Facettensuche</strong></h3>
                      <p class="icon-card-content">Geben Sie Ihren Kunden mittels Filtern die M&ouml;glichkeit, Suchergebnisse anhand von
                        gew&uuml;nschten Attributseigenschaften mit wenigen Klicks zu verfeinern.</p>
                    </article>
                  </div>
                  <div class="sx-grid-item">
                    <article>
                      <div class="icon-card-wrapper">
                        <img src="images/semknox/sx-half-rolled-document.svg"
                         width="80" height="80" alt="Half Rolled Document">
                      </div>
                      <h3>Volle <strong>Kontrolle</strong><br>&uuml;ber Suchergebnisse</h3>
                      <p class="icon-card-content">Mit dem Result Manager k&ouml;nnen Sie Suchergebnisse beliebig anpassen, um Landing Pages
                        aufzubauen, Suchanfragen umzuschreiben oder Weiterleitungen einzurichten, ganz nach Ihren W&uuml;nschen.</p>
                    </article>
                  </div>
                  <div class="sx-grid-item">
                    <article>
                      <div class="icon-card-wrapper">
                        <img src="images/semknox/sx-painter-palette.svg"
                         width="80" height="80" alt="Painter Palette">
                      </div>
                      <h3><strong>Automatische</strong> Anpassbarkeit</h3>
                      <p class="icon-card-content">Durch die hohe Kompatibilit&auml;t des Site Search 360 modified eCommerce Moduls ist die
                        Suche in jedem Template sofort einsatzbereit. Mit nur wenigen Anpassungen k&ouml;nnen Sie auch die erweiterten
                        Features in Ihrem Template nutzen.</p>
                    </article>
                  </div>
                  <div class="sx-grid-item">
                    <article>
                      <div class="icon-card-wrapper">
                        <img src="images/semknox/sx-conversation.svg"
                         width="80" height="80" alt="Conversation">
                      </div>
                      <h3><strong>Spitzen-</strong>Support</h3>
                      <p class="icon-card-content">Wir legen besonderen Wert auf Kundensupport und sind stolz darauf, Trustpilot's #1
                        bewertete Site Search L&ouml;sung zu sein.</p>
                    </article>
                  </div>
                  <div class="sx-grid-item">
                    <article>
                      <div class="icon-card-wrapper">
                        <img src="images/semknox/sx-piggy-bank.svg"
                         width="80" height="80" alt="Piggy Bank">
                      </div>
                      <h3><strong>Transparente</strong> Preise</h3>
                      <p class="icon-card-content">Das Abrechungsmodell ist einfach und transparent gestaltet. Zus&auml;tzlich bieten wir
                        jedem Kunden eine kostenlose Testphase an, in der Sie Site Search 360 vollumf&auml;nglich ausprobieren k&ouml;nnen.</p>
                    </article>
                  </div>
                </div>
              </div>

              <strong>Voraussetzung/Anforderungen:</strong><br /><br />
              Zum Betrieb der Site Search 360 Produktsuche ist ein kostenpflichtiger Account erforderlich, welcher unter <a href="https://app.sitesearch360.com/signup.html?kind=ecom&amp;ref=modified-info-page" target="_blank" style="font-size:12px;"><u>https://app.sitesearch360.com/signup.html</u></a> angelegt werden kann. Jede Neuanmeldung verbleibt f&uuml;r 14 Tage im kostenfreien Testzeitraum und wird automatisch gel&ouml;scht, sollte keine Anmeldung zu einem der kostenpflichtigen Tarife erfolgen.
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