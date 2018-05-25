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
        <div class="pageHeading pdg2">janolaw</div>
        <div class="main">Modules</div>         
        <table class="tableCenter">
          <tr>
            <td valign="middle" class="dataTableHeadingContent" style="width:250px;">
              AGB Hosting-Service                        
            </td>
            <td valign="middle" class="dataTableHeadingContent">
              <a href="<?php echo xtc_href_link('module_export.php', 'set=system&module=janolaw'); ?>"><u>Einstellungen</u></a>  
            </td>
          </tr>
          <tr style="background-color: #FFFFFF;">
            <td colspan="2" style="font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 12px; padding: 0px 10px 11px 10px; text-align: justify">
              <br />
              <font color="#0099cc"><strong>Sch&uuml;tzen Sie Ihren Shop vor Abmahnungen - dauerhaft, effektiv, automatisch</strong></font><br />
              <img src="images/janolaw/janolaw_header_1000x120s.png" />
              <br />
              <br />
              <img src="images/janolaw/agb_hosting_service_trans.png" align="right" />
              Das modified eCommerce Shopsoftware Schnittstellen-Modul zum AGB Hosting-Service f&uuml;r den Internet-Warenverkauf in Deutschland 
              h&auml;lt Ihre Dokumente automatisch aktuell und sch&uuml;tzt Sie damit effektiv und dauerhaft vor Abmahnungen.<br />
              <ul>
                <li style="list-style-type: circle !important;"><strong>AGB</strong></li>
                <li style="list-style-type: circle !important;"><strong>Widerrufsbelehrung</strong></li>
                <li style="list-style-type: circle !important;"><strong>Datenschutzerkl&auml;rung</strong></li>
                <li style="list-style-type: circle !important;"><strong>Impressum</strong></li>
              </ul>
              <br />
              <font color="#0099cc"><strong>Und so einfach geht&#x27;s:</strong></font>
              <ol>
                <li style="list-style-type: decimal !important;">Online rund 40 Fragen zu Ihrem Shop beantworten und dadurch alle 4 Dokumente erstellen</li> 
                <li style="list-style-type: decimal !important;">Dokumente &uuml;ber die Schnittstelle in Shop und E-Mails einbinden - fertig.</li>
              </ol>
              Ist das Modul einmal installiert, werden die Dokumente nach jeder rechtlichen &Auml;nderung von den janolaw 
              Anw&auml;lten inhaltlich angepasst. Dabei sorgt das janolaw Modul daf&uuml;r, dass die Dokumente ganz automatisch zum 
              richtigen Zeitpunkt in Ihrem Shop und Ihren Kunden-E-Mails aktualisiert werden.<br />
              <br />
              Zus&auml;tzlich beruhigend f&uuml;r Sie: Die janolaw AG erstattet im Falle einer berechtigten Abmahnung wegen Fehler in 
              Ihren AGB &amp; Co. die Anwalts- und Gerichtskosten. Mit diesem Sorglos-Paket sind Sie optimal vor Abmahnungen 
              gesch&uuml;tzt und k&ouml;nnen sich wieder ganz auf Ihr Kerngesch&auml;ft konzentrieren.<br />
              <br />
              Der AGB Hosting-Service von janolaw wird wahlweise f&uuml;r den Verkauf von klassischer Versandware oder Downloadprodukten angeboten.
              <br /><br />
              <img src="images/janolaw/ehi_120x120.png" align="right" width="100px" height="100px" style="position:relative; left:-200px" />
              <font color="#0099cc">
              <ul>
                <li style="list-style-type: circle !important;"><strong>Anwaltlich gepr&uuml;fte Dokumente f&uuml;r Ihren Shop</strong></li>
                <li style="list-style-type: circle !important;"><strong>Laufende Aktualisierung durch Anw&auml;lte</strong></li>
                <li style="list-style-type: circle !important;"><strong>Zeitersparnis durch automatische Updates</strong></li>
                <li style="list-style-type: circle !important;"><strong>Abmahnkostenhaftung durch die janolaw AG</strong></li>
                <li style="list-style-type: circle !important;"><strong>Bequeme Integration in die modified eCommerce Shopsoftware per Schnittstellen-Modul</strong></li>
                <li style="list-style-type: circle !important;"><strong>Dauerhaft 10% Rabatt auf alle Dokumente und Services von janolaw</strong></li>
              </ul>
              </font>
              <p align="left">
                <br />
                <a href="https://www.janolaw.de/internetrecht/agb/agb-hosting-service/modified/index.html?partnerid=8764#menu" target="_blank"><font size="3" color="#893769"><u><strong>Jetzt buchen und Online-Shop dauerhaft rechtssicher gestalten</strong></u></font></a> 
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