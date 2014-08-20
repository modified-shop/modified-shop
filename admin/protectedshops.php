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
        <div class="pageHeading pdg2">Protected Shops</div>
        <div class="main">Modules</div>         
        <table class="tableCenter">
          <tr>
            <td valign="middle" class="dataTableHeadingContent" style="width:250px;">
              Protectedshops Auto Updater
            </td>
            <td valign="middle" class="dataTableHeadingContent">
              <a href="<?php echo xtc_href_link('module_export.php', 'set=system&module=protectedshops'); ?>"><u>Einstellungen</u></a>  
            </td>
          </tr>
          <tr style="background-color: #FFFFFF;">
            <td colspan="2" style="font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 12px; padding: 0px 10px 11px 10px; text-align: justify">
              <br />
              <strong>Dauerhaft vor Abmahnungen gesch&uuml;tzt sein</strong><br />
              <br />
              <img src="images/protectedshops/protectedshops_logo.png" />
              <br />
              <br />
              Mit dem Schnittstellen-Modul f&uuml;r die modified eCommerce Shopsoftware erfolgt die Einbindung und Aktualisierung der Rechtstexte voll automatisch. F&uuml;r den Online-H&auml;ndler erfordert dies nur minimalen Aufwand und bietet ihm <strong>dauerhaft abmahngesch&uuml;tzte Texte</strong> f&uuml;r den Onlinehandel.<br />
              <br />
              <strong>Immer topaktuell</strong><br />
              <br />
              Das Modul ist dauerhaft und stets aktuell f&uuml;r folgende Texte erh&auml;ltlich:<br />
              <ul>
                <li style="list-style-type: circle !important;">AGB</li>
                <li style="list-style-type: circle !important;">Impressum</li>
                <li style="list-style-type: circle !important;">Widerrufsbelehrung</li>
                <li style="list-style-type: circle !important;">Datenschutzerkl&auml;rung</li>
                <li style="list-style-type: circle !important;">Zahlarten und Versandkostenerkl&auml;rung</li>
                <li style="list-style-type: circle !important;">Informationen zur Batterieentsorgung</li>
              </ul>
              <br />
              Bei &Auml;nderung der Gesetzeslage werden die betroffenen Texte kostenlos von uns angepasst und automatisch in ihren Shop eingepflegt. Zudem bieten wir die <strong>vollst&auml;ndige Abbildung von Bezahl- und Logistikdienstleistern</strong>.<br />
              <br />
              <strong>Einfach zu integrieren, nach 30 Minuten sicher vor Abmahnungen gesch&uuml;tzt</strong><br />
              <br />
              So funktioniert die schnelle und unkomplizierte Einbindung:<br />
              <ol>
                <li style="list-style-type: decimal !important;">Benutzerfreundlichen Fragebogen online durchlaufen,</li>
                <li style="list-style-type: decimal !important;">Schnittstelle einbinden und konfigurieren,</li> 
                <li style="list-style-type: decimal !important;">Rechtstexte werden automatisch im Shop dargestellt und in die Prozesse integriert.</li>
              </ol>
              <strong>Haftung inklusive</strong><br />
              <br />
              Au&szlig;erdem haftet die Protected Shops GmbH f&uuml;r ihre Texte. Sollte es dennoch aufgrund unserer Texte zu einer Abmahnung kommen &uuml;bernehmen wir die Kosten.<br />
              <br />
              <strong>Vorteile mit Protected Shops:</strong>
              <ul>
                <li style="list-style-type: circle !important;">Erstellung rechtssicherer anwaltlich &uuml;berpr&uuml;fter Texte f&uuml;r ihren Shop</li>
                <li style="list-style-type: circle !important;">Kostenloses automatisches Update der Rechtstexte</li>
                <li style="list-style-type: circle !important;">Vollst&auml;ndige Anbindung an Bezahl- und Logisikdienstleistern</li>
                <li style="list-style-type: circle !important;">Leichte Einbindung des Schnittstellen-Moduls</li>
                <li style="list-style-type: circle !important;">Die Haftung f&uuml;r die Texte &uuml;bernimmt Protected Shops</li>
              </ul>
              <p align="left">
                <br />
                <a href="http://www.protectedshops.de/unsere-schutzpakete?sPartner=modified" target="_blank"><font size="3" color="#893769"><u><strong>JETZT in wenigen Minuten rechtssichere Texte erstellen und sofort einbinden!</strong></u></font></a> 
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